<?php

/**
 * VAT Guard Block Integration for WooCommerce Store API
 */

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

if (!defined('ABSPATH')) {
    exit;
}

class VAT_Guard_Block_Integration implements IntegrationInterface
{
    /**
     * Reference to the main VAT Guard class
     * @var VAT_Guard_WooCommerce
     */
    private $main_class;

    /**
     * Constructor
     * @param VAT_Guard_WooCommerce $main_class
     */
    public function __construct($main_class = null)
    {
        $this->main_class = $main_class ?: VAT_Guard_WooCommerce::instance();
    }

    /**
     * Initialize block checkout functionality
     */
    public function init()
    {
        $this->setup_block_checkout_fields();
        $this->setup_block_checkout_hooks();
        $this->setup_block_checkout_scripts();
        $this->setup_rest_api();
        $this->setup_store_api_integration();
        
        // Also call the interface initialize method for Store API integration
        $this->initialize();
    }
    /**
     * The name of the integration.
     */
    public function get_name()
    {
        return 'vat-guard-woocommerce';
    }

    /**
     * When called invokes any initialization/setup for the integration.
     */
    public function initialize()
    {
        $this->register_block_frontend_scripts();
        $this->register_block_editor_scripts();
        $this->register_main_integration();
    }

    /**
     * Returns an array of script handles to enqueue in the frontend context.
     */
    public function get_script_handles()
    {
        return ['vat-guard-block-frontend'];
    }

    /**
     * Returns an array of script handles to enqueue in the editor context.
     */
    public function get_editor_script_handles()
    {
        return ['vat-guard-block-editor'];
    }

    /**
     * An array of key, value pairs of data made available to the block on the client side.
     */
    public function get_script_data()
    {
        return [
            'restUrl' => rest_url('vat-guard/v1/validate'),
            'nonce' => wp_create_nonce('wp_rest'),
            'messages' => [
                'validating' => __('Validating VAT number...', 'vat-guard-woocommerce'),
                'valid' => __('VAT number is valid', 'vat-guard-woocommerce'),
                'exempt' => __('VAT exempt for this order', 'vat-guard-woocommerce'),
                'invalid' => __('Invalid VAT number', 'vat-guard-woocommerce')
            ]
        ];
    }

    /**
     * Register scripts for frontend.
     */
    private function register_block_frontend_scripts()
    {
        $script_path = plugin_dir_path(dirname(__FILE__)) . 'assets/js/vat-guard-block-frontend.js';
        $script_url = plugin_dir_url(dirname(__FILE__)) . 'assets/js/vat-guard-block-frontend.js';

        wp_register_script(
            'vat-guard-block-frontend',
            $script_url,
            ['wp-element', 'wp-i18n', 'wp-data'],
            filemtime($script_path),
            true
        );
    }

    /**
     * Register scripts for editor.
     */
    private function register_block_editor_scripts()
    {
        $script_path = plugin_dir_path(dirname(__FILE__)) . 'assets/js/vat-guard-block-editor.js';
        $script_url = plugin_dir_url(dirname(__FILE__)) . 'assets/js/vat-guard-block-editor.js';

        wp_register_script(
            'vat-guard-block-editor',
            $script_url,
            ['wp-element', 'wp-i18n'],
            filemtime($script_path),
            true
        );
    }

    /**
     * Register the main integration with Store API.
     */
    private function register_main_integration()
    {
        if (!function_exists('woocommerce_store_api_register_endpoint_data')) {
            return;
        }

        woocommerce_store_api_register_endpoint_data([
            'endpoint' => \Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema::IDENTIFIER,
            'namespace' => $this->get_name(),
            'data_callback' => [$this, 'extend_checkout_data'],
            'schema_callback' => [$this, 'extend_checkout_schema'],
        ]);

        woocommerce_store_api_register_endpoint_data([
            'endpoint' => \Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema::IDENTIFIER,
            'namespace' => $this->get_name(),
            'data_callback' => [$this, 'extend_cart_data'],
            'schema_callback' => [$this, 'extend_cart_schema'],
        ]);

        // Also register for batch endpoint to ensure data is available everywhere
        woocommerce_store_api_register_endpoint_data([
            'endpoint' => \Automattic\WooCommerce\StoreApi\Schemas\V1\BatchSchema::IDENTIFIER,
            'namespace' => $this->get_name(),
            'data_callback' => [$this, 'extend_cart_data'],
            'schema_callback' => [$this, 'extend_cart_schema'],
        ]);
    }

    /**
     * Extend checkout data with VAT information.
     */
    public function extend_checkout_data()
    {
        $is_exempt = WC()->customer ? WC()->customer->get_is_vat_exempt() : false;
        return [
            'vat_exempt' => $is_exempt,
            'vat_number' => $this->get_customer_vat_number(),
            'vat_exempt_message' => $is_exempt ? __('VAT exempt for this order', 'vat-guard-woocommerce') : '',
        ];
    }

    /**
     * Extend cart data with VAT information.
     */
    public function extend_cart_data()
    {
        $is_exempt = WC()->customer ? WC()->customer->get_is_vat_exempt() : false;
        
        // Debug: Log the VAT exempt status
        error_log('VAT Guard: Cart data - VAT exempt status: ' . ($is_exempt ? 'true' : 'false'));
        
        return [
            'vat_exempt' => $is_exempt,
            'vat_number' => $this->get_customer_vat_number(),
            'vat_exempt_message' => $is_exempt ? __('VAT exempt for this order', 'vat-guard-woocommerce') : '',
        ];
    }

    /**
     * Schema for checkout extension data.
     */
    public function extend_checkout_schema()
    {
        return [
            'vat_exempt' => [
                'description' => __('Whether the customer is VAT exempt', 'vat-guard-woocommerce'),
                'type' => 'boolean',
                'readonly' => true,
            ],
            'vat_number' => [
                'description' => __('Customer VAT number', 'vat-guard-woocommerce'),
                'type' => 'string',
                'readonly' => true,
            ],
            'vat_exempt_message' => [
                'description' => __('VAT exempt message to display', 'vat-guard-woocommerce'),
                'type' => 'string',
                'readonly' => true,
            ],
        ];
    }

    /**
     * Schema for cart extension data.
     */
    public function extend_cart_schema()
    {
        return $this->extend_checkout_schema();
    }

    /**
     * Get customer VAT number from various sources.
     */
    private function get_customer_vat_number()
    {
        if (is_user_logged_in()) {
            return get_user_meta(get_current_user_id(), 'vat_number', true);
        }
        return '';
    }

    /**
     * Setup block checkout fields
     */
    private function setup_block_checkout_fields()
    {
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
                    'validate_callback' => array($this, 'validate_vat_field')
                )
            );
        });
    }

    /**
     * Setup block checkout hooks
     */
    private function setup_block_checkout_hooks()
    {
        // Handle VAT number updates and exemption status
        add_action(
            'woocommerce_set_additional_field_value',
            array($this, 'handle_vat_field_update'),
            10,
            4
        );

        // Preload the VAT number field with user meta data if available
        add_filter(
            "woocommerce_get_default_value_for_vat-guard-woocommerce/vat_number",
            array($this, 'preload_vat_field'),
            10,
            3
        );


    }

    /**
     * Setup block checkout scripts
     */
    private function setup_block_checkout_scripts()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_block_checkout_scripts'));
    }

    /**
     * Setup REST API endpoints
     */
    private function setup_rest_api()
    {
        add_action('rest_api_init', array($this, 'register_vat_validation_endpoint'));
    }

    /**
     * Setup Store API integration
     */
    private function setup_store_api_integration()
    {
        add_action('woocommerce_blocks_loaded', array($this, 'register_block_checkout_integration'));
    }

    /**
     * Handle VAT field updates
     */
    public function handle_vat_field_update($key, $value, $group, $wc_object)
    {
        if ('vat-guard-woocommerce/vat_number' !== $key) {
            return;
        }

        // Clean and validate VAT number
        $vat = strtoupper(str_replace([' ', '-', '.'], '', $value));
        $wc_object->update_meta_data('billing_eu_vat_number', $vat, true);

        // Set VAT exemption status
        $this->main_class->set_vat_exempt_status($vat);
        $is_exempt = WC()->customer->get_is_vat_exempt();
        $wc_object->update_meta_data('billing_is_vat_exempt', $is_exempt ? 'yes' : 'no');

        // Update user meta if logged in
        if (is_user_logged_in() && !empty($vat)) {
            $user_id = get_current_user_id();
            $current_vat = get_user_meta($user_id, 'vat_number', true);
            if ($vat !== $current_vat) {
                update_user_meta($user_id, 'vat_number', $vat);
            }
        }
    }

    /**
     * Preload VAT field with user data
     */
    public function preload_vat_field($value, $group, $wc_object)
    {
        if (is_user_logged_in()) {
            $vat = get_user_meta(get_current_user_id(), 'vat_number', true);
            if (!empty($vat)) {
                return $vat;
            }
        }
        return $value;
    }

    /**
     * Validate VAT field for block checkout
     */
    public function validate_vat_field($value, $fields = null)
    {
        $require_vat = get_option('vat_guard_woocommerce_require_vat', 1);

        // Get shipping country from Blocks Checkout API data structure
        $shipping_country = '';
        if (is_array($fields)) {
            // Get shipping country from shipping address
            if (isset($fields['shippingAddress']['country']) && !empty($fields['shippingAddress']['country'])) {
                $shipping_country = strtoupper(trim($fields['shippingAddress']['country']));
            }
            // Fallback to billing country for virtual orders
            elseif (isset($fields['billingAddress']['country']) && !empty($fields['billingAddress']['country'])) {
                $shipping_country = strtoupper(trim($fields['billingAddress']['country']));
            }
        }

        if ($require_vat && empty($value)) {
            $this->main_class->set_vat_exempt_status('');
            return new WP_Error('vat_number_error', __('Please enter your VAT number.', 'vat-guard-woocommerce'));
        }

        if (!empty($value)) {
            $error_message = '';
            if (!$this->main_class->is_valid_eu_vat_number($value, $error_message)) {
                $this->main_class->set_vat_exempt_status('');
                return new WP_Error('vat_number_error', $error_message);
            }

            // Check shipping country matches VAT country
            $vat_country = substr($value, 0, 2);
            if (!empty($shipping_country) && $shipping_country !== $vat_country) {
                $this->main_class->set_vat_exempt_status('');
                return new WP_Error('vat_number_error', __('The shipping country must match the country of the VAT number.', 'vat-guard-woocommerce'));
            }

            // Set VAT exempt status if valid
            $this->main_class->set_vat_exempt_status($value);
        } else {
            $this->main_class->set_vat_exempt_status('');
        }

        return true;
    }

    /**
     * Enqueue scripts for block checkout
     */
    public function enqueue_block_checkout_scripts()
    {
        if (!has_block('woocommerce/checkout') && !has_block('woocommerce/cart')) {
            return;
        }

        // Fallback script for when Store API integration is not available
        wp_enqueue_script(
            'vat-guard-block-checkout-fallback',
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/vat-guard-block-checkout.js',
            array('wp-element', 'wp-hooks', 'wp-data'),
            '1.0.0',
            true
        );

        wp_localize_script('vat-guard-block-checkout-fallback', 'vatGuardBlock', array(
            'restUrl' => rest_url('vat-guard/v1/validate'),
            'nonce' => wp_create_nonce('wp_rest'),
            'messages' => array(
                'validating' => __('Validating VAT number...', 'vat-guard-woocommerce'),
                'valid' => __('VAT number is valid', 'vat-guard-woocommerce'),
                'exempt' => __('VAT exempt for this order', 'vat-guard-woocommerce'),
                'invalid' => __('Invalid VAT number', 'vat-guard-woocommerce')
            )
        ));
    }

    /**
     * Register REST API endpoint for VAT validation
     */
    public function register_vat_validation_endpoint()
    {
        register_rest_route('vat-guard/v1', '/validate', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_validate_vat'),
            'permission_callback' => '__return_true',
            'args' => array(
                'vat_number' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'billing_country' => array(
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));
    }

    /**
     * REST API callback for VAT validation
     */
    public function rest_validate_vat($request)
    {
        $vat = $request->get_param('vat_number');
        $billing_country = $request->get_param('billing_country');

        if (empty($vat)) {
            return new WP_REST_Response(array(
                'valid' => true,
                'exempt' => false,
                'message' => ''
            ), 200);
        }

        $error_message = '';
        $is_valid = $this->main_class->is_valid_eu_vat_number($vat, $error_message);

        if (!$is_valid) {
            return new WP_REST_Response(array(
                'valid' => false,
                'exempt' => false,
                'message' => $error_message
            ), 200);
        }

        // Check country matching
        $vat_country = substr(strtoupper(str_replace([' ', '-', '.'], '', $vat)), 0, 2);
        if (!empty($billing_country) && $billing_country !== $vat_country) {
            return new WP_REST_Response(array(
                'valid' => false,
                'exempt' => false,
                'message' => __('The billing country must match the country of the VAT number.', 'vat-guard-woocommerce')
            ), 200);
        }

        // Check if VAT exempt
        $shop_base_country = wc_get_base_location()['country'];
        $is_exempt = !empty($vat) && $vat_country && $vat_country !== $shop_base_country;

        return new WP_REST_Response(array(
            'valid' => true,
            'exempt' => $is_exempt,
            'message' => $is_exempt ? __('VAT exempt for this order', 'vat-guard-woocommerce') : __('VAT number is valid', 'vat-guard-woocommerce')
        ), 200);
    }

    /**
     * Register Store API integration for block checkout
     */
    public function register_block_checkout_integration()
    {
        if (!class_exists('Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface')) {
            return;
        }

        add_action('woocommerce_blocks_checkout_block_registration', function ($integration_registry) {
            $integration_registry->register($this);
        });
    }

   
}
