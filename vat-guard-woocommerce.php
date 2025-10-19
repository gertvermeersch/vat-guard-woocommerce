<?php
/*
Plugin Name: EU VAT Guard for WooCommerce
Description: Manage EU VAT numbers and company information for WooCommerce customers and B2B. Adds company and VAT fields to registration, account, and checkout, exempts VAT (reverse charge) where applicable and provides admin tools for VAT management.
Version: 1.2.1
Author: Stormlabs
Author URI: https://stormlabs.be/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: eu-vat-guard-for-woocommerce
Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include main plugin class
require_once plugin_dir_path(__FILE__) . 'includes/class-vat-guard.php';

// Include VAT rate importer (admin only)
if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'includes/class-vat-guard-rate-importer.php';
}

// Initialize the plugin
add_action('plugins_loaded', function () {
    if (class_exists('EU_VAT_Guard')) {
        EU_VAT_Guard::instance();
    }
}, 20); // Load after text domain
