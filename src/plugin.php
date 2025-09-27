<?php
namespace Smartcc;

use Smartcc\Shortcode\ContactShortcode;

class Plugin
{
    public static function init(): void
    {
        add_action('plugins_loaded', [static::class, 'boot']);
    }

    public static function boot(): void
    {
        // Minimal front-end style
        add_action('wp_enqueue_scripts', function () {
            wp_register_style('smartcc-card', SMARTCC_URL . 'src/View/css/card.css', [], '0.1.0');
        });

        // Shortcode only (Elementor / Gutenberg folders are placeholders for now)
        (new ContactShortcode())->register();
    }
}
