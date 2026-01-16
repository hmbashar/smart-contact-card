<?php

namespace Smartcc\Shortcode;

class ContactShortcode
{
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
        $name    = isset($_GET['name']) ? sanitize_text_field(wp_unslash($_GET['name'])) : 'Contact';
        $title   = isset($_GET['title']) ? sanitize_text_field(wp_unslash($_GET['title'])) : '';
        $org     = isset($_GET['org']) ? sanitize_text_field(wp_unslash($_GET['org'])) : '';
        $phone   = isset($_GET['phone']) ? sanitize_text_field(wp_unslash($_GET['phone'])) : '';
        $email   = isset($_GET['email']) ? sanitize_email(wp_unslash($_GET['email'])) : '';
        $website = isset($_GET['website']) ? esc_url_raw(wp_unslash($_GET['website'])) : '';
        $address = isset($_GET['address']) ? sanitize_text_field(wp_unslash($_GET['address'])) : '';

        // Helpers for vCard escaping
        $esc_v = function ($s) {
            $s = trim((string) $s);
            $s = str_replace(["\r", "\n"], ["", ""], $s);
            $s = str_replace(["\\", ",", ";"], ["\\\\", "\\,", "\\;"], $s);
            return $s;
        };

        // Split name into given + family
        $parts  = preg_split('/\s+/', trim($name));
        $given  = $parts[0] ?? $name;
        $family = (count($parts) > 1) ? implode(' ', array_slice($parts, 1)) : '';

        $phone_clean = '';
        if (!empty($phone)) {
            $phone_clean = preg_replace('/[^\d+]/', '', (string) $phone);
        }

        // Build vCard (CRLF is safest for .vcf)
        $v_lines = [
            "BEGIN:VCARD",
            "VERSION:3.0",
            "N:" . $esc_v($family) . ";" . $esc_v($given) . ";;;",
            "FN:" . $esc_v($name),
        ];

        if (!empty($org))   $v_lines[] = "ORG:" . $esc_v($org);
        if (!empty($title)) $v_lines[] = "TITLE:" . $esc_v($title);

        if (!empty($phone_clean)) {
            $v_lines[] = "TEL;TYPE=CELL:" . $esc_v($phone_clean);
        }
        if (!empty($email)) {
            $v_lines[] = "EMAIL;TYPE=INTERNET:" . $esc_v($email);
        }
        if (!empty($website)) {
            $v_lines[] = "URL:" . $esc_v($website);
        }
        if (!empty($address)) {
            // POBOX;EXT;STREET;LOCALITY;REGION;POSTAL;COUNTRY
            $v_lines[] = "ADR;TYPE=WORK:;;" . $esc_v($address) . ";;;;";
        }

        $v_lines[] = "END:VCARD";

        $vcard_file = implode("\r\n", $v_lines) . "\r\n";

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

        // ---------------------------
        // Helpers
        // ---------------------------
        $esc_v = function ($s) {
            $s = trim((string) $s);
            $s = str_replace(["\r", "\n"], ["", ""], $s);
            $s = str_replace(["\\", ",", ";"], ["\\\\", "\\,", "\\;"], $s);
            return $s;
        };

        $esc_m = function ($s) {
            $s = trim((string) $s);
            $s = str_replace(["\r", "\n"], ["", ""], $s);
            $s = str_replace(["\\", ";", ":"], ["\\\\", "\\;", "\\:"], $s);
            return $s;
        };

        $name_raw = $a['name'] ?: 'Contact';

        $parts  = preg_split('/\s+/', trim($name_raw));
        $given  = $parts[0] ?? $name_raw;
        $family = (count($parts) > 1) ? implode(' ', array_slice($parts, 1)) : '';

        $phone_clean = '';
        if (!empty($a['phone'])) {
            $phone_clean = preg_replace('/[^\d+]/', '', (string) $a['phone']);
        }

        // ---------------------------
        // vCard for file streaming (we still build it here for QR fallback if needed)
        // ---------------------------
        $v_lines = [
            "BEGIN:VCARD",
            "VERSION:3.0",
            "N:" . $esc_v($family) . ";" . $esc_v($given) . ";;;",
            "FN:" . $esc_v($name_raw),
        ];

        if (!empty($a['org']))   $v_lines[] = "ORG:" . $esc_v($a['org']);
        if (!empty($a['title'])) $v_lines[] = "TITLE:" . $esc_v($a['title']);

        if (!empty($phone_clean)) {
            $v_lines[] = "TEL;TYPE=CELL:" . $esc_v($phone_clean);
        }
        if (!empty($a['email'])) {
            $v_lines[] = "EMAIL;TYPE=INTERNET:" . $esc_v($a['email']);
        }
        if (!empty($a['website'])) {
            $v_lines[] = "URL:" . $esc_v($a['website']);
        }
        if (!empty($a['address'])) {
            $v_lines[] = "ADR;TYPE=WORK:;;" . $esc_v($a['address']) . ";;;;";
        }

        $v_lines[] = "END:VCARD";
        $vcard_file = implode("\r\n", $v_lines) . "\r\n";

        // ---------------------------
        // MECARD for QR (best Android support)
        // ---------------------------
        $mecard = "MECARD:";
        $mecard .= "N:" . $esc_m($name_raw) . ";";
        if (!empty($phone_clean)) $mecard .= "TEL:" . $esc_m($phone_clean) . ";";
        if (!empty($a['email']))  $mecard .= "EMAIL:" . $esc_m($a['email']) . ";";
        if (!empty($a['address'])) $mecard .= "ADR:" . $esc_m($a['address']) . ";";
        if (!empty($a['website'])) $mecard .= "URL:" . $esc_m($a['website']) . ";";
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
                case 'vcard':
                default:
                    // IMPORTANT: Use MECARD for QR on Android
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
}
