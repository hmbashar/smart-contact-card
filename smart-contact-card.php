<?php
/**
 * Plugin Name:  Smart Contact Card
 * Description:  Shareable contact cards with QR + vCard via shortcode, Gutenberg block, and Elementor widget.
 * Version:      1.0.0
 * Author:       Md Abul Bashar
 * Author URI:   https://profiles.wordpress.org/hmbashar/
 * License:      GPL v2 or later
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  smart-contact-card
 * Prefix:       SMARTCC_
 * Namespace:    Smartcc
 */

if (!defined('ABSPATH')) exit;

/**
 * Main plugin class.
 *
 * @package Smartcc
 */
final class SmartContactCard
{
    /**
     * Singleton instance.
     *
     * @var SmartContactCard|null
     */
    private static $instance = null;

    /**
     * Get singleton instance.
     *
     * @return SmartContactCard
     */
    public static function get_instance(): SmartContactCard
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor is private to enforce singleton.
     */
    private function __construct()
    {
        $this->define_constants();
        $this->include_files();
        $this->init_hooks();
    }

    /**
     * Define plugin constants.
     */
    private function define_constants(): void
    {
        define('SMARTCC_VERSION', '1.0.0');
        define('SMARTCC_FILE', __FILE__);
        define('SMARTCC_DIR', plugin_dir_path(__FILE__));
        define('SMARTCC_URL', plugin_dir_url(__FILE__));
        define('SMARTCC_BASENAME', plugin_basename(__FILE__));
        define('SMARTCC_NAME', 'Smart Contact Card');
    }

    /**
     * Include necessary files.
     */
    private function include_files(): void
    {
        if (file_exists(SMARTCC_DIR . 'vendor/autoload.php')) {
            require_once SMARTCC_DIR . 'vendor/autoload.php';
        } else {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error"><p><strong>Smart Contact Card:</strong> Run <code>composer install</code> before activation.</p></div>';
            });
        }
    }

    /**
     * Register plugin hooks.
     */
    private function init_hooks(): void
    {
        add_action('plugins_loaded', [$this, 'plugin_loaded']);
        register_activation_hook(SMARTCC_FILE, [$this, 'activate']);
        register_deactivation_hook(SMARTCC_FILE, [$this, 'deactivate']);
    }

    /**
     * Actions after plugins_loaded.
     */
    public function plugin_loaded(): void
    {
        if (class_exists('\Smartcc\Plugin')) {
            \Smartcc\Plugin::init();
        }
    }

    /**
     * Plugin activation logic.
     */
    public function activate(): void
    {
        if (class_exists('\Smartcc\Plugin')) {
            \Smartcc\Plugin::activate();
        }
    }

    /**
     * Plugin deactivation logic.
     */
    public function deactivate(): void
    {
        if (class_exists('\Smartcc\Plugin')) {
            \Smartcc\Plugin::deactivate();
        }
    }
}

// Initialize the plugin.
if (!function_exists('smartcc_initialize')) {
    function smartcc_initialize()
    {
        return SmartContactCard::get_instance();
    }

    smartcc_initialize();
}
