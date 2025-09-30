/**
 * VAT Guard Block Checkout Support
 * Provides real-time VAT validation and exemption status for WooCommerce Block Checkout
 */

(function () {
    'use strict';

    // Wait for WooCommerce blocks to be available
    const initVatGuard = () => {
        if (!window.wc || !window.wc.blocksCheckout) {
            setTimeout(initVatGuard, 100);
            return;
        }

        //const { registerCheckoutFilters } = window.wc.blocksCheckout;
        const { __ } = window.wp.i18n;
        const { createElement, useState, useEffect, useRef } = window.wp.element;
        const { select, dispatch } = window.wp.data;

        // Debounce function for API calls
        // function debounce(func, wait) {
        //     let timeout;
        //     return function executedFunction(...args) {
        //         const later = () => {
        //             clearTimeout(timeout);
        //             func(...args);
        //         };
        //         clearTimeout(timeout);
        //         timeout = setTimeout(later, wait);
        //     };
        // }

        // Validate VAT number via REST API
        // const validateVatNumber = debounce(async (vatNumber, billingCountry, callback) => {
        //     if (!vatNumber || vatNumber.length < 4) {
        //         callback({ isValidating: false, isValid: true, isExempt: false, message: '' });
        //         return;
        //     }

        //     callback({ isValidating: true, isValid: false, isExempt: false, message: vatGuardBlock.messages.validating });

        //     try {
        //         const response = await fetch(vatGuardBlock.restUrl, {
        //             method: 'POST',
        //             headers: {
        //                 'Content-Type': 'application/json',
        //                 'X-WP-Nonce': vatGuardBlock.nonce
        //             },
        //             body: JSON.stringify({
        //                 vat_number: vatNumber,
        //                 billing_country: billingCountry
        //             })
        //         });

        //         const data = await response.json();

        //         callback({
        //             isValidating: false,
        //             isValid: data.valid,
        //             isExempt: data.exempt,
        //             message: data.message || ''
        //         });

        //         // Update checkout if VAT exemption status changed
        //         if (data.exempt !== undefined) {
        //             // Trigger a checkout update to refresh totals
        //             const checkoutStore = select('wc/store/checkout');
        //             if (checkoutStore) {
        //                 dispatch('wc/store/checkout').__internalSetIdle();
        //             }
        //         }

        //     } catch (error) {
        //         console.error('VAT validation error:', error);
        //         callback({
        //             isValidating: false,
        //             isValid: false,
        //             isExempt: false,
        //             message: __('VAT validation failed. Please try again.', 'vat-guard-woocommerce')
        //         });
        //     }
        // }, 1000);

        // VAT validation message component
        // const VatValidationMessage = ({ vatNumber, billingCountry }) => {
        //     const [validationState, setValidationState] = useState({
        //         isValidating: false,
        //         isValid: true,
        //         isExempt: false,
        //         message: ''
        //     });

        //     const prevVatNumber = useRef();
        //     const prevBillingCountry = useRef();

        //     useEffect(() => {
        //         // Only validate if VAT number or billing country changed
        //         if (vatNumber !== prevVatNumber.current || billingCountry !== prevBillingCountry.current) {
        //             prevVatNumber.current = vatNumber;
        //             prevBillingCountry.current = billingCountry;

        //             if (vatNumber && vatNumber.length >= 4) {
        //                 validateVatNumber(vatNumber, billingCountry, setValidationState);
        //             } else {
        //                 setValidationState({ isValidating: false, isValid: true, isExempt: false, message: '' });
        //             }
        //         }
        //     }, [vatNumber, billingCountry]);

        //     if (!vatNumber) {
        //         return null;
        //     }

        //     if (validationState.isValidating) {
        //         return createElement('div', {
        //             className: 'vat-guard-validation-message validating',
        //             style: { color: '#666', fontSize: '0.9em', marginTop: '5px' }
        //         }, validationState.message);
        //     }

        //     if (!validationState.isValid) {
        //         return createElement('div', {
        //             className: 'vat-guard-validation-message error',
        //             style: { color: '#d63638', fontSize: '0.9em', marginTop: '5px' }
        //         }, validationState.message);
        //     }

        //     if (validationState.isExempt) {
        //         return createElement('div', {
        //             className: 'vat-guard-validation-message exempt',
        //             style: { color: '#00a32a', fontSize: '0.9em', marginTop: '5px', fontWeight: 'bold' }
        //         }, '✓ ' + vatGuardBlock.messages.exempt);
        //     }

        //     if (vatNumber.length >= 4) {
        //         return createElement('div', {
        //             className: 'vat-guard-validation-message valid',
        //             style: { color: '#00a32a', fontSize: '0.9em', marginTop: '5px' }
        //         }, '✓ ' + vatGuardBlock.messages.valid);
        //     }

        //     return null;
        // };

        // Register checkout filters
        // registerCheckoutFilters('vat-guard-woocommerce', {
        //     // Add validation message after VAT field
        //     additionalFields: (additionalFields, extensions, args) => {
        //         return additionalFields.map(field => {
        //             if (field.id === 'vat-guard-woocommerce/vat_number') {
        //                 const billingAddress = select('wc/store/cart')?.getBillingAddress?.() || {};
        //                 const checkoutData = select('wc/store/checkout')?.getCheckoutData?.() || {};

        //                 // Get VAT number from additional fields
        //                 const vatNumber = checkoutData?.additional_fields?.['vat-guard-woocommerce/vat_number'] || '';
        //                 const billingCountry = billingAddress.country || '';

        //                 return {
        //                     ...field,
        //                     children: createElement(VatValidationMessage, {
        //                         vatNumber: vatNumber,
        //                         billingCountry: billingCountry
        //                     })
        //                 };
        //             }
        //             return field;
        //         });
        //     },


        // });

        // Add VAT exempt notice to checkout summary
        const addVatExemptNotice = () => {
            const cartData = select('wc/store/cart')?.getCartData?.() || {};
            const isVatExempt = cartData?.extensions?.['vat-guard-woocommerce']?.vat_exempt;

            // Remove existing notice
            const existingNotice = document.querySelector('.vat-guard-exempt-notice');
            if (existingNotice) {
                existingNotice.remove();
            }

            if (isVatExempt) {
                // Find the checkout totals area
                const totalsArea = document.querySelector('.wc-block-components-totals-wrapper, .wp-block-woocommerce-checkout-order-summary-block');
                if (totalsArea) {
                    const notice = document.createElement('div');
                    notice.className = 'vat-guard-exempt-notice';
                    notice.style.cssText = `
                        background: #f0f9ff;
                        border: 1px solid #00a32a;
                        border-radius: 4px;
                        padding: 10px;
                        margin: 10px 0;
                        color: #00a32a;
                        font-weight: bold;
                        text-align: center;
                    `;
                    notice.innerHTML = '✓ ' + vatGuardBlock.messages.exempt;
                    totalsArea.insertBefore(notice, totalsArea.firstChild);
                }
            }
        };

        // Monitor for cart changes
        let lastCartData = null;
        const monitorCartChanges = () => {
            const cartData = select('wc/store/cart')?.getCartData?.() || {};
            const currentVatExempt = cartData?.extensions?.['vat-guard-woocommerce']?.vat_exempt;

            // Debug: Log cart data
           //  console.log('VAT Guard: Cart data extensions:', cartData?.extensions);
           //TODO: get rid of this
             console.log('VAT Guard: VAT exempt status:', currentVatExempt);

            if (lastCartData !== currentVatExempt) {
                lastCartData = currentVatExempt;
                setTimeout(addVatExemptNotice, 100); // Small delay to ensure DOM is updated
            }
        };

        // Check for changes periodically
        setInterval(monitorCartChanges, 500);

        // Add CSS for validation messages
        const style = document.createElement('style');
        style.textContent = `
            .vat-guard-validation-message {
                margin: 5px;
                font-size: 0.9em;
                line-height: 1.4;
            }
            .vat-guard-validation-message.validating {
                color: #666;
            }
            .vat-guard-validation-message.error {
                color: #d63638;
            }
            .vat-guard-validation-message.valid {
                color: #00a32a;
            }
            .vat-guard-validation-message.exempt {
                color: #00a32a;
                font-weight: bold;
            }
            .vat-guard-exempt-notice {
                background: #f0f9ff;
                border: 1px solid #00a32a;
                border-radius: 4px;
                padding: 10px;
                margin: 10px 0;
                color: #00a32a;
                font-weight: bold;
                text-align: center;
            }
        `;
        document.head.appendChild(style);
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initVatGuard);
    } else {
        initVatGuard();
    }
})();