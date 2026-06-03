<?php

namespace Smartcc\Shortcode;

class ContactShortcode
{
    private const URL_ATTRIBUTES = ['avatar', 'website', 'qr_url'];

    public function register(): void
    {
        // Register shortcode on init hook to ensure proper WordPress initialization
        // This fixes the issue where shortcode displays as raw text in Gutenberg/text editor
        add_action('init', function() {
            add_shortcode('smartcc_contact', [$this, 'render']);
        }, 5);

        // Handle vCard download endpoint
        add_action('init', [$this, 'maybe_download_vcf']);
        
        // Fix line breaks in shortcodes (common issue in Gutenberg)
        // This ensures shortcodes work even when pasted with line breaks
        add_filter('the_content', [$this, 'fix_shortcode_formatting'], 7);
    }
    
    /**
     * Remove line breaks from within shortcodes to fix formatting issues.
     * Gutenberg and text editors sometimes add line breaks that break shortcode processing.
     */
    public function fix_shortcode_formatting($content): string
    {
        // Pattern to match our shortcode with potential line breaks
        $pattern = '/\[smartcc_contact\s+([^\]]*?)\]/s';
        
        $content = preg_replace_callback($pattern, function($matches) {
            // Remove line breaks and extra spaces from shortcode attributes
            $shortcode_content = preg_replace('/\s+/', ' ', $matches[0]);
            return $shortcode_content;
        }, $content);
        
        return $content;
    }

    /**
     * Download endpoint: /?smartcc_vcf=1&name=...&phone=...&...&_wpnonce=...
     */
    public function maybe_download_vcf(): void
    {
        if (empty($_GET['smartcc_vcf'])) {
            return;
        }

        // Basic nonce check
        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
        if (!wp_verify_nonce($nonce, 'smartcc_download_vcf')) {
            status_header(403);
            echo 'Invalid nonce.';
            exit;
        }

        // Read params (keep small & safe)
        $name    = isset($_GET['name']) ? sanitize_text_field($this->normalize_shortcode_value(wp_unslash($_GET['name']))) : 'Contact';
        $title   = isset($_GET['title']) ? sanitize_text_field($this->normalize_shortcode_value(wp_unslash($_GET['title']))) : '';
        $org     = isset($_GET['org']) ? sanitize_text_field($this->normalize_shortcode_value(wp_unslash($_GET['org']))) : '';
        $phone   = isset($_GET['phone']) ? sanitize_text_field($this->normalize_shortcode_value(wp_unslash($_GET['phone']))) : '';
        $email   = isset($_GET['email']) ? sanitize_email($this->normalize_shortcode_value(wp_unslash($_GET['email']))) : '';
        $website = isset($_GET['website']) ? $this->normalize_url_value(wp_unslash($_GET['website'])) : '';
        $avatar  = isset($_GET['avatar']) ? $this->normalize_url_value(wp_unslash($_GET['avatar'])) : '';
        $address = isset($_GET['address']) ? sanitize_text_field($this->normalize_shortcode_value(wp_unslash($_GET['address']))) : '';

        $vcard_file = $this->build_vcard_file([
            'name' => $name,
            'title' => $title,
            'org' => $org,
            'phone' => $phone,
            'email' => $email,
            'website' => $website,
            'avatar' => $avatar,
            'address' => $address,
        ]);

        // Stream file with correct headers
        nocache_headers();

        $filename = sanitize_title($name ?: 'contact') . '.vcf';

        header('Content-Type: text/vcard; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('X-Content-Type-Options: nosniff');

        echo $vcard_file;
        exit;
    }

    public function render($atts, $content = ''): string
    {
        $a = shortcode_atts([
            // identity
            'name'   => '',
            'title'  => '',
            'org'    => '',
            'avatar' => '',
            // contacts
            'phone'   => '',
            'email'   => '',
            'website' => '',
            'address' => '',
            'whatsapp' => '',
            'telegram' => '',
            'imo'      => '',
            'skype'    => '',
            'wechat'   => '',
            // visuals / behavior
            'button' => 'Save Contact (.vcf)',
            'layout' => 'card',
            // QR controls
            'qr_for' => 'vcard',
            'qr_url' => '',
            'qr_text' => '',
            'design'  => 'default', // 'default' | 'minimal_qr'
        ], $atts, 'smartcc_contact');
        $a = $this->normalize_shortcode_attributes($a);

        // ---------------------------
        // Helpers
        // ---------------------------
        $name_raw = $a['name'] ?: 'Contact';
        $phone_clean = $this->clean_phone($a['phone']);

        // ---------------------------
        // vCard for file streaming and optional full-vCard QR payloads.
        // ---------------------------
        $vcard_file = $this->build_vcard_file([
            'name' => $name_raw,
            'title' => $a['title'],
            'org' => $a['org'],
            'phone' => $a['phone'],
            'email' => $a['email'],
            'website' => $a['website'],
            'avatar' => $a['avatar'],
            'address' => $a['address'],
        ]);

        // ---------------------------
        // Compact contact QR payload. Android scanners parse this more consistently than multiline vCards.
        // ---------------------------
        $mecard = "MECARD:";
        $mecard .= "N:" . $this->escape_mecard_value($name_raw) . ";";
        if (!empty($phone_clean)) $mecard .= "TEL:" . $this->escape_mecard_value($phone_clean) . ";";
        if (!empty($a['email']))  $mecard .= "EMAIL:" . $this->escape_mecard_value($a['email']) . ";";
        if (!empty($a['address'])) $mecard .= "ADR:" . $this->escape_mecard_value($a['address']) . ";";
        if (!empty($a['website'])) $mecard .= "URL:" . $this->escape_mecard_value($a['website'], false) . ";";
        $mecard .= ";";

        // ---------------------------
        // Download URL (REAL file, not data:)
        // ---------------------------
        $download_name = sanitize_title($name_raw ?: 'contact') . '.vcf';

        $vcard_url = add_query_arg([
            'smartcc_vcf' => 1,
            'name' => $name_raw,
            'title' => $a['title'],
            'org' => $a['org'],
            'phone' => $a['phone'],
            'email' => $a['email'],
            'website' => $a['website'],
            'avatar' => $a['avatar'],
            'address' => $a['address'],
            '_wpnonce' => wp_create_nonce('smartcc_download_vcf'),
        ], home_url('/'));

        // ---------------------------
        // Links (chips)
        // ---------------------------
        $links = [];
        if (!empty($a['email']))   $links[] = ['label' => $a['email'], 'href' => 'mailto:' . $a['email'], 'type' => 'email'];
        if (!empty($a['phone']))   $links[] = ['label' => $a['phone'], 'href' => 'tel:' . $a['phone'], 'type' => 'phone'];
        if (!empty($a['website'])) $links[] = ['label' => 'Website', 'href' => $a['website'], 'type' => 'website'];
        if (!empty($a['address'])) $links[] = ['label' => 'Address', 'href' => 'https://maps.google.com/?q=' . rawurlencode($a['address']), 'type' => 'address'];
        if (!empty($a['whatsapp'])) $links[] = ['label' => 'WhatsApp', 'href' => 'https://wa.me/' . preg_replace('/\D/', '', $a['whatsapp']), 'type' => 'whatsapp'];
        if (!empty($a['telegram'])) $links[] = ['label' => 'Telegram', 'href' => 'https://t.me/' . ltrim($a['telegram'], '@'), 'type' => 'telegram'];
        if (!empty($a['imo']))      $links[] = ['label' => 'imo', 'href' => 'im:' . $a['imo'], 'type' => 'imo'];
        if (!empty($a['skype']))    $links[] = ['label' => 'Skype', 'href' => 'skype:' . $a['skype'] . '?chat', 'type' => 'skype'];
        if (!empty($a['wechat']))   $links[] = ['label' => 'WeChat', 'href' => 'weixin://dl/add?username=' . $a['wechat'], 'type' => 'wechat'];

        // ---------------------------
        // Minimal design branch
        // ---------------------------
        if (strtolower($a['design']) === 'minimal_qr') {
            $name   = esc_html($name_raw);
            $phone  = esc_html($a['phone']);
            $email  = esc_html($a['email']);
            $avatar = esc_url($a['avatar']);
            $qr_src = esc_url($a['qr_url']);

            wp_enqueue_style('smartcc-card');

            ob_start();
            include SMARTCC_DIR . 'src/View/card-minimal.php';
            return (string) ob_get_clean();
        }

        // ---------------------------
        // QR payload selection
        // ---------------------------
        $payload = '';

        if (!empty($a['qr_text'])) {
            $payload = (string) $a['qr_text'];
        } else {
            switch (strtolower($a['qr_for'])) {
                case 'whatsapp':
                    if (!empty($a['whatsapp'])) $payload = 'https://wa.me/' . preg_replace('/\D/', '', $a['whatsapp']);
                    break;
                case 'telegram':
                    if (!empty($a['telegram'])) $payload = 'https://t.me/' . ltrim($a['telegram'], '@');
                    break;
                case 'imo':
                    if (!empty($a['imo'])) $payload = 'im:' . $a['imo'];
                    break;
                case 'skype':
                    if (!empty($a['skype'])) $payload = 'skype:' . $a['skype'] . '?chat';
                    break;
                case 'wechat':
                    if (!empty($a['wechat'])) $payload = 'weixin://dl/add?username=' . $a['wechat'];
                    break;
                case 'url':
                    if (!empty($a['website'])) $payload = $a['website'];
                    elseif (!empty($a['email'])) $payload = 'mailto:' . $a['email'];
                    elseif (!empty($a['phone'])) $payload = 'tel:' . $a['phone'];
                    break;
                case 'vcard_full':
                    $payload = $vcard_file;
                    break;
                case 'mecard':
                case 'vcard':
                default:
                    $payload = $mecard;
                    break;
            }
        }

        // ---------------------------
        // QR image source
        // ---------------------------
        $qr_src = '';
        if (!empty($a['qr_url'])) {
            $qr_src = $a['qr_url'];
        } elseif (!empty($payload)) {
            $qr_src = apply_filters(
                'smartcc_qr_code_url',
                'https://quickchart.io/qr?size=200&margin=2&text=' . rawurlencode($payload),
                $payload
            );
        }

        // ---------------------------
        // View vars
        // ---------------------------
        $vars = [
            'name' => esc_html($name_raw),
            'title' => esc_html($a['title']),
            'org' => esc_html($a['org']),
            'avatar' => esc_url($a['avatar']),
            'links' => $links,
            'button' => esc_html($a['button']),
            'vcard_url' => esc_url($vcard_url),
            'download_name' => esc_attr($download_name),
            'qr_src' => esc_url($qr_src),
        ];

        wp_enqueue_style('smartcc-card');

        ob_start();
        extract($vars, EXTR_SKIP);
        include SMARTCC_DIR . 'src/View/card.php';
        return (string) ob_get_clean();
    }

    private function normalize_shortcode_attributes(array $atts): array
    {
        foreach ($atts as $key => $value) {
            if (is_string($value)) {
                $atts[$key] = $this->normalize_shortcode_value($value);
            }
        }

        foreach (self::URL_ATTRIBUTES as $key) {
            if (isset($atts[$key])) {
                $atts[$key] = $this->normalize_url_value($atts[$key]);
            }
        }

        return $atts;
    }

    private function normalize_shortcode_value($value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $charset = function_exists('get_bloginfo') ? get_bloginfo('charset') : 'UTF-8';
        $charset = $charset ?: 'UTF-8';

        for ($i = 0; $i < 2; $i++) {
            $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, $charset);
            if ($decoded === $value) {
                break;
            }
            $value = $decoded;
        }

        $value = trim($value);
        $value = preg_replace('/^[\s"\'\x{2018}\x{2019}\x{201C}\x{201D}]+|[\s"\'\x{2018}\x{2019}\x{201C}\x{201D}]+$/u', '', $value);

        return is_string($value) ? trim($value) : '';
    }

    private function normalize_url_value($value): string
    {
        $url = $this->normalize_shortcode_value($value);
        if ($url === '') {
            return '';
        }

        $url = preg_replace('/^([a-z][a-z0-9+.-]*)\\\\:\/\//i', '$1://', $url);

        return is_string($url) ? esc_url_raw($url) : '';
    }

    private function build_vcard_file(array $data): string
    {
        $name = $data['name'] ?? 'Contact';
        $name = $name !== '' ? $name : 'Contact';
        $title = $data['title'] ?? '';
        $org = $data['org'] ?? '';
        $phone_clean = $this->clean_phone($data['phone'] ?? '');
        $email = $data['email'] ?? '';
        $website = $data['website'] ?? '';
        $avatar = $data['avatar'] ?? '';
        $address = $data['address'] ?? '';

        $parts = preg_split('/\s+/', trim($name));
        $given = $parts[0] ?? $name;
        $family = (count($parts) > 1) ? implode(' ', array_slice($parts, 1)) : '';

        $v_lines = [
            "BEGIN:VCARD",
            "VERSION:3.0",
            "N:" . $this->escape_vcard_value($family) . ";" . $this->escape_vcard_value($given) . ";;;",
            "FN:" . $this->escape_vcard_value($name),
        ];

        if (!empty($org)) $v_lines[] = "ORG:" . $this->escape_vcard_value($org);
        if (!empty($title)) $v_lines[] = "TITLE:" . $this->escape_vcard_value($title);
        if (!empty($phone_clean)) $v_lines[] = "TEL;TYPE=CELL:" . $this->escape_vcard_value($phone_clean);
        if (!empty($email)) $v_lines[] = "EMAIL;TYPE=INTERNET:" . $this->escape_vcard_value($email);
        if (!empty($website)) $v_lines[] = "URL:" . $this->escape_vcard_value($website);
        if (!empty($avatar)) $v_lines[] = "PHOTO;VALUE=URI:" . $this->escape_vcard_value($avatar);
        if (!empty($address)) $v_lines[] = "ADR;TYPE=WORK:;;" . $this->escape_vcard_value($address) . ";;;;";

        $v_lines[] = "END:VCARD";

        return implode("\r\n", $v_lines) . "\r\n";
    }

    private function clean_phone($phone): string
    {
        return !empty($phone) ? preg_replace('/[^\d+]/', '', (string) $phone) : '';
    }

    private function escape_vcard_value($value): string
    {
        $value = trim((string) $value);
        $value = str_replace(["\r", "\n"], ["", ""], $value);

        return str_replace(["\\", ",", ";"], ["\\\\", "\\,", "\\;"], $value);
    }

    private function escape_mecard_value($value, bool $escape_colon = true): string
    {
        $value = trim((string) $value);
        $value = str_replace(["\r", "\n"], ["", ""], $value);

        $search = ["\\", ";"];
        $replace = ["\\\\", "\\;"];

        if ($escape_colon) {
            $search[] = ":";
            $replace[] = "\\:";
        }

        return str_replace($search, $replace, $value);
    }
}
