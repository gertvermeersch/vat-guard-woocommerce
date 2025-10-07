<?php
/*
Plugin Name: EU VAT Guard for WooCommerce
Description: Manage EU VAT numbers and company information for WooCommerce customers and B2B. Adds company and VAT fields to registration, account, and checkout, exempts VAT (reverse charge) where applicable and provides admin tools for VAT management.
Version: 1.0.0
Author: Stormlabs
Author URI: https://stormlabs.be/
License: GPL2
Text Domain: eu-vat-guard
Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Load text domain for translations
//Deprecated
add_action('plugins_loaded', function() {
    load_plugin_textdomain('eu-vat-guard', false, dirname(plugin_basename(__FILE__)) . '/languages/');
});

// Include main plugin class
require_once plugin_dir_path(__FILE__) . 'includes/class-vat-guard.php';

// Initialize the plugin
add_action('plugins_loaded', function() {
    if (class_exists('EU_VAT_Guard')) {
        EU_VAT_Guard::instance();
    }
}, 20); // Load after text domain
