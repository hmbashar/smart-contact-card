<?php
namespace Smartcc\Shortcode;

use Smartcc\Services\VCardService;
use Smartcc\Services\QrCodeService;

class ContactShortcode
{
    public function __construct(
        private VCardService $vcard,
        private QrCodeService $qr
    ) {}

    public function register(): void
    {
        add_shortcode('smartcc_contact', [$this, 'render']);
    }

    public function render($atts, $content = ''): string
    {
        $a = shortcode_atts([
            'name' => '', 'title' => '', 'org' => '',
            'phone' => '', 'email' => '', 'website' => '', 'address' => '',
            'whatsapp' => '', 'telegram' => '', 'imo' => '', 'skype' => '',
            'qr' => 'inline_vcard',          // inline_vcard | custom
            'qrtext' => '',                  // used when qr=custom
            'button' => 'Save Contact (.vcf)',
            'layout' => 'card',              // card | inline
        ], $atts, 'smartcc_contact');

        $vcard = $this->vcard->build($a);
        $dataUrl = $this->vcard->dataUrl($vcard);

        $links = [];
        if ($a['email'])    $links[] = ['label'=>$a['email'], 'href'=>'mailto:' . $a['email']];
        if ($a['phone'])    $links[] = ['label'=>$a['phone'], 'href'=>'tel:' . $a['phone']];
        if ($a['website'])  $links[] = ['label'=>'Website', 'href'=>$a['website']];
        if ($a['address'])  $links[] = ['label'=>'Address', 'href'=>'https://maps.google.com/?q=' . rawurlencode($a['address'])];
        if ($a['whatsapp']) $links[] = ['label'=>'WhatsApp','href'=>'https://wa.me/' . preg_replace('/\D/', '', $a['whatsapp'])];
        if ($a['telegram']) $links[] = ['label'=>'Telegram','href'=>'https://t.me/' . ltrim($a['telegram'], '@')];
        if ($a['imo'])      $links[] = ['label'=>'imo','href'=>'im:' . $a['imo']];
        if ($a['skype'])    $links[] = ['label'=>'Skype','href'=>'skype:' . $a['skype'] . '?chat'];

        $payload = ($a['qr'] === 'custom' && $a['qrtext']) ? $a['qrtext'] : $vcard;
        $qrSvg = $this->qr->svg($payload, 160);

        wp_enqueue_style('smartcc-card');

        $name  = esc_html($a['name'] ?: 'Contact');
        $title = esc_html($a['title']);
        $org   = esc_html($a['org']);
        $btn   = esc_html($a['button']);

        ob_start();
        if ($a['layout'] === 'inline') {
            include SMARTCC_DIR . 'src/View/partials/inline.php';
        } else {
            include SMARTCC_DIR . 'src/View/card.php';
        }
        return (string) ob_get_clean();
    }
}
