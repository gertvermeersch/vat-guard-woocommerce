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
}