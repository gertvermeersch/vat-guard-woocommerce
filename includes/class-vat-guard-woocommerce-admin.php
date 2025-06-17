<?php
// VAT Guard for WooCommerce Admin Page
if (!defined('ABSPATH')) {
    exit;
}

class VAT_Guard_WooCommerce_Admin {
    public static function register_settings() {
        register_setting('vat_guard_woocommerce_options', 'vat_guard_woocommerce_require_company', [
            'type' => 'boolean',
            'default' => true
        ]);
        register_setting('vat_guard_woocommerce_options', 'vat_guard_woocommerce_require_vat', [
            'type' => 'boolean',
            'default' => true
        ]);
        register_setting('vat_guard_woocommerce_options', 'vat_guard_woocommerce_require_vies', [
            'type' => 'boolean',
            'default' => false
        ]);
    }

    public static function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('VAT Guard for WooCommerce', 'vat-guard-woocommerce'),
            __('VAT Guard', 'vat-guard-woocommerce'),
            'manage_woocommerce',
            'vat-guard-woocommerce',
            array(__CLASS__, 'admin_page')
        );
    }

    public static function admin_page() {
        ?>
        <div class="wrap">
            <h1 style="display:flex;align-items:center;gap:12px;">
                <span style="font-size:2rem;">🛡️</span> <?php _e('VAT Guard for WooCommerce', 'vat-guard-woocommerce'); ?>
            </h1>
            <div style="background:#e7f7e7;border-left:4px solid #46b450;padding:16px 24px;margin:24px 0 32px 0;font-size:1.1em;">
                <?php _e('Thank you for using VAT Guard for WooCommerce! Your support helps us keep improving. If you have feedback or suggestions, let us know.', 'vat-guard-woocommerce'); ?>
            </div>
            <form method="post" action="options.php">
                <?php settings_fields('vat_guard_woocommerce_options'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php _e('Require Company Name', 'vat-guard-woocommerce'); ?></th>
                        <td>
                            <input type="checkbox" name="vat_guard_woocommerce_require_company" value="1" <?php checked(1, get_option('vat_guard_woocommerce_require_company', 1)); ?> />
                            <label for="vat_guard_woocommerce_require_company"><?php _e('Make company name a required field', 'vat-guard-woocommerce'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Require VAT Number', 'vat-guard-woocommerce'); ?></th>
                        <td>
                            <input type="checkbox" name="vat_guard_woocommerce_require_vat" value="1" <?php checked(1, get_option('vat_guard_woocommerce_require_vat', 1)); ?> />
                            <label for="vat_guard_woocommerce_require_vat"><?php _e('Make VAT number a required field', 'vat-guard-woocommerce'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Validate VAT Number with VIES', 'vat-guard-woocommerce'); ?></th>
                        <td>
                            <input type="checkbox" name="vat_guard_woocommerce_require_vies" value="1" <?php checked(1, get_option('vat_guard_woocommerce_require_vies', 0)); ?> />
                            <label for="vat_guard_woocommerce_require_vies"><?php _e('Check VAT number validity with the official VIES service', 'vat-guard-woocommerce'); ?></label>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

add_action('admin_menu', array('VAT_Guard_WooCommerce_Admin', 'add_admin_menu'));
add_action('admin_init', array('VAT_Guard_WooCommerce_Admin', 'register_settings'));
