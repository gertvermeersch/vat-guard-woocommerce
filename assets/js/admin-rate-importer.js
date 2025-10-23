/**
 * VAT Rate Importer Admin JavaScript
 * 
 * @package EU_VAT_Guard_For_WooCommerce
 * @since 1.2.0
 */

jQuery(document).ready(function ($) {
    'use strict';

    // Handle select all countries functionality
    $('#select-all-countries').change(function () {
        $('.country-checkbox').prop('checked', this.checked);
    });

    // Handle individual country checkbox changes
    $('.country-checkbox').change(function () {
        if (!this.checked) {
            $('#select-all-countries').prop('checked', false);
        } else if ($('.country-checkbox:checked').length === $('.country-checkbox').length) {
            $('#select-all-countries').prop('checked', true);
        }
    });
});