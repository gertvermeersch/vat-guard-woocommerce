<?php
// VAT Guard for WooCommerce Main Class
if (!defined('ABSPATH')) {
    exit;
}

class VAT_Guard_WooCommerce {
    private static $instance = null;

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Add hooks here (registration fields, account fields, etc.)
        add_action('woocommerce_register_form', array($this, 'add_registration_fields'));
        add_action('woocommerce_edit_account_form_start', array($this, 'add_account_fields'));
        add_filter('woocommerce_registration_errors', array($this, 'validate_registration_fields'), 10, 3);
        add_action('woocommerce_created_customer', array($this, 'save_fields'));
        add_action('woocommerce_save_account_details', array($this, 'save_fields'));
        add_filter('woocommerce_checkout_get_value', array($this, 'preload_checkout_fields'), 10, 2);
        add_filter('woocommerce_default_address_fields', array($this, 'default_billing_company'));

        // Admin logic moved to VAT_Guard_WooCommerce_Admin
        if (is_admin()) {
            require_once plugin_dir_path(__FILE__) . 'class-vat-guard-woocommerce-admin.php';
        }
        // VIES logic
        require_once plugin_dir_path(__FILE__) . 'class-vat-guard-woocommerce-vies.php';
    }

    public function add_registration_fields() {
        ?>
        <?php
        $require_company = get_option('vat_guard_woocommerce_require_company', 1);
        $require_vat = get_option('vat_guard_woocommerce_require_vat', 1);
        ?>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <input type="text"
               class="woocommerce-Input woocommerce-Input--text input-text"
               name="company_name"
               id="company_name"
               placeholder="<?php _e('Company Name', 'vat-guard-woocommerce'); ?><?php echo $require_company ? ' *' : ''; ?>"
               <?php if ($require_company) echo 'required'; ?>
               value="<?php if (!empty($_POST['company_name'])) esc_attr_e($_POST['company_name']); ?>" />
        </p>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <input type="text"
               class="woocommerce-Input woocommerce-Input--text input-text"
               name="vat_number"
               id="vat_number"
               placeholder="<?php _e('VAT Number', 'vat-guard-woocommerce'); ?><?php echo $require_vat ? ' *' : ''; ?>"
               <?php if ($require_vat) echo 'required'; ?>
               value="<?php if (!empty($_POST['vat_number'])) esc_attr_e($_POST['vat_number']); ?>" />
        </p>
        <?php
    }

    public function add_account_fields() {
        $company_name = get_user_meta(get_current_user_id(), 'company_name', true);
        $vat_number = get_user_meta(get_current_user_id(), 'vat_number', true);
         
        $require_company = get_option('vat_guard_woocommerce_require_company', 1);
        $require_vat = get_option('vat_guard_woocommerce_require_vat', 1);
        ?>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="company_name"><?php _e('Company Name', 'vat-guard-woocommerce'); 
             if($require_company) { ?><span class="required">*</span> <?php } ?></label>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="company_name" id="company_name" value="<?php esc_attr_e($company_name); ?>" />
        </p>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="vat_number"><?php _e('VAT Number', 'vat-guard-woocommerce'); if($require_vat) { ?><span class="required">*</span> <?php } ?></label>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="vat_number" id="vat_number" value="<?php esc_attr_e($vat_number); ?>" />
        </p>
        <?php
    }

    public function validate_registration_fields($errors, $username, $email) {
        $require_company = get_option('vat_guard_woocommerce_require_company', 1);
        $require_vat = get_option('vat_guard_woocommerce_require_vat', 1);
        $require_vies = get_option('vat_guard_woocommerce_require_vies', 0);
        if ($require_company && empty($_POST['company_name'])) {
            $errors->add('company_name_error', __('Please enter your company name.', 'vat-guard-woocommerce'));
        }
        if ($require_vat && empty($_POST['vat_number'])) {
            $errors->add('vat_number_error', __('Please enter your VAT number.', 'vat-guard-woocommerce'));
        } elseif (!empty($_POST['vat_number']) && !$this->is_valid_eu_vat_number($_POST['vat_number'])) {
            $errors->add('vat_number_error', __('Please enter a valid EU VAT number.', 'vat-guard-woocommerce'));
        } elseif ($require_vies && !empty($_POST['vat_number'])) {
            $vat = strtoupper(str_replace([' ', '-', '.'], '', $_POST['vat_number']));
            $country = substr($vat, 0, 2);
            $number = substr($vat, 2);
            $vies_result = VAT_Guard_WooCommerce_VIES::check_vat($country, $number);
            if ($vies_result === false) {
                $errors->add('vat_number_error', __('This VAT number could not be validated with the VIES service.', 'vat-guard-woocommerce'));
            } elseif ($vies_result === null) {
                $errors->add('vat_number_error', __('VIES validation is currently unavailable. Please try again later or contact support.', 'vat-guard-woocommerce'));
            }
        }
        return $errors;
    }

    /**
     * Basic offline validation for EU VAT numbers (structure only, not VIES check)
     */
    private function is_valid_eu_vat_number($vat) {
        $vat = strtoupper(str_replace([' ', '-', '.'], '', $vat));
        // List of EU country codes
        $eu_countries = [
            'AT','BE','BG','CY','CZ','DE','DK','EE','EL','ES','FI','FR','HR','HU','IE','IT','LT','LU','LV','MT','NL','PL','PT','RO','SE','SI','SK'
        ];
        $country = substr($vat, 0, 2);
        if (!in_array($country, $eu_countries)) {
            return false;
        }
        // Basic length check (8-14 chars typical)
        if (strlen($vat) < 8 || strlen($vat) > 14) {
            return false;
        }
        // Country-specific regex (partial, extend as needed)
        $patterns = [
            'BE' => '/^BE0?\d{9}$/',
            'DE' => '/^DE[0-9]{9}$/',
            'FR' => '/^FR[0-9A-Z]{2}\d{9}$/',
            'NL' => '/^NL[0-9]{9}B[0-9]{2}$/',
            'IT' => '/^IT[0-9]{11}$/',
            'ES' => '/^ES[A-Z0-9][0-9]{7}[A-Z0-9]$/',
            // ...add more as needed
        ];
        if (isset($patterns[$country])) {
            return preg_match($patterns[$country], $vat) === 1;
        }
        // Fallback: just check country code and length
        return true;
    }

    public function save_fields($customer_id) {
        $require_company = get_option('vat_guard_woocommerce_require_company', 1);
        $require_vat = get_option('vat_guard_woocommerce_require_vat', 1);
        $require_vies = get_option('vat_guard_woocommerce_require_vies', 0);
        // Validate VAT number on account edit
        if ($require_vat && isset($_POST['vat_number']) && empty($_POST['vat_number'])) {
            wc_add_notice(__('Please enter your VAT number.', 'vat-guard-woocommerce'), 'error');
            return;
        }
        if (!empty($_POST['vat_number']) && !$this->is_valid_eu_vat_number($_POST['vat_number'])) {
            wc_add_notice(__('Please enter a valid EU VAT number.', 'vat-guard-woocommerce'), 'error');
            return;
        }
        if ($require_vies && !empty($_POST['vat_number'])) {
            $vat = strtoupper(str_replace([' ', '-', '.'], '', $_POST['vat_number']));
            $country = substr($vat, 0, 2);
            $number = substr($vat, 2);
            $vies_result = VAT_Guard_WooCommerce_VIES::check_vat($country, $number);
            if ($vies_result === false) {
                wc_add_notice(__('This VAT number could not be validated with the VIES service.', 'vat-guard-woocommerce'), 'error');
                return;
            } elseif ($vies_result === null) {
                wc_add_notice(__('VIES validation is currently unavailable. Please try again later or contact support.', 'vat-guard-woocommerce'), 'error');
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

    public function preload_checkout_fields($value, $input) {
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

    public function default_billing_company($fields) {
        if (is_user_logged_in()) {
            $company_name = get_user_meta(get_current_user_id(), 'company_name', true);
            if (!empty($company_name)) {
                $fields['company']['default'] = $company_name;
            }
        }
        return $fields;
    }
}
