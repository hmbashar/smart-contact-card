<?php
namespace Smartcc;

use Smartcc\Shortcode\ContactShortcode;
use Smartcc\Elementor\Config;



class Plugin
{
    public static function init(): void
    {
        self::boot();
    }

    public static function boot(): void
    {
        // Minimal front-end style
        add_action('wp_enqueue_scripts', function () {
            wp_register_style('smartcc-card', SMARTCC_URL . 'src/View/css/card.css', [], '0.1.0');
        });

        // Shortcode only (Elementor / Gutenberg folders are placeholders for now)
        (new ContactShortcode())->register();
        
        // Elementor
        (new Config())->init();
    }

    public static function activate(): void
    {
        // Activation logic (if needed in future)
        // For now, we just ensure the plugin is ready
        flush_rewrite_rules();
    }

    public static function deactivate(): void
    {
        // Deactivation logic
        flush_rewrite_rules();
    }
}
