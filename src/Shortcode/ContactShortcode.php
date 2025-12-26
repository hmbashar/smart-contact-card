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
            'name'   => '',
            'title'  => '',
            'org'    => '',
            'avatar' => '',
            // contacts
            'phone'   => '',
            'email'   => '',
            'website' => '',
            'address' => '',
            'whatsapp' => '',   // E.164 preferred (digits only or with +)
            'telegram' => '',   // @username or username
            'imo'      => '',   // imo username/id
            'skype'    => '',   // skype id
            'wechat'   => '',   // WeChat ID (username)
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
            'design'  => 'default',  // 'default' | 'minimal_qr'
        ], $atts, 'smartcc_contact');

        // ---------------------------
        // Helpers (vCard + MECARD)
        // ---------------------------
        $esc_v = function ($s) {
            $s = trim((string) $s);
            $s = str_replace(["\r", "\n"], ["", ""], $s);
            // vCard escaping
            $s = str_replace(["\\", ",", ";"], ["\\\\", "\\,", "\\;"], $s);
            return $s;
        };

        $esc_m = function ($s) {
            $s = trim((string) $s);
            $s = str_replace(["\r", "\n"], ["", ""], $s);
            // MECARD uses ; and : as separators
            $s = str_replace(["\\", ";", ":"], ["\\\\", "\\;", "\\:"], $s);
            return $s;
        };

        $name_raw = $a['name'] ?: 'Contact';

        // Split name into given + family for better vCard importer compatibility
        $parts  = preg_split('/\s+/', trim($name_raw));
        $given  = $parts[0] ?? $name_raw;
        $family = (count($parts) > 1) ? implode(' ', array_slice($parts, 1)) : '';

        $phone_clean = '';
        if (!empty($a['phone'])) {
            $phone_clean = preg_replace('/[^\d+]/', '', (string) $a['phone']);
        }

        // ---------------------------
        // vCard 3.0 (for .vcf download)
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
            // Keep it simple for Android importers
            $v_lines[] = "TEL;TYPE=CELL:" . $esc_v($phone_clean);
        }

        if (!empty($a['email'])) {
            $v_lines[] = "EMAIL;TYPE=INTERNET:" . $esc_v($a['email']);
        }

        if (!empty($a['website'])) {
            $v_lines[] = "URL:" . $esc_v($a['website']);
        }

        if (!empty($a['address'])) {
            // POBOX;EXT;STREET;LOCALITY;REGION;POSTAL;COUNTRY
            // Put full address in STREET slot
            $v_lines[] = "ADR;TYPE=WORK:;;" . $esc_v($a['address']) . ";;;;";
        }

        $v_lines[] = "END:VCARD";

        // CRLF is safest for .vcf files
        $vcard_file = implode("\r\n", $v_lines) . "\r\n";
        $vcard_url  = 'data:text/vcard;charset=utf-8,' . rawurlencode($vcard_file);
        $download_name = sanitize_title($name_raw ?: 'contact') . '.vcf';

        // ---------------------------
        // MECARD (for QR scan → Save Contact on Android)
        // ---------------------------
        $mecard = "MECARD:";
        $mecard .= "N:" . $esc_m($name_raw) . ";";

        if (!empty($phone_clean)) {
            $mecard .= "TEL:" . $esc_m($phone_clean) . ";";
        }
        if (!empty($a['email'])) {
            $mecard .= "EMAIL:" . $esc_m($a['email']) . ";";
        }
        if (!empty($a['address'])) {
            $mecard .= "ADR:" . $esc_m($a['address']) . ";";
        }
        if (!empty($a['website'])) {
            $mecard .= "URL:" . $esc_m($a['website']) . ";";
        }

        $mecard .= ";"; // terminator

        // ---------------------------
        // Links (chips)
        // ---------------------------
        $links = [];
        if (!empty($a['email'])) {
            $links[] = ['label' => $a['email'], 'href' => 'mailto:' . $a['email'], 'type' => 'email'];
        }
        if (!empty($a['phone'])) {
            $links[] = ['label' => $a['phone'], 'href' => 'tel:' . $a['phone'], 'type' => 'phone'];
        }
        if (!empty($a['website'])) {
            $links[] = ['label' => 'Website', 'href' => $a['website'], 'type' => 'website'];
        }
        if (!empty($a['address'])) {
            $links[] = ['label' => 'Address', 'href' => 'https://maps.google.com/?q=' . rawurlencode($a['address']), 'type' => 'address'];
        }
        if (!empty($a['whatsapp'])) {
            $links[] = ['label' => 'WhatsApp', 'href' => 'https://wa.me/' . preg_replace('/\D/', '', $a['whatsapp']), 'type' => 'whatsapp'];
        }
        if (!empty($a['telegram'])) {
            $links[] = ['label' => 'Telegram', 'href' => 'https://t.me/' . ltrim($a['telegram'], '@'), 'type' => 'telegram'];
        }
        if (!empty($a['imo'])) {
            $links[] = ['label' => 'imo', 'href' => 'im:' . $a['imo'], 'type' => 'imo'];
        }
        if (!empty($a['skype'])) {
            $links[] = ['label' => 'Skype', 'href' => 'skype:' . $a['skype'] . '?chat', 'type' => 'skype'];
        }
        if (!empty($a['wechat'])) {
            $links[] = ['label' => 'WeChat', 'href' => 'weixin://dl/add?username=' . $a['wechat'], 'type' => 'wechat'];
        }

        // ---------------------------
        // Minimal design branch
        // ---------------------------
        if (strtolower($a['design']) === 'minimal_qr') {
            $name   = esc_html($name_raw);
            $phone  = esc_html($a['phone']);
            $email  = esc_html($a['email']);
            $avatar = esc_url($a['avatar']);
            $qr_src = esc_url($a['qr_url']); // user-provided QR image URL

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
            // explicit override always wins
            $payload = (string) $a['qr_text'];
        } else {
            switch (strtolower($a['qr_for'])) {
                case 'whatsapp':
                    if (!empty($a['whatsapp'])) {
                        $payload = 'https://wa.me/' . preg_replace('/\D/', '', $a['whatsapp']);
                    }
                    break;

                case 'telegram':
                    if (!empty($a['telegram'])) {
                        $payload = 'https://t.me/' . ltrim($a['telegram'], '@');
                    }
                    break;

                case 'imo':
                    if (!empty($a['imo'])) {
                        $payload = 'im:' . $a['imo'];
                    }
                    break;

                case 'skype':
                    if (!empty($a['skype'])) {
                        $payload = 'skype:' . $a['skype'] . '?chat';
                    }
                    break;

                case 'wechat':
                    if (!empty($a['wechat'])) {
                        $payload = 'weixin://dl/add?username=' . $a['wechat'];
                    }
                    break;

                case 'url':
                    if (!empty($a['website'])) {
                        $payload = $a['website'];
                    } elseif (!empty($a['email'])) {
                        $payload = 'mailto:' . $a['email'];
                    } elseif (!empty($a['phone'])) {
                        $payload = 'tel:' . $a['phone'];
                    }
                    break;

                case 'vcard':
                default:
                    // IMPORTANT: Use MECARD for QR (best Android "Save Contact" support)
                    $payload = $mecard;
                    break;
            }
        }

        // ---------------------------
        // QR image source
        // ---------------------------
        $qr_src = '';
        if (!empty($a['qr_url'])) {
            $qr_src = $a['qr_url']; // trust user-provided QR image
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
