<?php
namespace Smartcc;

use Smartcc\Services\VCardService;
use Smartcc\Services\QrCodeService;
use Smartcc\Shortcode\ContactShortcode;
use Smartcc\Support\BlockRenderer;
use Smartcc\Elementor\Widgets\ContactCard as ElementorContactCard;

class Plugin
{
    public static function init(): void
    {
        add_action('plugins_loaded', [static::class, 'boot']);
    }

    public static function activate(): void
    {
        static::boot();
        flush_rewrite_rules();
    }

    public static function deactivate(): void
    {
        flush_rewrite_rules();
    }

    public static function boot(): void
    {
        // Assets (minimal, front-end)
        add_action('wp_enqueue_scripts', function () {
            wp_register_style('smartcc-card', SMARTCC_URL . 'src/View/css/card.css', [], '1.0.0');
        });

        // Shortcode
        (new ContactShortcode(new VCardService(), new QrCodeService()))->register();

        // Gutenberg (server render -> shortcode)
        add_action('init', function () {
            register_block_type(SMARTCC_DIR . 'blocks/contact-card', [
                'render_callback' => [BlockRenderer::class, 'render_contact_card']
            ]);
        });

        // Elementor
        add_action('elementor/widgets/register', function ($manager) {
            require_once SMARTCC_DIR . 'src/Elementor/Widgets/ContactCard.php';
            $manager->register(new ElementorContactCard());
        });
    }
}