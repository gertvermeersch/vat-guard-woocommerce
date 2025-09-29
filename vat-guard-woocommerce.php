<?php
/*
Plugin Name: VAT Guard for WooCommerce
Description: Manage EU VAT numbers and company information for WooCommerce customers. Adds company and VAT fields to registration, account, and checkout, and provides admin tools for VAT management.
Version: 1.0.0
Author: Gert Vermeersch
Author URI: https://stormlabs.be/
License: GPL2
Text Domain: vat-guard-woocommerce
Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Load text domain for translations
add_action('plugins_loaded', function() {
    load_plugin_textdomain('vat-guard-woocommerce', false, dirname(plugin_basename(__FILE__)) . '/languages/');
});

// Include main plugin class
require_once plugin_dir_path(__FILE__) . 'includes/class-vat-guard-woocommerce.php';

// Initialize the plugin
add_action('plugins_loaded', function() {
    if (class_exists('VAT_Guard_WooCommerce')) {
        VAT_Guard_WooCommerce::instance();
    }
}, 20); // Load after text domain
