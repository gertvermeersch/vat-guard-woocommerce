<?php
/**
 * VAT Guard for WooCommerce Admin Page
 *
 * @package Stormlabs\EUVATGuard
 */

namespace Stormlabs\EUVATGuard;

if (!defined('ABSPATH')) {
    exit;
}

class VAT_Guard_Admin
{
    public static function register_settings()
    {
        // Basic settings group
        register_setting('eu_vat_guard_basic_options', 'eu_vat_guard_require_company', [
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean'
        ]);
        register_setting('eu_vat_guard_basic_options', 'eu_vat_guard_require_vat', [
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean'
        ]);
        register_setting('eu_vat_guard_basic_options', 'eu_vat_guard_require_vies', [
            'type' => 'boolean',
            'default' => false,
            'sanitize_callback' => 'rest_sanitize_boolean'
        ]);
        register_setting('eu_vat_guard_basic_options', 'eu_vat_guard_ignore_vies_error', [
            'type' => 'boolean',
            'default' => false,
            'sanitize_callback' => 'rest_sanitize_boolean'
        ]);
        register_setting('eu_vat_guard_basic_options', 'eu_vat_guard_enable_block_checkout', [
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean'
        ]);

        // Advanced settings group
        register_setting('eu_vat_guard_advanced_options', 'eu_vat_guard_disable_exemption', [
            'type' => 'boolean',
            'default' => false,
            'sanitize_callback' => 'rest_sanitize_boolean'
        ]);
        register_setting('eu_vat_guard_advanced_options', 'eu_vat_guard_company_label', [
            'type' => 'string',
            'default' => '',
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('eu_vat_guard_advanced_options', 'eu_vat_guard_vat_label', [
            'type' => 'string',
            'default' => '',
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('eu_vat_guard_advanced_options', 'eu_vat_guard_exemption_message', [
            'type' => 'string',
            'default' => '',
            'sanitize_callback' => 'sanitize_text_field'
        ]);

        // Hook to register WPML strings when settings are saved
        add_action('update_option_eu_vat_guard_company_label', [__CLASS__, 'register_wpml_string'], 10, 3);
        add_action('update_option_eu_vat_guard_vat_label', [__CLASS__, 'register_wpml_string'], 10, 3);
        add_action('update_option_eu_vat_guard_exemption_message', [__CLASS__, 'register_wpml_string'], 10, 3);
    }

    /**
     * Register custom strings with WPML when they're saved
     */
    public static function register_wpml_string($old_value, $new_value, $option_name)
    {
        // Only register if WPML is active and string is not empty
        if (!function_exists('icl_register_string') || empty($new_value)) {
            return;
        }

        // Map option names to WPML string names
        $string_names = [
            'eu_vat_guard_company_label' => 'Company Label',
            'eu_vat_guard_vat_label' => 'VAT Label',
            'eu_vat_guard_exemption_message' => 'Exemption Message'
        ];

        if (isset($string_names[$option_name])) {
            icl_register_string('EU VAT Guard', $string_names[$option_name], $new_value);
        }
    }

    public static function add_admin_menu()
    {
        // Add main menu page
        add_menu_page(
            __('EU VAT Guard', 'eu-vat-guard-for-woocommerce'),
            __('EU VAT Guard', 'eu-vat-guard-for-woocommerce'),
            'manage_woocommerce',
            'eu-vat-guard',
            array(__CLASS__, 'admin_page'),
            'dashicons-shield-alt',
            56
        );

        // Add settings submenu (duplicate main menu item)
        add_submenu_page(
            'eu-vat-guard',
            __('VAT Guard Settings', 'eu-vat-guard-for-woocommerce'),
            __('Settings', 'eu-vat-guard-for-woocommerce'),
            'manage_woocommerce',
            'eu-vat-guard',
            array(__CLASS__, 'admin_page')
        );
        //load menu items from the pro plugin
        do_action('eu_vat_guard_admin_page_content');
    }

    public static function admin_page()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Simple tab navigation, no data modification
        $active_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'settings';
        ?>
        <div class="wrap">
            <h1 style="display:flex;align-items:center;gap:12px;">
                <span style="font-size:2rem;">🛡️</span>
                <?php esc_html_e('EU VAT Guard for WooCommerce', 'eu-vat-guard-for-woocommerce'); ?>
            </h1>

            <div
                style="background:#e7f7e7;border-left:4px solid #46b450;padding:16px 24px;margin:24px 0 32px 0;font-size:1.1em;">
                <?php esc_html_e('Thank you for using VAT Guard for WooCommerce! Your support helps us keep improving. If you have feedback or suggestions, contact us at', 'eu-vat-guard-for-woocommerce'); ?>
                <a href="mailto:dev@stormlabs.be" style="color: #2271b1; text-decoration: none;">dev@stormlabs.be</a>
            </div>

            <h2 class="nav-tab-wrapper">
                <a href="?page=eu-vat-guard&tab=settings"
                    class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Settings', 'eu-vat-guard-for-woocommerce'); ?>
                </a>
                <a href="?page=eu-vat-guard&tab=advanced"
                    class="nav-tab <?php echo $active_tab == 'advanced' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Advanced', 'eu-vat-guard-for-woocommerce'); ?>
                </a>
                <a href="?page=eu-vat-guard&tab=documentation"
                    class="nav-tab <?php echo $active_tab == 'documentation' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('How It Works', 'eu-vat-guard-for-woocommerce'); ?>
                </a>
            </h2>

            <?php if ($active_tab == 'settings'): ?>
                <?php self::render_settings_tab(); ?>
            <?php elseif ($active_tab == 'advanced'): ?>
                <?php self::render_advanced_tab(); ?>
            <?php elseif ($active_tab == 'documentation'): ?>
                <?php self::render_documentation_tab(); ?>
            <?php endif; ?>
        </div>
        <?php
    }

    private static function render_settings_tab()
    {
        ?>
        <form method="post" action="options.php">
            <?php settings_fields('eu_vat_guard_basic_options'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable Block-based Checkout Support', 'eu-vat-guard-for-woocommerce'); ?>
                    </th>
                    <td>
                        <input type="checkbox" name="eu_vat_guard_enable_block_checkout" value="1" <?php checked(1, get_option('eu_vat_guard_enable_block_checkout', 0)); ?> />
                        <label
                            for="eu_vat_guard_enable_block_checkout"><?php esc_html_e('Enable support for WooCommerce Block-based Checkout (Cart & Checkout Blocks)', 'eu-vat-guard-for-woocommerce'); ?></label>
                        <p class="description">
                            <?php esc_html_e('Disable when using classic checkout or Cartflows', 'eu-vat-guard-for-woocommerce'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Require Company Name', 'eu-vat-guard-for-woocommerce'); ?></th>
                    <td>
                        <input type="checkbox" name="eu_vat_guard_require_company" value="1" <?php checked(1, get_option('eu_vat_guard_require_company', 1)); ?> />
                        <label
                            for="eu_vat_guard_require_company"><?php esc_html_e('Make company name a required field', 'eu-vat-guard-for-woocommerce'); ?></label>
                        <p class="description">
                            <?php esc_html_e('Warning: This does not have effect on the block based checkout. You need to manually enable the Company name field in the block editor.', 'eu-vat-guard-for-woocommerce'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Require VAT Number - only sell B2B', 'eu-vat-guard-for-woocommerce'); ?>
                    </th>
                    <td>
                        <input type="checkbox" name="eu_vat_guard_require_vat" value="1" <?php checked(1, get_option('eu_vat_guard_require_vat', 1)); ?> />
                        <label
                            for="eu_vat_guard_require_vat"><?php esc_html_e('Make VAT number a required field', 'eu-vat-guard-for-woocommerce'); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Validate VAT Number with VIES', 'eu-vat-guard-for-woocommerce'); ?></th>
                    <td>
                        <input type="checkbox" name="eu_vat_guard_require_vies" value="1" <?php checked(1, get_option('eu_vat_guard_require_vies', 0)); ?> />
                        <label
                            for="eu_vat_guard_require_vies"><?php esc_html_e('Check VAT number validity with the official VIES service', 'eu-vat-guard-for-woocommerce'); ?></label>
                        <p class="description">
                            <?php esc_html_e('VIES (VAT Information Exchange System) is a search engine (not a database) owned by the European Commission', 'eu-vat-guard-for-woocommerce'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Ignore VIES errors', 'eu-vat-guard-for-woocommerce'); ?></th>
                    <td>
                        <input type="checkbox" name="eu_vat_guard_ignore_vies_error" value="1" <?php checked(1, get_option('eu_vat_guard_ignore_vies_error', 0)); ?> />
                        <label
                            for="eu_vat_guard_ignore_vies_error"><?php esc_html_e('Allow checkout if VIES is unavailable or returns an error', 'eu-vat-guard-for-woocommerce'); ?></label>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <?php
    }

    private static function render_advanced_tab()
    {
        ?>
        <form method="post" action="options.php">
            <?php settings_fields('eu_vat_guard_advanced_options'); ?>

            <h2><?php esc_html_e('Exemption Rules', 'eu-vat-guard-for-woocommerce'); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Disable VAT Exemption', 'eu-vat-guard-for-woocommerce'); ?></th>
                    <td>
                        <input type="checkbox" name="eu_vat_guard_disable_exemption" value="1" <?php checked(1, get_option('eu_vat_guard_disable_exemption', 0)); ?> />
                        <label
                            for="eu_vat_guard_disable_exemption"><?php esc_html_e('Skip VAT exemption entirely and just register VAT numbers', 'eu-vat-guard-for-woocommerce'); ?></label>
                        <p class="description">
                            <?php esc_html_e('When enabled, VAT numbers will be collected and validated but no tax exemption will be applied.', 'eu-vat-guard-for-woocommerce'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e('Custom Labels & Messages', 'eu-vat-guard-for-woocommerce'); ?></h2>
            <p><?php esc_html_e('These labels and messages will be displayed on the checkout page. Can be translated (e.g. WPML String Translations)', 'eu-vat-guard-for-woocommerce'); ?>
            </p>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label
                            for="eu_vat_guard_company_label"><?php esc_html_e('Company Name Label', 'eu-vat-guard-for-woocommerce'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="eu_vat_guard_company_label" name="eu_vat_guard_company_label"
                            value="<?php echo esc_attr(get_option('eu_vat_guard_company_label', '')); ?>" class="regular-text"
                            placeholder="<?php esc_attr_e('Company Name', 'eu-vat-guard-for-woocommerce'); ?>" />
                        <p class="description">
                            <?php esc_html_e('Custom label for the company name field. Leave empty to use default.', 'eu-vat-guard-for-woocommerce'); ?>
                            <?php if (function_exists('icl_register_string')): ?>
                                <br><em><?php esc_html_e('This string will be registered with WPML for translation.', 'eu-vat-guard-for-woocommerce'); ?></em>
                            <?php endif; ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label
                            for="eu_vat_guard_vat_label"><?php esc_html_e('VAT Number Label', 'eu-vat-guard-for-woocommerce'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="eu_vat_guard_vat_label" name="eu_vat_guard_vat_label"
                            value="<?php echo esc_attr(get_option('eu_vat_guard_vat_label', '')); ?>" class="regular-text"
                            placeholder="<?php esc_attr_e('VAT Number', 'eu-vat-guard-for-woocommerce'); ?>" />
                        <p class="description">
                            <?php esc_html_e('Custom label for the VAT number field. Leave empty to use default.', 'eu-vat-guard-for-woocommerce'); ?>
                            <?php if (function_exists('icl_register_string')): ?>
                                <br><em><?php esc_html_e('This string will be registered with WPML for translation.', 'eu-vat-guard-for-woocommerce'); ?></em>
                            <?php endif; ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label
                            for="eu_vat_guard_exemption_message"><?php esc_html_e('VAT Exemption Message', 'eu-vat-guard-for-woocommerce'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="eu_vat_guard_exemption_message" name="eu_vat_guard_exemption_message"
                            value="<?php echo esc_attr(get_option('eu_vat_guard_exemption_message', '')); ?>"
                            class="regular-text"
                            placeholder="<?php esc_attr_e('VAT exempt for this order', 'eu-vat-guard-for-woocommerce'); ?>" />
                        <p class="description">
                            <?php esc_html_e('Custom message shown when VAT exemption is applied. Leave empty to use default.', 'eu-vat-guard-for-woocommerce'); ?>
                            <?php if (function_exists('icl_register_string')): ?>
                                <br><em><?php esc_html_e('This string will be registered with WPML for translation.', 'eu-vat-guard-for-woocommerce'); ?></em>
                            <?php endif; ?>
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>

        <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; margin: 20px 0; border-radius: 5px;">
            <h3 style="margin-top: 0;"><?php esc_html_e('⚙️ Advanced Settings Info', 'eu-vat-guard-for-woocommerce'); ?></h3>
            <ul style="margin-left: 20px;">
                <li><strong><?php esc_html_e('Disable VAT Exemption:', 'eu-vat-guard-for-woocommerce'); ?></strong>
                    <?php esc_html_e('Useful if you only want to collect VAT numbers for record-keeping without applying tax exemptions.', 'eu-vat-guard-for-woocommerce'); ?>
                </li>
                <li><strong><?php esc_html_e('Custom Labels:', 'eu-vat-guard-for-woocommerce'); ?></strong>
                    <?php esc_html_e('Override default field labels to match your store\'s terminology or language preferences.', 'eu-vat-guard-for-woocommerce'); ?>
                </li>
                <li><strong><?php esc_html_e('Custom Messages:', 'eu-vat-guard-for-woocommerce'); ?></strong>
                    <?php esc_html_e('Customize the VAT exemption message shown to customers during checkout.', 'eu-vat-guard-for-woocommerce'); ?>
                </li>
                <li><strong><?php esc_html_e('WPML Compatibility:', 'eu-vat-guard-for-woocommerce'); ?></strong>
                    <?php esc_html_e('Custom strings are automatically registered with WPML for translation when saved.', 'eu-vat-guard-for-woocommerce'); ?>
                </li>
            </ul>
        </div>

        <?php if (function_exists('icl_register_string')): ?>
            <div style="background: #d1ecf1; border: 1px solid #bee5eb; padding: 20px; margin: 20px 0; border-radius: 5px;">
                <h3 style="margin-top: 0;"><?php esc_html_e('🌍 WPML Translation', 'eu-vat-guard-for-woocommerce'); ?></h3>
                <p><?php esc_html_e('WPML is active! Custom strings will be automatically registered for translation when you save them. You can translate them in:', 'eu-vat-guard-for-woocommerce'); ?>
                </p>
                <p><strong>WPML → String Translation → EU VAT Guard</strong></p>
            </div>
        <?php endif; ?>
    <?php
    }

    private static function render_documentation_tab()
    {
        ?>
        <div style="max-width: 800px;">
            <h2><?php esc_html_e('How VAT Guard Works', 'eu-vat-guard-for-woocommerce'); ?></h2>

            <div style="background: #f9f9f9; border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 5px;">
                <h3 style="margin-top: 0;"><?php esc_html_e('🎯 Purpose', 'eu-vat-guard-for-woocommerce'); ?></h3>
                <p><?php esc_html_e('VAT Guard helps you comply with EU VAT regulations by automatically managing VAT exemptions for valid B2B transactions between EU member states.', 'eu-vat-guard-for-woocommerce'); ?>
                </p>
            </div>

            <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; margin: 20px 0; border-radius: 5px;">
                <h3 style="margin-top: 0;"><?php esc_html_e('⚖️ VAT Exemption Rules', 'eu-vat-guard-for-woocommerce'); ?></h3>
                <p><?php esc_html_e('VAT exemption is automatically applied when ALL of the following conditions are met:', 'eu-vat-guard-for-woocommerce'); ?>
                </p>
                <ul style="margin-left: 20px;">
                    <li><?php esc_html_e('Customer provides a valid EU VAT number', 'eu-vat-guard-for-woocommerce'); ?></li>
                    <li><?php esc_html_e('VAT number country is different from your store\'s base country', 'eu-vat-guard-for-woocommerce'); ?>
                    </li>
                    <li><?php esc_html_e('Shipping method is NOT local pickup', 'eu-vat-guard-for-woocommerce'); ?></li>
                    <li><?php esc_html_e('Billing and shipping countries match the VAT number country', 'eu-vat-guard-for-woocommerce'); ?>
                    </li>
                </ul>
                <p><strong><?php esc_html_e('Important:', 'eu-vat-guard-for-woocommerce'); ?></strong>
                    <?php esc_html_e('If local pickup is selected, VAT is always charged regardless of the VAT number.', 'eu-vat-guard-for-woocommerce'); ?>
                </p>
            </div>

            <div style="background: #e8f4fd; border: 1px solid #bee5eb; padding: 20px; margin: 20px 0; border-radius: 5px;">
                <h3 style="margin-top: 0;"><?php esc_html_e('🔍 VAT Number Validation', 'eu-vat-guard-for-woocommerce'); ?></h3>
                <p><?php esc_html_e('The plugin validates VAT numbers in two stages:', 'eu-vat-guard-for-woocommerce'); ?></p>
                <ol style="margin-left: 20px;">
                    <li><strong><?php esc_html_e('Format Validation:', 'eu-vat-guard-for-woocommerce'); ?></strong>
                        <?php esc_html_e('Checks if the VAT number matches the correct format for each EU country', 'eu-vat-guard-for-woocommerce'); ?>
                    </li>
                    <li><strong><?php esc_html_e('VIES Validation (Optional):', 'eu-vat-guard-for-woocommerce'); ?></strong>
                        <?php esc_html_e('Verifies the VAT number exists in the official EU VIES database', 'eu-vat-guard-for-woocommerce'); ?>
                    </li>
                </ol>
            </div>

            <div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; margin: 20px 0; border-radius: 5px;">
                <h3 style="margin-top: 0;"><?php esc_html_e('🚫 When VAT is NOT Exempted', 'eu-vat-guard-for-woocommerce'); ?>
                </h3>
                <ul style="margin-left: 20px;">
                    <li><?php esc_html_e('No VAT number provided', 'eu-vat-guard-for-woocommerce'); ?></li>
                    <li><?php esc_html_e('Invalid VAT number format', 'eu-vat-guard-for-woocommerce'); ?></li>
                    <li><?php esc_html_e('VAT number country same as store base country', 'eu-vat-guard-for-woocommerce'); ?>
                    </li>
                    <li><?php esc_html_e('Local pickup shipping method selected', 'eu-vat-guard-for-woocommerce'); ?></li>
                    <li><?php esc_html_e('Billing/shipping country doesn\'t match VAT number country', 'eu-vat-guard-for-woocommerce'); ?>
                    </li>
                    <li><?php esc_html_e('VIES validation fails (if enabled)', 'eu-vat-guard-for-woocommerce'); ?></li>
                </ul>
            </div>

            <div style="background: #d1ecf1; border: 1px solid #bee5eb; padding: 20px; margin: 20px 0; border-radius: 5px;">
                <h3 style="margin-top: 0;"><?php esc_html_e('📋 Supported Features', 'eu-vat-guard-for-woocommerce'); ?></h3>
                <ul style="margin-left: 20px;">
                    <li><?php esc_html_e('Classic WooCommerce checkout', 'eu-vat-guard-for-woocommerce'); ?></li>
                    <li><?php esc_html_e('Block-based checkout (Cart & Checkout blocks)', 'eu-vat-guard-for-woocommerce'); ?>
                    </li>
                    <li><?php esc_html_e('Customer registration and account pages', 'eu-vat-guard-for-woocommerce'); ?></li>
                    <li><?php esc_html_e('Admin order management', 'eu-vat-guard-for-woocommerce'); ?></li>
                    <li><?php esc_html_e('Email notifications', 'eu-vat-guard-for-woocommerce'); ?></li>
                    <li><?php esc_html_e('All 27 EU member states VAT formats', 'eu-vat-guard-for-woocommerce'); ?></li>
                </ul>
            </div>

            <div style="background: #fff2cc; border: 1px solid #ffeaa7; padding: 20px; margin: 20px 0; border-radius: 5px;">
                <h3 style="margin-top: 0;"><?php esc_html_e('⚙️ Configuration Tips', 'eu-vat-guard-for-woocommerce'); ?></h3>
                <ul style="margin-left: 20px;">
                    <li><strong><?php esc_html_e('B2B Only:', 'eu-vat-guard-for-woocommerce'); ?></strong>
                        <?php esc_html_e('Enable "Require VAT Number" to sell only to businesses', 'eu-vat-guard-for-woocommerce'); ?>
                    </li>
                    <li><strong><?php esc_html_e('VIES Validation:', 'eu-vat-guard-for-woocommerce'); ?></strong>
                        <?php esc_html_e('Enable for stricter validation, but consider enabling "Ignore VIES errors" for better user experience when the service is down', 'eu-vat-guard-for-woocommerce'); ?>
                    </li>
                    <li><strong><?php esc_html_e('Block Checkout:', 'eu-vat-guard-for-woocommerce'); ?></strong>
                        <?php esc_html_e('Enable if you\'re using WooCommerce\'s new block-based checkout', 'eu-vat-guard-for-woocommerce'); ?>
                    </li>
                </ul>
            </div>

            <div style="background: #e2e3e5; border: 1px solid #d6d8db; padding: 20px; margin: 20px 0; border-radius: 5px;">
                <h3 style="margin-top: 0;"><?php esc_html_e('📞 Need Help?', 'eu-vat-guard-for-woocommerce'); ?></h3>
                <p><?php esc_html_e('If you encounter any issues or need assistance with VAT compliance, please contact our support team. We\'re here to help ensure your store meets all EU VAT requirements.', 'eu-vat-guard-for-woocommerce'); ?>
                </p>
                <p><strong><?php esc_html_e('Email:', 'eu-vat-guard-for-woocommerce'); ?></strong>
                    <a href="mailto:dev@stormlabs.be" style="color: #2271b1;">dev@stormlabs.be</a>
                </p>
                <p><strong><?php esc_html_e('Website:', 'eu-vat-guard-for-woocommerce'); ?></strong>
                    <a href="https://stormlabs.be/" target="_blank" style="color: #2271b1;">stormlabs.be</a>
                </p>
            </div>

        </div>
        <?php
    }
}

if (is_admin() && !wp_doing_ajax()) {
    add_action('admin_menu', array('Stormlabs\EUVATGuard\VAT_Guard_Admin', 'add_admin_menu'));
    add_action('admin_init', array('Stormlabs\EUVATGuard\VAT_Guard_Admin', 'register_settings'));
}