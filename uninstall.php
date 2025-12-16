<?php
/**
 * Uninstall Smart Contact Card
 *
 * @package Smartcc
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete options if any (currently the plugin doesn't store options, but this is for future use)
// Clean up any plugin-specific options
$option_names = [
    'smartcc_contact_card_widget',
];

foreach ($option_names as $option) {
    delete_option($option);
    
    // For multisite
    delete_site_option($option);
}

// Clear any cached data
wp_cache_flush();
