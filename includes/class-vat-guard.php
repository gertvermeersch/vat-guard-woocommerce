<?php
/**
 * VAT Guard for WooCommerce Main Class
 *
 * @package Stormlabs\EUVATGuard
 */

namespace Stormlabs\EUVATGuard;

if (!defined('ABSPATH')) {
    exit;
}

class VAT_Guard
{
    /**
     * Show a VAT exempt notice in the order review totals if VAT is removed.
     */

    private static $instance = null;

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // Load block integration early if enabled (needs to be before woocommerce_init)
        if (!is_admin() || wp_doing_ajax()) {
            add_action('plugins_loaded', array($this, 'maybe_init_block_support'), 50);
        }



        // Fallback: also try on woocommerce_loaded if plugins_loaded doesn't work
        //add_action('woocommerce_loaded', array($this, 'maybe_init_block_support_fallback'));

        // Hook into WordPress init to set up the plugin after all plugins are loaded
        add_action('init', array($this, 'init'), 10);
    }

    /**
     * Maybe initialize block support early (before woocommerce_init)
     */
    public function maybe_init_block_support()
    {

        // Only proceed if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            //error_log('VAT Guard: WooCommerce class not found, skipping block support init');
            return;
        }
        // Load block integration if enabled - needs to happen early
        if (get_option('eu_vat_guard_enable_block_checkout', 0)) {
            $this->init_block_checkout_support();
        }
    }



    /**
     * Initialize the plugin - called on 'init' hook
     */
    public function init()
    {
        // Only proceed if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            return;
        }

        // Load dependencies based on context
        $this->load_dependencies();

        // Set up hooks based on current request context
        $this->setup_hooks();


    }

    /**
     * Load required dependencies based on context
     */
    private function load_dependencies()
    {
        // Always load VIES for validation (lightweight)
        if (!class_exists(__NAMESPACE__ . '\VAT_Guard_VIES')) {
            require_once plugin_dir_path(__FILE__) . 'class-vat-guard-vies.php';
        }

        if (!class_exists(__NAMESPACE__ . '\VAT_Guard_Account')) {
            require_once plugin_dir_path(__FILE__) . 'class-vat-guard-account.php';
            VAT_Guard_Account::instance();
        }

        // Load admin functionality only in admin
        if (is_admin()) {
            require_once plugin_dir_path(__FILE__) . 'class-vat-guard-admin.php';
            VAT_Guard_Admin::instance();
        }

        // Block integration is loaded earlier in maybe_init_block_support()
    }

    /**
     * Set up hooks based on current request context
     */
    private function setup_hooks()
    {
        // Frontend-specific hooks
        if (!is_admin() || wp_doing_ajax()) {
            $this->setup_frontend_hooks();

        }

        // Admin-specific hooks
        if (is_admin()) {
            $this->setup_admin();
        }

        // Email hooks (needed for both frontend and admin)
        add_action('woocommerce_email_customer_details', array($this, 'show_vat_in_emails'), 20, 4);
    }

    /**
     * Set up frontend-specific hooks
     */
    private function setup_frontend_hooks()
    {
        //register hooks for account and registration forms
        VAT_Guard_Account::instance()->setup_hooks();
        // Checkout hooks - only load when actually needed
        add_action('wp', array($this, 'maybe_setup_checkout_hooks'));
    }

    /**
     * Set up admin-specific hooks
    //  */
    // private function setup_admin_hooks()
    // {
    //     // Order display hooks
    //     add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'show_vat_in_admin_order'));

    // }

    /**
     * Conditionally set up checkout hooks only when on checkout page or processing checkout
     */
    public function maybe_setup_checkout_hooks()
    {
        if (is_checkout() || wp_doing_ajax()) {
            // Classic checkout hooks
            add_filter('woocommerce_checkout_get_value', array($this, 'preload_checkout_fields'), 10, 2);
            //add_filter('woocommerce_default_address_fields', array($this, 'default_billing_company'));
            add_filter('woocommerce_checkout_fields', array($this, 'add_checkout_vat_field'), 99);

            // Order saving hooks
            add_action('woocommerce_checkout_update_order_meta', array($this, 'save_checkout_vat_field'));

            // Validation hooks
            add_action('woocommerce_checkout_update_order_review', array($this, 'ajax_validate_and_exempt_vat'), 20);
            add_action('woocommerce_after_checkout_validation', array($this, 'on_checkout_vat_field'), 10, 2);

            // VAT exempt notice
            add_action('woocommerce_review_order_before_shipping', array($this, 'show_vat_exempt_notice_checkout'), 5);

            // Enqueue checkout scripts
            //disabled for now, might go against woocommerce default behaviour
            //add_action('wp_enqueue_scripts', array($this, 'enqueue_checkout_scripts'));

            // Setup additional hooks for admin and email display
            //$this->setup_admin_hooks();
        }
    }

    /**
     * Enqueue checkout scripts only when needed
     */
    public function enqueue_checkout_scripts()
    {
        if (is_checkout()) {
            wp_enqueue_script(
                'vat-guard-checkout',
                plugin_dir_url(dirname(__FILE__)) . '/assets/js/vat-guard-checkout.js',
                array('jquery'),
                '1.0',
                true
            );
        }
    }

    /**
     * Setup additional hooks for admin and email display
     */
    public function setup_admin()
    {
        // Admin logic moved to VAT_Guard_WooCommerce_Admin
        if (is_admin()) {
            //admin screen functions
            require_once plugin_dir_path(__FILE__) . 'class-vat-guard-admin.php';

            // Show VAT number in the WooCommerce admin order edit screen (billing section)
            // replaced by function below
            //add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'show_vat_in_admin_order'));

            // Add VAT field to admin order billing address editing
            add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'add_vat_field_to_admin_order'));

            // Save VAT field when order is updated in admin
            add_action('woocommerce_process_shop_order_meta', array($this, 'save_admin_order_vat_field'));
        }
        // Show VAT number in WooCommerce order emails (customer & admin)
        add_action('woocommerce_email_customer_details', array($this, 'woocommerce_email_customer_details'));

        // Initialize PDF integration
        $this->init_pdf_integration();

    }

    /**
     * Check if VAT exemption is disabled
     */
    public function is_exemption_disabled()
    {
        return (bool) get_option('eu_vat_guard_disable_exemption', false);
    }

    /**
     * Get custom company label or default (WPML compatible)
     */
    public function get_company_label()
    {
        $custom_label = get_option('eu_vat_guard_company_label', '');
        if (!empty($custom_label)) {
            // Make custom string translatable with WPML
            return $this->translate_custom_string($custom_label, 'Company Label');
        }
        return __('Company Name', 'eu-vat-guard-for-woocommerce');
    }

    /**
     * Get custom VAT label or default (WPML compatible)
     */
    public function get_vat_label()
    {
        $custom_label = get_option('eu_vat_guard_vat_label', '');
        if (!empty($custom_label)) {
            // Make custom string translatable with WPML
            return $this->translate_custom_string($custom_label, 'VAT Label');
        }
        return __('VAT Number', 'eu-vat-guard-for-woocommerce');
    }

    /**
     * Get custom exemption message or default (WPML compatible)
     */
    public function get_exemption_message()
    {
        $custom_message = get_option('eu_vat_guard_exemption_message', '');
        if (!empty($custom_message)) {
            // Make custom string translatable with WPML
            return $this->translate_custom_string($custom_message, 'Exemption Message');
        }
        return __('VAT exempt for this order', 'eu-vat-guard-for-woocommerce');
    }

    /**
     * Translate custom string with WPML if available
     */
    private function translate_custom_string($string, $name)
    {
        // Check if WPML is active and has string translation
        if (function_exists('icl_t')) {
            // Register string for translation if not already registered
            if (function_exists('icl_register_string')) {
                icl_register_string('EU VAT Guard', $name, $string);
            }
            // Return translated string
            return icl_t('EU VAT Guard', $name, $string);
        }

        // Fallback: return original string if WPML not available
        return $string;
    }

    /**
     * Initialize PDF integration
     */
    private function init_pdf_integration()
    {
        // Load PDF integration class
        if (!class_exists(__NAMESPACE__ . '\VAT_Guard_PDF_Integration')) {
            require_once plugin_dir_path(__FILE__) . 'class-vat-guard-pdf-integration.php';
        }

        // Initialize PDF integration
        VAT_Guard_PDF_Integration::instance();
    }

    /**
     * Initialize block-based checkout support
     * @return void
     */
    public function init_block_checkout_support()
    {
        // Load and initialize the block integration
        if (!class_exists('VAT_Guard_Block_Integration')) {
            require_once plugin_dir_path(__FILE__) . 'class-vat-guard-block-integration.php';
        }

        // Initialize the block integration with access to main class methods
        $block_integration = new VAT_Guard_Block_Integration($this);
        $block_integration->init();
    }

    /**
     * Get VAT number from existing order, checking both block and classic checkout sources
     * @param \WC_Order $order
     * @return string VAT number or empty string
     */
    public function get_order_vat_number($order)
    {
        $vat = $order->get_meta('billing_eu_vat_number');
        if (empty($vat)) {
            $vat = get_post_meta($order->get_id(), 'billing_eu_vat_number', true);
        }

        return $vat;
    }




    /**
     * Validate EU VAT number structure and optionally VIES check
     * @param string $vat The VAT number (with country code)
     * @param bool $require_vies Whether to require VIES validation
     * @param array &$error_message If invalid, set to error message string
     * @return bool
     */
    public function is_valid_eu_vat_number($vat, &$error_message = null)
    {
        $vat = strtoupper(str_replace([' ', '-', '.'], '', $vat));
        $require_vies = get_option('eu_vat_guard_require_vies', 0);
        $eu_countries = [
            'AT',
            'BE',
            'BG',
            'CY',
            'CZ',
            'DE',
            'DK',
            'EE',
            'EL',
            'ES',
            'FI',
            'FR',
            'HR',
            'HU',
            'IE',
            'IT',
            'LT',
            'LU',
            'LV',
            'MT',
            'NL',
            'PL',
            'PT',
            'RO',
            'SE',
            'SI',
            'SK'
        ];
        $country = substr($vat, 0, 2);
        if (!in_array($country, $eu_countries) || strlen($vat) < 8 || strlen($vat) > 14) {
            $error_message = __('Please enter a valid EU VAT number.', 'eu-vat-guard-for-woocommerce');
            return false;
        }
        // VAT number regex patterns for all EU countries
        $patterns = [
            'AT' => '/^ATU\d{8}$/',                  // Austria
            'BE' => '/^BE0?\d{9,10}$/',              // Belgium - 9 or 10 digits, optional leading 0
            'BG' => '/^BG\d{9,10}$/',                // Bulgaria - 9 or 10 digits
            'CY' => '/^CY\d{8}[A-Z]$/',              // Cyprus - 8 digits + 1 letter
            'CZ' => '/^CZ\d{8,10}$/',                // Czech Republic - 8, 9, or 10 digits
            'DE' => '/^DE\d{9}$/',                   // Germany - 9 digits
            'DK' => '/^DK\d{8}$/',                   // Denmark - 8 digits
            'EE' => '/^EE\d{9}$/',                   // Estonia - 9 digits
            'EL' => '/^EL\d{9}$/',                   // Greece - 9 digits
            'ES' => '/^ES[A-Z0-9]\d{7}[A-Z0-9]$/',  // Spain - letter/digit + 7 digits + letter/digit
            'FI' => '/^FI\d{8}$/',                   // Finland - 8 digits
            'FR' => '/^FR[A-HJ-NP-Z0-9]{2}\d{9}$/', // France - 2 chars + 9 digits
            'HR' => '/^HR\d{11}$/',                  // Croatia - 11 digits
            'HU' => '/^HU\d{8}$/',                   // Hungary - 8 digits
            'IE' => '/^IE(\d{7}[A-W]|\d[A-Z]\d{5}[A-W])[A-I]?$/', // Ireland - multiple formats
            'IT' => '/^IT\d{11}$/',                  // Italy - 11 digits
            'LT' => '/^LT(\d{9}|\d{12})$/',          // Lithuania - 9 or 12 digits
            'LU' => '/^LU\d{8}$/',                   // Luxembourg - 8 digits
            'LV' => '/^LV\d{11}$/',                  // Latvia - 11 digits
            'MT' => '/^MT\d{8}$/',                   // Malta - 8 digits
            'NL' => '/^NL\d{9}B\d{2}$/',             // Netherlands - 9 digits + B + 2 digits
            'PL' => '/^PL\d{10}$/',                  // Poland - 10 digits
            'PT' => '/^PT\d{9}$/',                   // Portugal - 9 digits
            'RO' => '/^RO\d{2,10}$/',                // Romania - 2 to 10 digits
            'SE' => '/^SE\d{12}$/',                  // Sweden - 12 digits (not ending in 01)
            'SI' => '/^SI\d{8}$/',                   // Slovenia - 8 digits
            'SK' => '/^SK\d{10}$/',                  // Slovakia - 10 digits
        ];
        if (isset($patterns[$country]) && preg_match($patterns[$country], $vat) !== 1) {
            $error_message = __('Please enter a valid EU VAT number.', 'eu-vat-guard-for-woocommerce');
            return false;
        }
        // VIES check if required
        if ($require_vies) {
            $ignore_vies_error = get_option('eu_vat_guard_ignore_vies_error', 0);
            $number = substr($vat, 2);
            $vies_result = VAT_Guard_VIES::check_vat($country, $number);
            if ($vies_result === false) {
                $error_message = __('This VAT number is not valid according to the VIES service.', 'eu-vat-guard-for-woocommerce');
                return false;
            } elseif ($vies_result === null) {
                if ($ignore_vies_error) {
                    // Allow checkout if VIES is unavailable and option is enabled
                    return true;
                }
                $error_message = __('VAT number validation is currently unavailable. Please try again later or contact the website owner.', 'eu-vat-guard-for-woocommerce');
                return false;
            }
        }

        // Allow Pro plugin to enhance VAT validation
        $enhanced_result = apply_filters('eu_vat_guard_validate_vat_number', true, $vat, $country);

        return $enhanced_result;
    }


    /**
     * Sanitize VAT field input
     * Removes dots, spaces, and other non-alphanumeric characters
     * Converts to uppercase
     * @param string $value The VAT number to sanitize
     * @return string The sanitized VAT number
     */
    public function sanitize_vat_field($value)
    {
        if (empty($value)) {
            return '';
        }

        // Remove dots, spaces, dashes, and other non-alphanumeric characters
        // Keep only letters and numbers
        $sanitized = preg_replace('/[^A-Za-z0-9]/', '', $value);

        // Convert to uppercase
        return strtoupper($sanitized);
    }


    /**
     * Preload checkout fields with user meta data if available
     * @param mixed $value Current value of the field
     * @param string $input Name of the input field
     * @return mixed
     */
    public function preload_checkout_fields($value, $input)
    {
        if ($input == 'billing_eu_vat_number' && is_user_logged_in()) {
            $vat = get_user_meta(get_current_user_id(), 'vat_number', true);
            if (!empty($vat)) {
                $value = $vat;
            }
        } else if ($input == 'billing_company' && is_user_logged_in()) {
            $company = get_user_meta(get_current_user_id(), 'company_name', true);
            if (!empty($company)) {
                $value = $company;
            }
        } else if ($input == 'billing_email' && is_user_logged_in()) {
            $email = get_user_meta(get_current_user_id(), 'email', true);
            if (!empty($email)) {
                $value = $email;
            }
        }
        return $value;
    }

    /**
     * Preload billing company field with user meta data if available
     * @param array $fields Current billing fields
     * @return array Modified billing fields
     * TODO: might be not required
     */
    public function default_billing_company($fields)
    {
        if (is_user_logged_in()) {
            $company_name = get_user_meta(get_current_user_id(), 'company_name', true);
            if (!empty($company_name)) {
                $fields['company']['default'] = $company_name;
            }
        }
        return $fields;
    }

    /**
     * Add VAT number field to checkout
     * @param array $fields Current checkout fields
     * @return array Modified checkout fields
     */
    public function add_checkout_vat_field($fields)
    {

        $require_vat = get_option('eu_vat_guard_require_vat', 1);
        $require_company = get_option('eu_vat_guard_require_company', 1);

        $fields['billing']['billing_company'] = array(
            'type' => 'text',
            'label' => $this->get_company_label(),
            'placeholder' => $this->get_company_label(),
            'required' => (bool) $require_company,
            'class' => array('form-row-wide'),
            'priority' => 25,
            'default' => '',
        );

        $fields['billing']['billing_eu_vat_number'] = array(
            'type' => 'text',
            'label' => $this->get_vat_label(),
            'placeholder' => $this->get_vat_label(),
            'required' => (bool) $require_vat,
            'class' => array('form-row-wide', 'update_totals_on_change'),
            'priority' => 26,
            'default' => '',
        );
        return $fields;
    }


    /**
     * Save VAT number and exemption status to order meta during checkout
     * Not triggered on block based checkout
     * @param int $order_id The ID of the order being created
     */
    public function save_checkout_vat_field($order_id)
    {
        // Try to get VAT number from POST data
        $vat_number = '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce handles nonce verification for checkout process
        if (isset($_POST['billing_eu_vat_number'])) {
            $vat_number = sanitize_text_field(wp_unslash($_POST['billing_eu_vat_number']));
        }

        if (!empty($vat_number)) {


            // Use both post meta and order meta for compatibility
            update_post_meta($order_id, 'billing_eu_vat_number', $vat_number);

            // Also try to update using WC_Order object if available
            $order = wc_get_order($order_id);
            if ($order) {
                $order->update_meta_data('billing_eu_vat_number', $vat_number);
                $order->save_meta_data();
            }

            // Save VAT exemption status as order meta using WC()->customer->get_is_vat_exempt()
            $is_exempt = (WC()->customer && WC()->customer->get_is_vat_exempt());
            update_post_meta($order_id, 'billing_is_vat_exempt', $is_exempt ? 'yes' : 'no');

            if ($order) {
                $order->update_meta_data('billing_is_vat_exempt', $is_exempt ? 'yes' : 'no');

                // Notify Pro plugin when VAT exemption is applied to an actual order
                if ($is_exempt) {
                    $vat_country = substr(strtoupper(str_replace([' ', '-', '.'], '', $vat_number)), 0, 2);
                    $shop_base_country = wc_get_base_location()['country'];
                    $billing_country = $order->get_billing_country();
                    $shipping_country = $order->get_shipping_country();

                    do_action('eu_vat_guard_vat_exemption_applied', $order_id, array(
                        'vat_number' => $vat_number,
                        'vat_country' => $vat_country,
                        'billing_country' => $billing_country,
                        'shipping_country' => $shipping_country,
                        'shop_base_country' => $shop_base_country
                    ));
                }

                // Allow Pro plugin to add additional order data
                $order_data = array(
                    'vat_number' => $vat_number,
                    'is_vat_exempt' => $is_exempt,
                    'vat_country' => substr($vat_number, 0, 2)
                );
                $enhanced_order_data = apply_filters('eu_vat_guard_order_data', $order_data, $order);

                // Save any additional data from Pro plugin
                if (is_array($enhanced_order_data) && $enhanced_order_data !== $order_data) {
                    foreach ($enhanced_order_data as $key => $value) {
                        if (!in_array($key, array('vat_number', 'is_vat_exempt', 'vat_country'))) {
                            $order->update_meta_data('_vat_guard_' . $key, $value);
                        }
                    }
                }

                $order->save_meta_data();
            }

            // Update customer account VAT number if different
            if (is_user_logged_in()) {
                $user_id = get_current_user_id();
                $current_vat = get_user_meta($user_id, 'vat_number', true);
                if ($vat_number !== $current_vat) {
                    update_user_meta($user_id, 'vat_number', $vat_number);

                    // Notify Pro plugin when customer VAT is updated
                    do_action('eu_vat_guard_customer_vat_updated', $user_id, array(
                        'vat_number' => $vat_number,
                        'previous_vat' => $current_vat,
                        'order_id' => $order_id
                    ));
                }
            }
        }
    }




    /**
     * Validate VAT number during checkout and set VAT exemption status.
     * This runs after the default WooCommerce validation.
     * Uses the centralized validation function for consistency.
     */
    public function on_checkout_vat_field($data, $errors)
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce handles nonce verification for checkout process
        $vat = isset($_POST['billing_eu_vat_number']) ? sanitize_text_field(wp_unslash($_POST['billing_eu_vat_number'])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce handles nonce verification for checkout process
        $ship_to_different_address = isset($_POST['ship_to_different_address']) && sanitize_text_field(wp_unslash($_POST['ship_to_different_address'])) === '1';

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce handles nonce verification for checkout process
        $shipping_country = $ship_to_different_address && isset($_POST['shipping_country']) ?
            sanitize_text_field(wp_unslash($_POST['shipping_country'])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce handles nonce verification for checkout process
        $billing_country = isset($_POST['billing_country']) ? sanitize_text_field(wp_unslash($_POST['billing_country'])) : '';

        // Use the centralized validation function
        $error_messages = [];
        $this->validate_and_set_vat_exemption(
            $vat,
            $billing_country,
            $shipping_country,
            $error_messages
        );

        // Add any error messages to the WooCommerce errors object
        foreach ($error_messages as $error_message) {
            $errors->add('vat_number_error', $error_message);
        }
    }
    /**
     * Comprehensive VAT exemption validation and status setting
     * This is the centralized function that handles all VAT validation and exemption logic
     * 
     * @param string $vat VAT number to validate
     * @param string $billing_country Billing country code (2 letters)
     * @param string $shipping_country Shipping country code (2 letters)
     * @param array &$error_messages Array to collect error messages
     * @return bool True if VAT exempt, false otherwise
     */
    public function validate_and_set_vat_exemption($vat, $billing_country = '', $shipping_country = '', &$error_messages = [])
    {
        $require_vat = get_option('eu_vat_guard_require_vat', 1);

        // Initialize error messages array if not provided
        if (!is_array($error_messages)) {
            $error_messages = [];
        }

        // Check if VAT exemption is disabled
        if ($this->is_exemption_disabled()) {
            $this->set_customer_vat_exempt_status(false);
            // Still validate VAT number if provided, but don't apply exemption
            if (!empty($vat)) {
                $vat_error_message = '';
                if (!$this->is_valid_eu_vat_number($vat, $vat_error_message)) {
                    $error_messages[] = $vat_error_message;
                    return false;
                }
            }
            return false; // No exemption applied
        }

        // Step 1: Check if VAT is required but empty
        if ($require_vat && empty($vat)) {
            $error_messages[] = __('Please enter your VAT number.', 'eu-vat-guard-for-woocommerce');
            $this->set_customer_vat_exempt_status(false);
            return false;
        }

        // Step 2: If no VAT number provided (and not required), no exemption
        if (empty($vat)) {
            $this->set_customer_vat_exempt_status(false);
            return false;
        }

        // Step 3: Validate VAT number format and VIES (if enabled)
        $vat_error_message = '';
        if (!$this->is_valid_eu_vat_number($vat, $vat_error_message)) {
            $error_messages[] = $vat_error_message;
            $this->set_customer_vat_exempt_status(false);
            return false;
        }

        // Step 4: Extract VAT country and validate country matching
        $vat_country = substr(strtoupper(str_replace([' ', '-', '.'], '', $vat)), 0, 2);

        // Check billing country matches VAT country
        if (!empty($billing_country) && strtoupper($billing_country) !== $vat_country) {
            $error_messages[] = __('The billing country must match the country of the VAT number.', 'eu-vat-guard-for-woocommerce');
            $this->set_customer_vat_exempt_status(false);
            return false;
        }

        // Check shipping country matches VAT country (use shipping if different from billing)
        $country_to_check = !empty($shipping_country) ? strtoupper($shipping_country) : strtoupper($billing_country);
        if (!empty($country_to_check) && $country_to_check !== $vat_country) {
            $error_messages[] = __('The shipping country must match the country of the VAT number.', 'eu-vat-guard-for-woocommerce');
            $this->set_customer_vat_exempt_status(false);
            return false;
        }

        // Step 5: Check shipping method - no exemption for local pickup
        $chosen_methods = $this->get_current_shipping_methods();
        $local_pickup_methods = apply_filters('woocommerce_local_pickup_methods', ['local_pickup']);

        if (count(array_intersect($chosen_methods, $local_pickup_methods)) > 0) {
            $this->set_customer_vat_exempt_status(false);
            return false;
        }

        // Step 6: Check if this is a cross-border transaction (different from shop base country)
        $shop_base_country = wc_get_base_location()['country'];
        $is_cross_border = !empty($vat) && $vat_country && $vat_country !== $shop_base_country;

        $this->set_customer_vat_exempt_status($is_cross_border);

        return $is_cross_border;
    }

    /**
     * Set VAT exempt status on the customer (simplified version)
     * @param bool $is_exempt Whether customer should be VAT exempt
     */
    private function set_customer_vat_exempt_status($is_exempt)
    {
        if (WC()->customer) {
            WC()->customer->set_is_vat_exempt($is_exempt);
        }
    }

    /**
     * Clear VAT exempt status (used when VAT validation fails or is incomplete)
     * This is a lightweight function for cases where we don't have complete address info
     */
    public function clear_vat_exempt_status()
    {
        $this->set_customer_vat_exempt_status(false);
    }

    /**
     * Set VAT exempt status on the customer based on VAT number, shop base country and selected shipping method
     * This method does expect that basic checks on shipping/billing country have already been carried out
     * It will not show any errors but just apply the exemption rules:
     *      - if local pickup is selected, no exemption will occur
     *      - if the VAT number provided is from a different country than the store, VAT exemption occurs
     * @param string $vat
     * @deprecated Use validate_and_set_vat_exemption() instead
     */
    public function set_vat_exempt_status($vat)
    {
        if (empty($vat)) {
            WC()->customer->set_is_vat_exempt(false); //no VAT so no exemption
            return;
        }

        // Check if local pickup is selected - never exempt VAT for local pickup
        $chosen_methods = $this->get_current_shipping_methods();
        $local_pickup_methods = apply_filters('woocommerce_local_pickup_methods', array('local_pickup'));

        if (count(array_intersect($chosen_methods, $local_pickup_methods)) > 0) {
            WC()->customer->set_is_vat_exempt(false);
            return;
        }

        $vat_country = substr($vat, 0, 2);
        $shop_base_country = wc_get_base_location()['country'];
        if (!empty($vat) && $vat_country && $vat_country !== $shop_base_country) {
            WC()->customer->set_is_vat_exempt(true);
        } else {
            WC()->customer->set_is_vat_exempt(false);
        }
    }

    /* Validate VAT number and set VAT exemption after editing the field
     * Uses the centralized validation function for consistency
     */
    public function ajax_validate_and_exempt_vat($post_data)
    {
        parse_str($post_data, $data);

        $vat = isset($data['billing_eu_vat_number']) ? trim($data['billing_eu_vat_number']) : '';
        $ship_to_different_address = isset($data['ship_to_different_address']) && $data['ship_to_different_address'] === '1';

        // Get shipping country from the data
        // If shipping address is different, use that, otherwise use billing country
        $shipping_country = $ship_to_different_address && isset($data['shipping_country']) ?
            trim($data['shipping_country']) : '';
        $billing_country = isset($data['billing_country']) ? trim($data['billing_country']) : '';

        // Use the centralized validation function
        $error_messages = [];
        $this->validate_and_set_vat_exemption(
            $vat,
            $billing_country,
            $shipping_country,
            $error_messages
        );

        // Display any error messages
        foreach ($error_messages as $error_message) {
            wc_add_notice($error_message, 'error');
        }

        // Store VAT number in customer session for later retrieval
        if (!empty($vat) && WC()->customer) {
            WC()->session->set('billing_eu_vat_number', $vat);
        }
    }


    /**
     * Show VAT number in order emails
     */
    public function show_vat_in_emails($order, $sent_to_admin, $plain_text, $email)
    {
        $vat = $this->get_order_vat_number($order);

        if ($vat) {
            echo '<p><strong>' . esc_html__('VAT Number', 'eu-vat-guard-for-woocommerce') . ':</strong> ' . esc_html($vat) . '</p>';
        }
    }

    /**
     * Show VAT number in admin order screen
     * @deprecated use add_vat_field_to_admin_order instead
     */
    public function show_vat_in_admin_order($order)
    {
        // Check if this order has VAT data from block checkout (additional fields)
        $block_vat = $order->get_meta('_wc_other/eu-vat-guard/vat_number');

        // Only show custom VAT display if WooCommerce hasn't already shown it via additional fields
        // We check if block VAT exists - if it does, WooCommerce will handle the display automatically
        if (empty($block_vat)) {
            // Get VAT from custom meta (classic checkout or block fallback)
            $custom_vat = $order->get_meta('billing_eu_vat_number');
            if (empty($custom_vat)) {
                $custom_vat = get_post_meta($order->get_id(), 'billing_eu_vat_number', true);
            }

            if (!empty($custom_vat)) {
                echo '<p><strong>' . esc_html__('VAT Number', 'eu-vat-guard-for-woocommerce') . ':</strong> ' . esc_html($custom_vat) . '</p>';
            }
        }

        // Always show VAT exempt status regardless of checkout type
        $is_exempt = $order->get_meta('billing_is_vat_exempt');
        if (empty($is_exempt)) {
            $is_exempt = get_post_meta($order->get_id(), 'billing_is_vat_exempt', true);
        }
        if ($is_exempt === 'yes') {
            echo '<p style="color: #008000;"><strong>' . esc_html($this->get_exemption_message()) . '</strong></p>';
        }
    }


    /**
     * Add VAT field to admin order billing address section
     */
    public function add_vat_field_to_admin_order($order)
    {
        // Get current VAT number
        $vat_number = '';
        if ($order && $order->get_id()) {
            $vat_number = $this->get_order_vat_number($order);
        }

        // Check if we're in edit mode by looking for the edit action in the request
        $is_edit_mode = isset($_GET['action']) && $_GET['action'] === 'edit';

        // If not in edit mode and VAT number exists, show it as read-only text
        if (!$is_edit_mode && !empty($vat_number)) {
            echo '<p class="form-field form-field-wide">';
            echo '<strong>' . esc_html($this->get_vat_label()) . ':</strong> ';
            echo '<span>' . esc_html($vat_number) . '</span>';
            echo '</p>';
        } else {
            // Add the VAT field using WooCommerce's woocommerce_wp_text_input function
            woocommerce_wp_text_input(array(
                'id' => '_billing_eu_vat_number',
                'label' => $this->get_vat_label(),
                'placeholder' => __('Enter VAT number', 'eu-vat-guard-for-woocommerce'),
                'value' => $vat_number,
                'wrapper_class' => 'form-field-wide'
            ));
        }

        // Show current exemption status if applicable
        $is_exempt = $order && $order->get_id() ? $order->get_meta('billing_is_vat_exempt') : '';
        if ($is_exempt === 'yes') {
            echo '<p style="color: #00a32a; font-weight: bold; margin: 5px 0;">✓ ' . esc_html($this->get_exemption_message()) . '</p>';
        }
    }

    /**
     * Save VAT field when order is updated in admin
     */
    public function save_admin_order_vat_field($post_id)
    {
        if (isset($_POST['_billing_eu_vat_number'])) {
            $vat_number = sanitize_text_field(wp_unslash($_POST['_billing_eu_vat_number']));

            // Get the order object
            $order = wc_get_order($post_id);
            if (!$order) {
                return;
            }

            // Validate VAT number if provided
            if (!empty($vat_number)) {
                $error = '';
                $validation_result = $this->is_valid_eu_vat_number($vat_number, $error);

                if ($validation_result) {
                    // Save VAT number
                    $order->update_meta_data('billing_eu_vat_number', $vat_number);

                    // Set VAT exempt status if validation passed
                    $order->update_meta_data('billing_is_vat_exempt', 'yes');

                    // Save the order
                    $order->save();

                    // Add order note
                    $order->add_order_note(
                        /* translators: %s: VAT number */
                        sprintf(__('VAT number %s added and validated via admin.', 'eu-vat-guard-for-woocommerce'), $vat_number)
                    );
                } else {
                    // Add admin notice for invalid VAT
                    add_action('admin_notices', function () use ($error) {
                        echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
                    });
                }
            } else {
                // Remove VAT number and exempt status if field is empty
                $order->delete_meta_data('billing_eu_vat_number');
                $order->delete_meta_data('billing_is_vat_exempt');
                $order->save();

                $order->add_order_note(__('VAT number removed via admin.', 'eu-vat-guard-for-woocommerce'));
            }
        }
    }



    /**
     * Show VAT exempt notice in checkout totals for classic checkout
     */
    public function show_vat_exempt_notice_checkout()
    {
        if (WC()->customer && WC()->customer->get_is_vat_exempt()) {
            echo '<tr class="vat-exempt-notice">';
            echo '<th colspan="2" style="color: #00a32a; font-weight: bold; text-align: center; padding: 10px;">';
            echo '✓ ' . esc_html($this->get_exemption_message());
            echo '</th>';
            echo '</tr>';
        }
    }

    /**
     * Get current shipping methods, checking POST data first for AJAX updates
     * This ensures we get the newly selected shipping method during checkout updates
     * Extracts method types from full IDs (e.g., 'flat_rate:2' -> 'flat_rate')
     * 
     * @return array Array of chosen shipping method IDs and method types
     */
    public function get_current_shipping_methods()
    {
        $chosen_methods = array();

        // Try direct POST data first (for AJAX contexts)
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce handles nonce verification for shipping method updates
        if (isset($_POST['shipping_method']) && is_array($_POST['shipping_method'])) {
            $chosen_methods = array_map('sanitize_text_field', wp_unslash($_POST['shipping_method']));
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce handles nonce verification for shipping method updates
        } elseif (isset($_POST['shipping_method'])) {
            $chosen_methods = array(sanitize_text_field(wp_unslash($_POST['shipping_method'])));
        }

        // If no POST data, fall back to session data
        if (empty($chosen_methods) && function_exists('wc_get_chosen_shipping_method_ids')) {
            $chosen_methods = wc_get_chosen_shipping_method_ids();
        }

        // Extract method types from full method IDs (e.g., 'flat_rate:2' -> 'flat_rate')
        // Also keep the full IDs for backward compatibility
        $method_types = array();
        foreach ($chosen_methods as $method_id) {
            $method_types[] = $method_id; // Keep full ID
            if (strpos($method_id, ':') !== false) {
                $method_types[] = substr($method_id, 0, strpos($method_id, ':')); // Add method type
            }
        }

        return array_unique($method_types);
    }
}
