// Description: This script disables the "Place Order" button on the WooCommerce checkout page if there are any errors or notices present.
jQuery(function($){
    function togglePlaceOrderButton() {
        var hasError = $('.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message').find('li, .woocommerce-error').length > 0;
        var $button = $('#place_order');
        if (hasError) {
            $button.prop('disabled', true);
        } else {
            $button.prop('disabled', false);
        }
    }
    // Run on checkout update and error events
    $('body').on('updated_checkout checkout_error', togglePlaceOrderButton);
    // Initial check
    togglePlaceOrderButton();
});