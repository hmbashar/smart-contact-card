<?php
/**
 * Plugin Name:  Smart Contact Card
 * Description:  Shareable contact cards with QR + vCard via shortcode, Gutenberg block, and Elementor widget.
 * Version:      1.0.0
 * Author:       Md Abul Bashar
 * Text Domain:  smart-contact-card
 * Prefix:       SMARTCC_
 * Namespace:    Smartcc
 */

if (!defined('ABSPATH')) exit;

define('SMARTCC_FILE', __FILE__);
define('SMARTCC_DIR', plugin_dir_path(__FILE__));
define('SMARTCC_URL', plugin_dir_url(__FILE__));

$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
} else {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p><strong>Smart Contact Card:</strong> Run <code>composer install</code> before activation.</p></div>';
    });
    return;
}

Smartcc\Plugin::init();

// register_activation_hook(__FILE__, function () {
//     Smartcc\Plugin::activate();
// });
// register_deactivation_hook(__FILE__, function () {
//     Smartcc\Plugin::deactivate();
// });
