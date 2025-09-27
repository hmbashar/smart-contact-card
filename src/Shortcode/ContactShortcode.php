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
            'name'     => '',
            'title'    => '',
            'org'      => '',
            'avatar'   => '',  // NEW: optional avatar URL
            // contacts
            'phone'    => '',
            'email'    => '',
            'website'  => '',
            'address'  => '',
            'whatsapp' => '',
            'telegram' => '',
            'imo'      => '',
            'skype'    => '',
            // visuals / behavior
            'qr'       => 'auto',   // auto | off (ignored if qr_url is provided)
            'qr_url'   => '',       // provide your own QR image URL
            'layout'   => 'card',   // card | inline (inline template could be added later)
            'button'   => 'Save Contact (.vcf)',
        ], $atts, 'smartcc_contact');

        // Build a simple vCard (3.0)
        $e = fn($s) => str_replace(["\r","\n",",",";"], ["","","\\,", "\\;"], trim((string)$s));
        $name = $a['name'] ?: 'Contact';
        $lines = [
            'BEGIN:VCARD','VERSION:3.0',
            'N:;'.$e($name).';;;',
            'FN:'.$e($name),
        ];
        if ($a['org'])     $lines[] = 'ORG:'.$e($a['org']);
        if ($a['title'])   $lines[] = 'TITLE:'.$e($a['title']);
        if ($a['phone'])   $lines[] = 'TEL;TYPE=CELL,VOICE:'.$e($a['phone']);
        if ($a['email'])   $lines[] = 'EMAIL;TYPE=INTERNET:'.$e($a['email']);
        if ($a['website']) $lines[] = 'URL:'.$e($a['website']);
        if ($a['address']) $lines[] = 'ADR;TYPE=WORK:;;'.$e($a['address']).';;;';
        $lines[] = 'END:VCARD';
        $vcard = implode("\r\n", $lines);

        // Data URL for one-click download (no rewrite rules needed)
        $vcard_url  = 'data:text/vcard;charset=utf-8,' . rawurlencode($vcard);
        $download_name = sanitize_title($name ?: 'contact') . '.vcf';

        // Build links list
        $links = [];
        if ($a['email'])    $links[] = ['label'=>$a['email'],  'href'=>'mailto:'.$a['email'], 'type'=>'email'];
        if ($a['phone'])    $links[] = ['label'=>$a['phone'],  'href'=>'tel:'.$a['phone'], 'type'=>'phone'];
        if ($a['website'])  $links[] = ['label'=>'Website',    'href'=>$a['website'], 'type'=>'website'];
        if ($a['address'])  $links[] = ['label'=>'Address',    'href'=>'https://maps.google.com/?q='.rawurlencode($a['address']), 'type'=>'address'];
        if ($a['whatsapp']) $links[] = ['label'=>'WhatsApp',   'href'=>'https://wa.me/'.preg_replace('/\D/','', $a['whatsapp']), 'type'=>'whatsapp'];
        if ($a['telegram']) $links[] = ['label'=>'Telegram',   'href'=>'https://t.me/'.ltrim($a['telegram'],'@'), 'type'=>'telegram'];
        if ($a['imo'])      $links[] = ['label'=>'imo',        'href'=>'im:'.$a['imo'], 'type'=>'imo'];
        if ($a['skype'])    $links[] = ['label'=>'Skype',      'href'=>'skype:'.$a['skype'].'?chat', 'type'=>'skype'];

        // QR image logic (no PHP library calls)
        $qr_src = '';
        if ($a['qr_url']) {
            $qr_src = $a['qr_url'];
        } elseif ($a['qr'] !== 'off') {
            // Auto-generate via QuickChart (encodes the vCard text)
            $qr_src = 'https://quickchart.io/qr?size=200&margin=2&text=' . rawurlencode($vcard);
        }

        // View vars
        $vars = [
            'name'          => esc_html($name),
            'title'         => esc_html($a['title']),
            'org'           => esc_html($a['org']),
            'avatar'        => esc_url($a['avatar']),
            'links'         => $links,
            'button'        => esc_html($a['button']),
            'vcard_url'     => $vcard_url,
            'download_name' => $download_name,
            'qr_src'        => esc_url($qr_src),
        ];

        // Enqueue minimal CSS
        wp_enqueue_style('smartcc-card');

        // Render
        ob_start();
        extract($vars, EXTR_SKIP);
        include SMARTCC_DIR . 'src/View/card.php';
        return (string) ob_get_clean();
    }
}
