<?php
namespace Smartcc\Shortcode;

class ContactShortcode
{
    public function register(): void
    {
        add_shortcode('smartcc_contact', [$this, 'render']);
    }

    public function render($atts, $content = ''): string
    {
        $a = shortcode_atts([
            // identity
            'name' => '',
            'title' => '',
            'org' => '',
            'avatar' => '',
            // contacts
            'phone' => '',
            'email' => '',
            'website' => '',
            'address' => '',
            'whatsapp' => '',   // E.164 preferred (digits only or with +)
            'telegram' => '',   // @username or username
            'imo' => '',   // imo username/id
            'skype' => '',   // skype id
            'wechat' => '',   // WeChat ID (username)
            // visuals / behavior
            'button' => 'Save Contact (.vcf)',
            'layout' => 'card',     // reserved for future 'inline'
            // QR controls
            // choose what the QR encodes: vcard | whatsapp | telegram | imo | skype | wechat | url
            'qr_for' => 'vcard',
            // directly use this QR image (bypasses auto generation)
            'qr_url' => '',
            // override payload explicitly (encoded as QR if provided)
            'qr_text' => '',
            'design' => 'default',  // 'default' | 'minimal'
        ], $atts, 'smartcc_contact');

        // --- vCard (3.0) ---
        $e = fn($s) => str_replace(["\r", "\n", ",", ";"], ["", "", "\\,", "\\;"], trim((string) $s));
        $name = $a['name'] ?: 'Contact';
        $lines = [
            'BEGIN:VCARD',
            'VERSION:3.0',
            'N:;' . $e($name) . ';;;',
            'FN:' . $e($name),
        ];
        if ($a['org'])
            $lines[] = 'ORG:' . $e($a['org']);
        if ($a['title'])
            $lines[] = 'TITLE:' . $e($a['title']);
        if ($a['phone'])
            $lines[] = 'TEL;TYPE=CELL,VOICE:' . $e($a['phone']);
        if ($a['email'])
            $lines[] = 'EMAIL;TYPE=INTERNET:' . $e($a['email']);
        if ($a['website'])
            $lines[] = 'URL:' . $e($a['website']);
        if ($a['address'])
            $lines[] = 'ADR;TYPE=WORK:;;' . $e($a['address']) . ';;;';
        $lines[] = 'END:VCARD';
        $vcard = implode("\r\n", $lines);
        $vcard_url = 'data:text/vcard;charset=utf-8,' . rawurlencode($vcard);
        $download_name = sanitize_title($name ?: 'contact') . '.vcf';

        // --- Links (chips) ---
        $links = [];
        if ($a['email'])
            $links[] = ['label' => $a['email'], 'href' => 'mailto:' . $a['email'], 'type' => 'email'];
        if ($a['phone'])
            $links[] = ['label' => $a['phone'], 'href' => 'tel:' . $a['phone'], 'type' => 'phone'];
        if ($a['website'])
            $links[] = ['label' => 'Website', 'href' => $a['website'], 'type' => 'website'];
        if ($a['address'])
            $links[] = ['label' => 'Address', 'href' => 'https://maps.google.com/?q=' . rawurlencode($a['address']), 'type' => 'address'];
        if ($a['whatsapp'])
            $links[] = ['label' => 'WhatsApp', 'href' => 'https://wa.me/' . preg_replace('/\D/', '', $a['whatsapp']), 'type' => 'whatsapp'];
        if ($a['telegram'])
            $links[] = ['label' => 'Telegram', 'href' => 'https://t.me/' . ltrim($a['telegram'], '@'), 'type' => 'telegram'];
        if ($a['imo'])
            $links[] = ['label' => 'imo', 'href' => 'im:' . $a['imo'], 'type' => 'imo'];
        if ($a['skype'])
            $links[] = ['label' => 'Skype', 'href' => 'skype:' . $a['skype'] . '?chat', 'type' => 'skype'];
        if ($a['wechat'])
            $links[] = ['label' => 'WeChat', 'href' => 'weixin://dl/add?username=' . $a['wechat'], 'type' => 'wechat'];


        // ===== Minimal design branch (avatar, name, phone, email, custom QR only) =====
        if (strtolower($a['design']) === 'minimal_qr') {
            $name = esc_html($a['name'] ?: 'Contact');
            $phone = esc_html($a['phone']);
            $email = esc_html($a['email']);
            $avatar = esc_url($a['avatar']);
            $qr_src = esc_url($a['qr_url']); // user-provided QR image URL

            // enqueue the same base style your default design uses
            wp_enqueue_style('smartcc-card');

            ob_start();
            include SMARTCC_DIR . 'src/View/card-minimal.php';
            return (string) ob_get_clean();
        }



        // --- QR payload selection ---
        $payload = '';
        if (!empty($a['qr_text'])) {
            $payload = (string) $a['qr_text'];
        } else {
            switch (strtolower($a['qr_for'])) {
                case 'whatsapp':
                    if ($a['whatsapp'])
                        $payload = 'https://wa.me/' . preg_replace('/\D/', '', $a['whatsapp']);
                    break;
                case 'telegram':
                    if ($a['telegram'])
                        $payload = 'https://t.me/' . ltrim($a['telegram'], '@');
                    break;
                case 'imo':
                    if ($a['imo'])
                        $payload = 'im:' . $a['imo'];
                    break;
                case 'skype':
                    if ($a['skype'])
                        $payload = 'skype:' . $a['skype'] . '?chat';
                    break;
                case 'wechat':
                    // WeChat supports deep link scheme (opens in WeChat if installed)
                    if ($a['wechat'])
                        $payload = 'weixin://dl/add?username=' . $a['wechat'];
                    break;
                case 'url':
                    // Use website if present; else email mailto; else tel
                    if ($a['website'])
                        $payload = $a['website'];
                    elseif ($a['email'])
                        $payload = 'mailto:' . $a['email'];
                    elseif ($a['phone'])
                        $payload = 'tel:' . $a['phone'];
                    break;
                case 'vcard':
                default:
                    $payload = $vcard;
            }
        }

        // --- QR image source ---
        $qr_src = '';
        if (!empty($a['qr_url'])) {
            $qr_src = $a['qr_url']; // trust user-provided QR image
        } elseif (!empty($payload)) {
            // Generate via QuickChart (no PHP deps)
            // Allow developers to filter the QR code generation service
            $qr_src = apply_filters('smartcc_qr_code_url', 
                'https://quickchart.io/qr?size=200&margin=2&text=' . rawurlencode($payload),
                $payload
            );
        }

        // --- View vars ---
        $vars = [
            'name' => esc_html($name),
            'title' => esc_html($a['title']),
            'org' => esc_html($a['org']),
            'avatar' => esc_url($a['avatar']),
            'links' => $links,
            'button' => esc_html($a['button']),
            'vcard_url' => $vcard_url,
            'download_name' => $download_name,
            'qr_src' => esc_url($qr_src),
        ];

        wp_enqueue_style('smartcc-card');

        ob_start();
        extract($vars, EXTR_SKIP);
        include SMARTCC_DIR . 'src/View/card.php';
        return (string) ob_get_clean();
    }
}
