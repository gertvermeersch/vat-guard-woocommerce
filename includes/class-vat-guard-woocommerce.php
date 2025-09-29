<?php
// VAT Guard for WooCommerce Main Class
if (!defined('ABSPATH')) {
    exit;
}

class VAT_Guard_WooCommerce
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
        add_action('plugins_loaded', array($this, 'maybe_init_block_support'), 50);

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
            error_log('VAT Guard: WooCommerce class not found, skipping block support init');
            return;
        }
        // Load block integration if enabled - needs to happen early
        if (get_option('vat_guard_woocommerce_enable_block_checkout', 0)) {
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
        if (!class_exists('VAT_Guard_WooCommerce_VIES')) {
            require_once plugin_dir_path(__FILE__) . 'class-vat-guard-woocommerce-vies.php';
        }

        // Load admin functionality only in admin
        if (is_admin()) {
            require_once plugin_dir_path(__FILE__) . 'class-vat-guard-woocommerce-admin.php';
        }

        // Block integration is loaded earlier in maybe_init_block_support()
    }

    /**
     * Set up hooks based on current request context
     */
    private function setup_hooks()
    {
        // Always needed hooks (lightweight)
        //add_filter('woocommerce_order_formatted_billing_address', array($this, 'add_vat_to_formatted_address'), 10, 2);
        //add_filter('woocommerce_my_account_my_address_formatted_address', array($this, 'add_vat_to_my_account_address'), 10, 3);

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
        // Account and registration hooks
        add_action('woocommerce_register_form', array($this, 'add_registration_fields'));
        add_action('woocommerce_edit_account_form_start', array($this, 'add_account_fields'));
        add_filter('woocommerce_registration_errors', array($this, 'validate_registration_fields'), 10, 3);
        add_action('woocommerce_created_customer', array($this, 'save_fields_registration'));
        add_action('woocommerce_save_account_details', array($this, 'save_fields_registration'));

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
            require_once plugin_dir_path(__FILE__) . 'class-vat-guard-woocommerce-admin.php';

            // Show VAT number in the WooCommerce admin order edit screen (billing section)
            add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'show_vat_in_admin_order'));
        }
        // Show VAT number in WooCommerce order emails (customer & admin)
        add_action('woocommerce_email_customer_details', array($this, 'woocommerce_email_customer_details'));


    }

    /**
     * Add VAT number to address formats
     */
    public function add_vat_to_address_format($formats)
    {
        foreach ($formats as $country => &$format) {
            if (strpos($format, '{vat_number}') === false) {
                // Add VAT number after company if exists
                $format = str_replace('{company}', "{company}\n{vat_number}", $format);
            }
        }
        return $formats;
    }

    /**
     * Add VAT number replacement
     */
    public function add_vat_number_replacement($replacements, $args)
    {
        $replacements['{vat_number}'] = !empty($args['vat_number']) ?
            __('VAT Number:', 'vat-guard-woocommerce') . ' ' . $args['vat_number'] : '';
        return $replacements;
    }

    /**
     * Add VAT number to order's formatted address
     */
    public function add_vat_to_formatted_address($address, $order)
    {
        $vat = $this->get_order_vat_number($order);

        if (!empty($vat)) {
            $address['vat_number'] = $vat;

            // Add VAT exempt status if applicable
            $is_exempt = $order->get_meta('billing_is_vat_exempt');
            if (empty($is_exempt)) {
                $is_exempt = get_post_meta($order->get_id(), 'billing_is_vat_exempt', true);
            }
            if ($is_exempt === 'yes') {
                $address['vat_status'] = __('VAT exempt', 'vat-guard-woocommerce');
            }
        }
        return $address;
    }

    /**
     * Add VAT number to My Account formatted address
     */
    public function add_vat_to_my_account_address($address, $customer_id, $address_type)
    {
        if ($address_type === 'billing') {
            $vat = get_user_meta($customer_id, 'vat_number', true);
            if (!empty($vat)) {
                $address['vat_number'] = $vat;
            }
        }
        return $address;
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
     * @param WC_Order $order
     * @return string VAT number or empty string
     */
    public function get_order_vat_number($order)
    {
        // Try to get VAT from block checkout additional fields first
        $vat = '';
        if (function_exists('woocommerce_get_order_additional_field_value')) {
            $vat = woocommerce_get_order_additional_field_value($order, 'vat-guard-woocommerce/vat_number');
        }

        // Fallback to custom meta field (classic checkout or block fallback)
        if (empty($vat)) {
            $vat = $order->get_meta('billing_eu_vat_number');
            if (empty($vat)) {
                $vat = get_post_meta($order->get_id(), 'billing_eu_vat_number', true);
            }
        }

        return $vat;
    }


    /* adds registration field to create account 
     * 
     */
    public function add_registration_fields()
    {
        $require_company = get_option('vat_guard_woocommerce_require_company', 1);
        $require_vat = get_option('vat_guard_woocommerce_require_vat', 1);
        ?>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="company_name"
                id="company_name"
                placeholder="<?php _e('Company Name', 'vat-guard-woocommerce'); ?><?php echo $require_company ? ' *' : ''; ?>"
                <?php if ($require_company)
                    echo 'required'; ?> value="<?php if (!empty($_POST['company_name']))
                           esc_attr_e($_POST['company_name']); ?>" />
        </p>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="vat_number" id="vat_number"
                placeholder="<?php _e('VAT Number', 'vat-guard-woocommerce'); ?><?php echo $require_vat ? ' *' : ''; ?>" value="<?php if (!empty($_POST['vat_number']))
                              esc_attr_e($_POST['vat_number']); ?>" <?php if ($require_vat) {
                                   echo 'required';
                               } ?> />
        </p>
        <?php
    }

    public function add_account_fields()
    {
        $company_name = get_user_meta(get_current_user_id(), 'company_name', true);
        $vat_number = get_user_meta(get_current_user_id(), 'vat_number', true);

        $require_company = get_option('vat_guard_woocommerce_require_company', 1);
        $require_vat = get_option('vat_guard_woocommerce_require_vat', 1);
        ?>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="company_name"><?php _e('Company Name', 'vat-guard-woocommerce');
            if ($require_company) { ?><span class="required">*</span> <?php } ?></label>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="company_name"
                id="company_name" value="<?php esc_attr_e($company_name); ?>" />
        </p>
        <?php //if (!get_option('vat_guard_woocommerce_enable_block_checkout', 0)) { 
                // TODO: check if we still need this condition check
                // ?>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="vat_number"><?php _e('VAT Number', 'vat-guard-woocommerce');
            if ($require_vat) { ?><span class="required">*</span> <?php } ?></label>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="vat_number" id="vat_number"
                value="<?php esc_attr_e($vat_number); ?>" />
        </p>
        <?php
        //  }
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
        $require_vies = get_option('vat_guard_woocommerce_require_vies', 0);
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
            $error_message = __('Please enter a valid EU VAT number.', 'vat-guard-woocommerce');
            return false;
        }
        // VAT number regex patterns for all EU countries
        $patterns = [
            'AT' => '/^ATU\d{8}$/',                  // Austria
            'BE' => '/^BE0?\d{9}$/',                 // Belgium
            'BG' => '/^BG\d{9,10}$/',                // Bulgaria
            'CY' => '/^CY\d{8}[A-Z]$/',              // Cyprus
            'CZ' => '/^CZ\d{8,10}$/',                // Czech Republic
            'DE' => '/^DE\d{9}$/',                   // Germany
            'DK' => '/^DK\d{8}$/',                   // Denmark
            'EE' => '/^EE\d{9}$/',                   // Estonia
            'EL' => '/^EL\d{9}$/',                   // Greece
            'ES' => '/^ES[A-Z0-9]\d{7}[A-Z0-9]$/',   // Spain
            'FI' => '/^FI\d{8}$/',                   // Finland
            'FR' => '/^FR[A-HJ-NP-Z0-9]{2}\d{9}$/',  // France
            'HR' => '/^HR\d{11}$/',                  // Croatia
            'HU' => '/^HU\d{8}$/',                   // Hungary
            'IE' => '/^IE\d{7}[A-W][A-I0-9]?$/',     // Ireland
            'IT' => '/^IT\d{11}$/',                  // Italy
            'LT' => '/^LT(\d{9}|\d{12})$/',          // Lithuania
            'LU' => '/^LU\d{8}$/',                   // Luxembourg
            'LV' => '/^LV\d{11}$/',                  // Latvia
            'MT' => '/^MT\d{8}$/',                   // Malta
            'NL' => '/^NL\d{9}B\d{2}$/',             // Netherlands
            'PL' => '/^PL\d{10}$/',                  // Poland
            'PT' => '/^PT\d{9}$/',                   // Portugal
            'RO' => '/^RO\d{2,10}$/',                // Romania
            'SE' => '/^SE\d{10}01$/',                // Sweden
            'SI' => '/^SI\d{8}$/',                   // Slovenia
            'SK' => '/^SK\d{10}$/',                  // Slovakia
        ];
        if (isset($patterns[$country]) && preg_match($patterns[$country], $vat) !== 1) {
            $error_message = __('Please enter a valid EU VAT number.', 'vat-guard-woocommerce');
            return false;
        }
        // VIES check if required
        if ($require_vies) {
            $ignore_vies_error = get_option('vat_guard_woocommerce_ignore_vies_error', 0);
            $number = substr($vat, 2);
            $vies_result = VAT_Guard_WooCommerce_VIES::check_vat($country, $number);
            if ($vies_result === false) {
                $error_message = __('This VAT number is not valid according to the VIES service.', 'vat-guard-woocommerce');
                return false;
            } elseif ($vies_result === null) {
                if ($ignore_vies_error) {
                    // Allow checkout if VIES is unavailable and option is enabled
                    return true;
                }
                $error_message = __('VAT number validation is currently unavailable. Please try again later or contact the website owner.', 'vat-guard-woocommerce');
                return false;
            }
        }
        return true;
    }

    /* Validate registration fields for company name and VAT number
     * @param WP_Error $errors
     * @param string $username
     * @param string $email
     * @return WP_Error
     */
    public function validate_registration_fields($errors, $username, $email)
    {
        $require_company = get_option('vat_guard_woocommerce_require_company', 1);
        $require_vat = get_option('vat_guard_woocommerce_require_vat', 1);

        if ($require_company && empty($_POST['company_name'])) {
            $errors->add('company_name_error', __('Please enter your company name.', 'vat-guard-woocommerce'));
        }
        if ($require_vat && empty($_POST['vat_number'])) {
            $errors->add('vat_number_error', __('Please enter your VAT number.', 'vat-guard-woocommerce'));
        } elseif (!empty($_POST['vat_number'])) {
            $error_message = '';
            if (!$this->is_valid_eu_vat_number($_POST['vat_number'], $error_message)) {
                $errors->add('vat_number_error', $error_message);
            }
        }
        return $errors;
    }

    /* Save registration fields for company name and VAT number
     * @param int $customer_id
     */
    public function save_fields_registration($customer_id)
    {
        $require_company = get_option('vat_guard_woocommerce_require_company', 1);
        $require_vat = get_option('vat_guard_woocommerce_require_vat', 1);
        if ($require_vat && isset($_POST['vat_number']) && empty($_POST['vat_number'])) {
            wc_add_notice(__('Please enter your VAT number.', 'vat-guard-woocommerce'), 'error');
            return;
        }
        if (!empty($_POST['vat_number'])) {
            $error_message = '';
            if (!$this->is_valid_eu_vat_number($_POST['vat_number'], $error_message)) {
                wc_add_notice($error_message, 'error');
                return;
            }
        }
        if ($require_company && isset($_POST['company_name']) && empty($_POST['company_name'])) {
            wc_add_notice(__('Please enter your company name.', 'vat-guard-woocommerce'), 'error');
            return;
        }
        if (isset($_POST['company_name'])) {
            update_user_meta($customer_id, 'company_name', sanitize_text_field($_POST['company_name']));
        }
        if (isset($_POST['vat_number'])) {
            update_user_meta($customer_id, 'vat_number', $this->sanitize_vat_field($_POST['vat_number']));
        }
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

        $require_vat = get_option('vat_guard_woocommerce_require_vat', 1);
        $require_company = get_option('vat_guard_woocommerce_require_company', 1);

        // Ensure company field is present and required/optional
        $fields['billing']['billing_company']['required'] = (bool) $require_company;
        $fields['billing']['billing_company']['priority'] = 25; // After name fields

        $fields['billing']['billing_eu_vat_number'] = array(
            'type' => 'text',
            'label' => __('VAT Number', 'vat-guard-woocommerce'),
            'placeholder' => __('VAT Number', 'vat-guard-woocommerce'),
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
        if (isset($_POST['billing_eu_vat_number'])) {
            $vat_number = sanitize_text_field($_POST['billing_eu_vat_number']);
        }

        // If no VAT number in POST, try to get from customer session/data - We shouldn't do this as 
        // the user is required to enter it in the billing section ALWAYS
        /*
        if (empty($vat_number) && WC()->customer) {
            // Check if there's a VAT number in the customer's billing data
            $billing_data = WC()->customer->get_billing();
            if (isset($billing_data['eu_vat_number'])) {
                $vat_number = sanitize_text_field($billing_data['eu_vat_number']);
            }
        }

        // If no VAT number in customer billing data, try session
        if (empty($vat_number) && WC()->session) {
            $vat_number = WC()->session->get('billing_eu_vat_number');
        }

        // If still no VAT number, try user meta for logged in users
        if (empty($vat_number) && is_user_logged_in()) {
            $vat_number = get_user_meta(get_current_user_id(), 'vat_number', true);
        }*/

        if (!empty($vat_number)) {
            // Log for debugging
            //error_log("VAT Guard: Saving VAT number '{$vat_number}' for order {$order_id} via save_checkout_vat_field");

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
                $order->save_meta_data();
            }

            // Update customer account VAT number if different
            if (is_user_logged_in()) {
                $user_id = get_current_user_id();
                $current_vat = get_user_meta($user_id, 'vat_number', true);
                if ($vat_number !== $current_vat) {
                    update_user_meta($user_id, 'vat_number', $vat_number);
                }
            }
        } else {
            // Log when no VAT number is found
            error_log("VAT Guard: No VAT number found for order {$order_id} in save_checkout_vat_field");
        }
    }




    /**
     * Validate VAT number during checkout and set VAT exemption status.
     * This runs after the default WooCommerce validation.
     * It checks the VAT number, validates it, and sets the exemption status.
     * TODO: Is this really needed? It seems to duplicate the AJAX validation logic which can't be skipped.
     */
    public function on_checkout_vat_field($data, $errors)
    {
        $require_vat = get_option('vat_guard_woocommerce_require_vat', 1);
        $vat = isset($_POST['billing_eu_vat_number']) ? trim($_POST['billing_eu_vat_number']) : '';
        // $shipping_country = isset($_POST['shipping_country']) ? strtoupper(trim($_POST['shipping_country'])) : '';
        if ($require_vat && empty($vat)) {
            $errors->add('vat_number_error', __('Please enter your VAT number.', 'vat-guard-woocommerce'));
        } elseif (!empty($vat)) {
            $error_message = '';
            if (!$this->is_valid_eu_vat_number($vat, $error_message)) {
                $errors->add('vat_number_error', $error_message);
                $this->set_vat_exempt_status('');
                return;
            }
            // Check shipping country matches VAT country
            $vat_country = substr($vat, 0, 2);
            $ship_to_different_address = isset($_POST['ship_to_different_address']) && $_POST['ship_to_different_address'] === '1';
            $country_to_check = $ship_to_different_address && !empty($shipping_country) ?
                $shipping_country : (isset($_POST['billing_country']) ? strtoupper(trim($_POST['billing_country'])) : '');

            if (!empty($country_to_check) && $country_to_check !== $vat_country) {
                $errors->add('vat_number_error', __('The shipping country must match the country of the VAT number.', 'vat-guard-woocommerce'));
                $this->set_vat_exempt_status('');
                return;
            }
        }
        // Exemption
        $this->set_vat_exempt_status($vat);
    }
    /**
     * Set VAT exempt status on the customer based on VAT number, shop base country and selected shipping method
     * This method does expect that basic checks on shipping/billing country have already been carried out
     * It will not show any errors but just apply the exemption rules:
     *      - if local pickup is selected, no exemption will occur
     *      - if the VAT number provided is from a different country than the store, VAT exemption occurs
     * @param string $vat
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
     * It checks the VAT number, validates it, and sets the exemption status.
     */
    public function ajax_validate_and_exempt_vat($post_data)
    {
        parse_str($post_data, $data);

        // Store POST data temporarily for shipping method access
        $this->current_post_data = $data;
        $require_vat = get_option('vat_guard_woocommerce_require_vat', 1);
        $vat = isset($data['billing_eu_vat_number']) ? trim($data['billing_eu_vat_number']) : '';
        $ship_to_different_address = isset($data['ship_to_different_address']) && $data['ship_to_different_address'] === '1';
        // Get shipping country from the data
        // If shipping address is different, use that, otherwise use billing country
        // This is needed for the classic checkout where shipping country is not always set
        $shipping_country = $ship_to_different_address && isset($data['shipping_country']) ?
            strtoupper(trim($data['shipping_country'])) : (isset($data['billing_country']) ? strtoupper(trim($data['billing_country'])) : '');

        // Validation
        if ($require_vat && empty($vat)) {
            wc_add_notice(__('Please enter your VAT number.', 'vat-guard-woocommerce'), 'error');
            $this->set_vat_exempt_status('');
            return;
        } elseif (!empty($vat)) {
            $error_message = '';
            if (!$this->is_valid_eu_vat_number($vat, $error_message)) {
                wc_add_notice($error_message, 'error');
                $this->set_vat_exempt_status('');
                return;
            }
            // Check shipping country matches VAT country
            $vat_country = substr($vat, 0, 2);
            if (!empty($shipping_country) && $shipping_country !== $vat_country) {
                wc_add_notice(__('The shipping country must match the country of the VAT number.', 'vat-guard-woocommerce'), 'error');
                $this->set_vat_exempt_status('');
                return;
            }
            // Check billing country matches VAT country
            if (isset($data['billing_country'])) {
                $billing_country = strtoupper(trim($data['billing_country']));
                if ($billing_country !== $vat_country) {
                    wc_add_notice(__('The billing country must match the country of the VAT number.', 'vat-guard-woocommerce'), 'error');
                    $this->set_vat_exempt_status('');
                    return;
                }
            }
        }

        // Exemption
        $this->set_vat_exempt_status($vat);

        // Store VAT number in customer session for later retrieval
        if (!empty($vat) && WC()->customer) {
            // Store in customer session using WC session
            WC()->session->set('billing_eu_vat_number', $vat);
            error_log("VAT Guard: Stored VAT number '{$vat}' in customer session");
        }

        // Clear stored POST data
        unset($this->current_post_data);
    }


    /**
     * Show VAT number in admin order screen
     */
    public function show_vat_in_admin_order($order)
    {
        // Check if this order has VAT data from block checkout (additional fields)
        $block_vat = '';
        if (function_exists('woocommerce_get_order_additional_field_value')) {
            $block_vat = woocommerce_get_order_additional_field_value($order, 'vat-guard-woocommerce/vat_number');
        }

        // Only show custom VAT display if WooCommerce hasn't already shown it via additional fields
        // We check if block VAT exists - if it does, WooCommerce will handle the display automatically
        if (empty($block_vat)) {
            // Get VAT from custom meta (classic checkout or block fallback)
            $custom_vat = $order->get_meta('billing_eu_vat_number');
            if (empty($custom_vat)) {
                $custom_vat = get_post_meta($order->get_id(), 'billing_eu_vat_number', true);
            }

            if (!empty($custom_vat)) {
                echo '<p><strong>' . esc_html__('VAT Number', 'vat-guard-woocommerce') . ':</strong> ' . esc_html($custom_vat) . '</p>';
            }
        }

        // Always show VAT exempt status regardless of checkout type
        $is_exempt = $order->get_meta('billing_is_vat_exempt');
        if (empty($is_exempt)) {
            $is_exempt = get_post_meta($order->get_id(), 'billing_is_vat_exempt', true);
        }
        if ($is_exempt === 'yes') {
            echo '<p style="color: #008000;"><strong>' . esc_html__('VAT exempt for this order', 'vat-guard-woocommerce') . '</strong></p>';
        }
    }

    /**
     * Show VAT number in order emails
     */
    public function show_vat_in_emails($order, $sent_to_admin, $plain_text, $email)
    {
        $vat = $this->get_order_vat_number($order);

        if ($vat) {
            echo '<p><strong>' . esc_html__('VAT Number', 'vat-guard-woocommerce') . ':</strong> ' . esc_html($vat) . '</p>';
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
            echo '✓ ' . esc_html__('VAT exempt for this order', 'vat-guard-woocommerce');
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

        // First, try to get from stored POST data (during AJAX processing)
        if (isset($this->current_post_data['shipping_method'])) {
            if (is_array($this->current_post_data['shipping_method'])) {
                $chosen_methods = array_map('sanitize_text_field', $this->current_post_data['shipping_method']);
            } else {
                $chosen_methods = array(sanitize_text_field($this->current_post_data['shipping_method']));
            }
        }
        // Then try direct POST data (for other contexts)
        elseif (isset($_POST['shipping_method']) && is_array($_POST['shipping_method'])) {
            $chosen_methods = array_map('sanitize_text_field', $_POST['shipping_method']);
        } elseif (isset($_POST['shipping_method'])) {
            $chosen_methods = array(sanitize_text_field($_POST['shipping_method']));
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
