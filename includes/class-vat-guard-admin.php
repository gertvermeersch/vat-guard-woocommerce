<?php
// VAT Guard for WooCommerce Admin Page
if (!defined('ABSPATH')) {
    exit;
}

class EU_VAT_Guard_Admin
{
    public static function register_settings()
    {
        register_setting('eu_vat_guard_options', 'eu_vat_guard_require_company', [
            'type' => 'boolean',
            'default' => true
        ]);
        register_setting('eu_vat_guard_options', 'eu_vat_guard_require_vat', [
            'type' => 'boolean',
            'default' => true
        ]);
        register_setting('eu_vat_guard_options', 'eu_vat_guard_require_vies', [
            'type' => 'boolean',
            'default' => false
        ]);
        register_setting('eu_vat_guard_options', 'eu_vat_guard_ignore_vies_error', [
            'type' => 'boolean',
            'default' => false
        ]);
        register_setting('eu_vat_guard_options', 'eu_vat_guard_enable_block_checkout', [
            'type' => 'boolean',
            'default' => true
        ]);
    }

    public static function add_admin_menu()
    {
        add_submenu_page(
            'woocommerce',
            __('VAT Guard for WooCommerce', 'eu-vat-guard-for-woocommerce'),
            __('VAT Guard', 'eu-vat-guard-for-woocommerce'),
            'manage_woocommerce',
            'eu-vat-guard',
            array(__CLASS__, 'admin_page')
        );
    }

    public static function admin_page()
    {
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'settings';
        ?>
        <div class="wrap">
            <h1 style="display:flex;align-items:center;gap:12px;">
                <span style="font-size:2rem;">🛡️</span> <?php esc_html_e('EU VAT Guard for WooCommerce', 'eu-vat-guard-for-woocommerce'); ?>
            </h1>

            <div
                style="background:#e7f7e7;border-left:4px solid #46b450;padding:16px 24px;margin:24px 0 32px 0;font-size:1.1em;">
                <?php esc_html_e('Thank you for using VAT Guard for WooCommerce! Your support helps us keep improving. If you have feedback or suggestions, let us know.', 'eu-vat-guard-for-woocommerce'); ?>
            </div>

            <h2 class="nav-tab-wrapper">
                <a href="?page=eu-vat-guard&tab=settings"
                    class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Settings', 'eu-vat-guard-for-woocommerce'); ?>
                </a>
                <a href="?page=eu-vat-guard&tab=documentation"
                    class="nav-tab <?php echo $active_tab == 'documentation' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('How It Works', 'eu-vat-guard-for-woocommerce'); ?>
                </a>
            </h2>

            <?php if ($active_tab == 'settings'): ?>
                <?php self::render_settings_tab(); ?>
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
            <?php settings_fields('eu_vat_guard_options'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable Block-based Checkout Support', 'eu-vat-guard-for-woocommerce'); ?></th>
                    <td>
                        <input type="checkbox" name="eu_vat_guard_enable_block_checkout" value="1" <?php checked(1, get_option('eu_vat_guard_enable_block_checkout', 0)); ?> />
                        <label
                            for="eu_vat_guard_enable_block_checkout"><?php esc_html_e('Enable support for WooCommerce Block-based Checkout (Cart & Checkout Blocks)', 'eu-vat-guard-for-woocommerce'); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Require Company Name', 'eu-vat-guard-for-woocommerce'); ?></th>
                    <td>
                        <input type="checkbox" name="eu_vat_guard_require_company" value="1" <?php checked(1, get_option('eu_vat_guard_require_company', 1)); ?> />
                        <label
                            for="eu_vat_guard_require_company"><?php esc_html_e('Make company name a required field', 'eu-vat-guard-for-woocommerce'); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Require VAT Number - only sell B2B', 'eu-vat-guard-for-woocommerce'); ?></th>
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
                    <li><?php esc_html_e('VAT number country is different from your store\'s base country', 'eu-vat-guard-for-woocommerce'); ?></li>
                    <li><?php esc_html_e('Shipping method is NOT local pickup', 'eu-vat-guard-for-woocommerce'); ?></li>
                    <li><?php esc_html_e('Billing and shipping countries match the VAT number country', 'eu-vat-guard-for-woocommerce'); ?></li>
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
                        <?php esc_html_e('Verifies the VAT number exists in the official EU VIES database', 'eu-vat-guard-for-woocommerce'); ?></li>
                </ol>
            </div>

            <div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; margin: 20px 0; border-radius: 5px;">
                <h3 style="margin-top: 0;"><?php esc_html_e('🚫 When VAT is NOT Exempted', 'eu-vat-guard-for-woocommerce'); ?></h3>
                <ul style="margin-left: 20px;">
                    <li><?php esc_html_e('No VAT number provided', 'eu-vat-guard-for-woocommerce'); ?></li>
                    <li><?php esc_html_e('Invalid VAT number format', 'eu-vat-guard-for-woocommerce'); ?></li>
                    <li><?php esc_html_e('VAT number country same as store base country', 'eu-vat-guard-for-woocommerce'); ?></li>
                    <li><?php esc_html_e('Local pickup shipping method selected', 'eu-vat-guard-for-woocommerce'); ?></li>
                    <li><?php esc_html_e('Billing/shipping country doesn\'t match VAT number country', 'eu-vat-guard-for-woocommerce'); ?></li>
                    <li><?php esc_html_e('VIES validation fails (if enabled)', 'eu-vat-guard-for-woocommerce'); ?></li>
                </ul>
            </div>

            <div style="background: #d1ecf1; border: 1px solid #bee5eb; padding: 20px; margin: 20px 0; border-radius: 5px;">
                <h3 style="margin-top: 0;"><?php esc_html_e('📋 Supported Features', 'eu-vat-guard-for-woocommerce'); ?></h3>
                <ul style="margin-left: 20px;">
                    <li><?php esc_html_e('Classic WooCommerce checkout', 'eu-vat-guard-for-woocommerce'); ?></li>
                    <li><?php esc_html_e('Block-based checkout (Cart & Checkout blocks)', 'eu-vat-guard-for-woocommerce'); ?></li>
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
                        <?php esc_html_e('Enable "Require VAT Number" to sell only to businesses', 'eu-vat-guard-for-woocommerce'); ?></li>
                    <li><strong><?php esc_html_e('VIES Validation:', 'eu-vat-guard-for-woocommerce'); ?></strong>
                        <?php esc_html_e('Enable for stricter validation, but consider enabling "Ignore VIES errors" for better user experience when the service is down', 'eu-vat-guard-for-woocommerce'); ?>
                    </li>
                    <li><strong><?php esc_html_e('Block Checkout:', 'eu-vat-guard-for-woocommerce'); ?></strong>
                        <?php esc_html_e('Enable if you\'re using WooCommerce\'s new block-based checkout', 'eu-vat-guard-for-woocommerce'); ?></li>
                </ul>
            </div>

            <div style="background: #e2e3e5; border: 1px solid #d6d8db; padding: 20px; margin: 20px 0; border-radius: 5px;">
                <h3 style="margin-top: 0;"><?php esc_html_e('📞 Need Help?', 'eu-vat-guard-for-woocommerce'); ?></h3>
                <p><?php esc_html_e('If you encounter any issues or need assistance with VAT compliance, please contact our support team. We\'re here to help ensure your store meets all EU VAT requirements.', 'eu-vat-guard-for-woocommerce'); ?>
                </p>
            </div>
        </div>
        <?php
    }
}

if (is_admin()) {
    add_action('admin_menu', array('EU_VAT_Guard_Admin', 'add_admin_menu'));
    add_action('admin_init', array('EU_VAT_Guard_Admin', 'register_settings'));
}