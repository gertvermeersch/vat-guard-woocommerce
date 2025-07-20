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
        // Add hooks here (registration fields, account fields, etc.)
        add_action('woocommerce_register_form', array($this, 'add_registration_fields'));
        add_action('woocommerce_edit_account_form_start', array($this, 'add_account_fields'));
        add_filter('woocommerce_registration_errors', array($this, 'validate_registration_fields'), 10, 3);
        add_action('woocommerce_created_customer', array($this, 'save_fields'));
        add_action('woocommerce_save_account_details', array($this, 'save_fields'));
        add_filter('woocommerce_checkout_get_value', array($this, 'preload_checkout_fields'), 10, 2);
        add_filter('woocommerce_default_address_fields', array($this, 'default_billing_company'));
        add_filter('woocommerce_checkout_fields', array($this, 'add_checkout_vat_field'), 99);

        // Runs when the order is created, saves the VAT number to the order meta
        add_action('woocommerce_checkout_update_order_meta', array($this, 'save_checkout_vat_field'));

        // runs every time the checkout is updated (e.g. when shipping changes)
        // This is where we check the VAT number and potentially exempt VAT
        // Called on wc-ajax=update_order_review
        add_action('woocommerce_checkout_update_order_review', array($this, 'ajax_validate_and_exempt_vat'), 20);

        // Checkout validation and saving, run after the default WooCommerce validation, this is needed when users ignore the VAT error messages (they can still submit the form)
        // called on wc-ajax=checkout
        add_action('woocommerce_after_checkout_validation', array($this, 'on_checkout_vat_field'), 10, 2);

        add_action('wp_enqueue_scripts', function () {
            if (is_checkout()) {
                wp_enqueue_script(
                    'vat-guard-checkout',
                    plugin_dir_url(dirname(__FILE__)) . '/assets/js/vat-guard-checkout.js',
                    array('jquery'),
                    '1.0',
                    true
                );
            }
        });


        //for block based checkout
        add_action('woocommerce_init', function () {
            if (!function_exists('woocommerce_register_additional_checkout_field')) {
                return;
            }
            woocommerce_register_additional_checkout_field(
                array(
                    'id'       => 'vat-guard-woocommerce/vat_number',
                    'label'    => __('VAT Number', 'vat-guard-woocommerce'),
                    'location' => 'contact',
                    'type'     => 'text',
                    'required' => (bool) get_option('vat_guard_woocommerce_require_vat', 1),
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => array($this, 'ajax_validate_and_exempt_vat_block')
                )
            );

        });


        // Show VAT exempt notice in the order review totals (before shipping row)
        add_action('woocommerce_review_order_before_shipping', array($this, 'show_vat_exempt_notice_checkout'), 5);

        // Admin logic moved to VAT_Guard_WooCommerce_Admin
        if (is_admin()) {
            require_once plugin_dir_path(__FILE__) . 'class-vat-guard-woocommerce-admin.php';
        }
        // VIES logic
        require_once plugin_dir_path(__FILE__) . 'class-vat-guard-woocommerce-vies.php';

        // Show VAT number in the WooCommerce admin order edit screen (billing section)
        add_action('woocommerce_admin_order_data_after_billing_address', function ($order) {
            $vat = get_post_meta($order->get_id(), 'billing_eu_vat_number', true);
            if ($vat) {
                echo '<p><strong>' . esc_html__('VAT Number', 'vat-guard-woocommerce') . ':</strong> ' . esc_html($vat) . '</p>';
                $is_exempt = get_post_meta($order->get_id(), '_vat_guard_woocommerce_is_vat_exempt', true);
                if ($is_exempt === 'yes') {
                    echo '<p style="color: #008000;"><strong>' . esc_html__('VAT exempt for this order', 'vat-guard-woocommerce') . '</strong></p>';
                }
            }
        });

        // Show VAT number in WooCommerce order emails (customer & admin)
        add_action('woocommerce_email_customer_details', function ($order, $sent_to_admin, $plain_text, $email) {
            $vat = get_post_meta($order->get_id(), 'billing_eu_vat_number', true);
            if ($vat) {
                echo '<p><strong>' . esc_html__('VAT Number', 'vat-guard-woocommerce') . ':</strong> ' . esc_html($vat) . '</p>';
            }
        }, 20, 4);
    }


    /**
     * Set VAT exempt status on the customer based on VAT number and shop base country
     * @param string $vat
     */
    public function set_vat_exempt_status($vat)
    {
        if (empty($vat)) {
            WC()->customer->set_is_vat_exempt(false); //no VAT so no exemption
        }
        $vat_country = substr($vat, 0, 2);
        $shop_base_country = wc_get_base_location()['country'];
        if (!empty($vat) && $vat_country && $vat_country !== $shop_base_country) {
            WC()->customer->set_is_vat_exempt(true);
        } else {
            WC()->customer->set_is_vat_exempt(false);
        }
    }
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
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="vat_number"><?php _e('VAT Number', 'vat-guard-woocommerce');
                                    if ($require_vat) { ?><span class="required">*</span> <?php } ?></label>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="vat_number" id="vat_number"
                value="<?php esc_attr_e($vat_number); ?>" />
        </p>
<?php
    }

    /**
     * Validate EU VAT number structure and optionally VIES check
     * @param string $vat The VAT number (with country code)
     * @param bool $require_vies Whether to require VIES validation
     * @param array &$error_message If invalid, set to error message string
     * @return bool
     */
    private function is_valid_eu_vat_number($vat, &$error_message = null)
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
        if (!in_array($country, $eu_countries)) {
            $error_message = __('Please enter a valid EU VAT number.', 'vat-guard-woocommerce');
            return false;
        }
        if (strlen($vat) < 8 || strlen($vat) > 14) {
            $error_message = __('Please enter a valid EU VAT number.', 'vat-guard-woocommerce');
            return false;
        }
        $patterns = [
            'BE' => '/^BE0?\d{9}$/',
            'DE' => '/^DE[0-9]{9}$/',
            'FR' => '/^FR[0-9A-Z]{2}\d{9}$/',
            'NL' => '/^NL[0-9]{9}B[0-9]{2}$/',
            'IT' => '/^IT[0-9]{11}$/',
            'ES' => '/^ES[A-Z0-9][0-9]{7}[A-Z0-9]$/',
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
    public function save_fields($customer_id)
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
            update_user_meta($customer_id, 'vat_number', sanitize_text_field($_POST['vat_number']));
        }
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
        if (isset($_POST['billing_eu_vat_number'])) {
            $vat_number = sanitize_text_field($_POST['billing_eu_vat_number']);
            update_post_meta($order_id, 'billing_eu_vat_number', $vat_number);
            // Save VAT exemption status as order meta using WC()->customer->get_is_vat_exempt()
            $is_exempt = (WC()->customer && WC()->customer->get_is_vat_exempt());
            update_post_meta($order_id, '_vat_guard_woocommerce_is_vat_exempt', $is_exempt ? 'yes' : 'no');
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
        if ($require_vat && empty($vat)) {
            $errors->add('vat_number_error', __('Please enter your VAT number.', 'vat-guard-woocommerce'));
        } elseif (!empty($vat)) {
            $error_message = '';
            if (!$this->is_valid_eu_vat_number($vat, $error_message)) {
                $errors->add('vat_number_error', $error_message);
                WC()->customer->set_is_vat_exempt(false);
                return;
            }
        }
        // Exemption
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
        $require_vat = get_option('vat_guard_woocommerce_require_vat', 1);
        $vat = isset($data['billing_eu_vat_number']) ? trim($data['billing_eu_vat_number']) : '';

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
        }

        // Exemption
        $this->set_vat_exempt_status($vat);
    }

    /* 
     * very similar to the above function, but for block based checkout
     * @param string $value The VAT number entered in the block checkout
     * @return WP_Error|true Returns true if valid, WP_Error if invalid
     * This function validates the VAT number, checks its validity, and sets the VAT exemption status
     */
    public function ajax_validate_and_exempt_vat_block($value)
    {
        $require_vat = get_option('vat_guard_woocommerce_require_vat', 1);
        $main = VAT_Guard_WooCommerce::instance();
        if ($require_vat && empty($value)) {
            $main->set_vat_exempt_status('');
            return new WP_Error('vat_number_error', __('Please enter your VAT number.', 'vat-guard-woocommerce'));
        }
        if (!empty($value)) {
            $error_message = '';
            if (!$main->is_valid_eu_vat_number($value, $error_message)) {
                $main->set_vat_exempt_status('');
                return new WP_Error('vat_number_error', $error_message);
            }
            // Set VAT exempt status if valid
            $main->set_vat_exempt_status($value);
        } else {
            $main->set_vat_exempt_status('');
        }
        return true;
    }

    public function show_vat_exempt_notice_checkout()
    {
        if (WC()->customer && WC()->customer->get_is_vat_exempt()) {
            echo '<tr class="vat-exempt"><th>' . esc_html__('VAT', 'vat-guard-woocommerce') . '</th><td><strong>' . esc_html__('VAT exempt', 'vat-guard-woocommerce') . '</strong></td></tr>';
        }
    }
}
