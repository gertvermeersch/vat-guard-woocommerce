/**
 * VAT Guard Block Frontend Integration
 */

(function() {
    'use strict';

    const { createElement, useState, useEffect } = wp.element;
    const { __ } = wp.i18n;
    const { select, subscribe } = wp.data;

    // Wait for WooCommerce blocks to be available
    const initVatGuard = () => {
        if (!window.wc || !window.wc.blocksCheckout) {
            setTimeout(initVatGuard, 100);
            return;
        }

        const { registerCheckoutFilters } = window.wc.blocksCheckout;

        // Debounce function
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // VAT validation component
        const VatValidationMessage = ({ vatNumber, billingCountry }) => {
            const [validationState, setValidationState] = useState({
                isValidating: false,
                isValid: true,
                isExempt: false,
                message: ''
            });

            const validateVat = debounce(async (vat, country) => {
                if (!vat || vat.length < 4) {
                    setValidationState({ isValidating: false, isValid: true, isExempt: false, message: '' });
                    return;
                }

                setValidationState(prev => ({ ...prev, isValidating: true, message: vatGuardBlockIntegration.messages.validating }));

                try {
                    const response = await fetch(vatGuardBlockIntegration.restUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': vatGuardBlockIntegration.nonce
                        },
                        body: JSON.stringify({
                            vat_number: vat,
                            billing_country: country
                        })
                    });

                    const data = await response.json();
                    
                    setValidationState({
                        isValidating: false,
                        isValid: data.valid,
                        isExempt: data.exempt,
                        message: data.message || ''
                    });

                } catch (error) {
                    console.error('VAT validation error:', error);
                    setValidationState({
                        isValidating: false,
                        isValid: false,
                        isExempt: false,
                        message: __('VAT validation failed. Please try again.', 'vat-guard-woocommerce')
                    });
                }
            }, 1000);

            useEffect(() => {
                validateVat(vatNumber, billingCountry);
            }, [vatNumber, billingCountry]);

            if (!vatNumber) {
                return null;
            }

            const messageStyle = {
                marginTop: '5px',
                fontSize: '0.9em',
                lineHeight: '1.4'
            };

            if (validationState.isValidating) {
                return createElement('div', { 
                    className: 'vat-guard-validation-message validating',
                    style: { ...messageStyle, color: '#666' }
                }, validationState.message);
            }

            if (!validationState.isValid) {
                return createElement('div', { 
                    className: 'vat-guard-validation-message error',
                    style: { ...messageStyle, color: '#d63638' }
                }, validationState.message);
            }

            if (validationState.isExempt) {
                return createElement('div', { 
                    className: 'vat-guard-validation-message exempt',
                    style: { ...messageStyle, color: '#00a32a', fontWeight: 'bold' }
                }, '✓ ' + vatGuardBlockIntegration.messages.exempt);
            }

            if (vatNumber.length >= 4) {
                return createElement('div', { 
                    className: 'vat-guard-validation-message valid',
                    style: { ...messageStyle, color: '#00a32a' }
                }, '✓ ' + vatGuardBlockIntegration.messages.valid);
            }

            return null;
        };

        // Register checkout filters
        registerCheckoutFilters('vat-guard-woocommerce', {
            additionalFields: (additionalFields) => {
                return additionalFields.map(field => {
                    if (field.id === 'vat-guard-woocommerce/vat_number') {
                        // Get current checkout data
                        const checkoutData = select('wc/store/checkout')?.getCheckoutData?.() || {};
                        const billingAddress = checkoutData.billingAddress || {};
                        const extensionData = checkoutData.extensions || {};
                        
                        const vatNumber = extensionData['vat-guard-woocommerce/vat_number'] || '';
                        const billingCountry = billingAddress.country || '';

                        return {
                            ...field,
                            children: createElement(VatValidationMessage, {
                                vatNumber: vatNumber,
                                billingCountry: billingCountry
                            })
                        };
                    }
                    return field;
                });
            },
            

        });

        // Show VAT exempt notice in totals
        let lastVatExemptStatus = false;
        let lastVatExemptMessage = '';
        
        const updateVatExemptNotice = () => {
            const cartData = select('wc/store/cart')?.getCartData?.() || {};
            const vatGuardData = cartData.extensions?.['vat-guard-woocommerce'] || {};
            const isVatExempt = vatGuardData.vat_exempt || false;
            const vatExemptMessage = vatGuardData.vat_exempt_message || vatGuardBlockIntegration.messages.exempt;
            
            if (isVatExempt !== lastVatExemptStatus || vatExemptMessage !== lastVatExemptMessage) {
                lastVatExemptStatus = isVatExempt;
                lastVatExemptMessage = vatExemptMessage;
                
                setTimeout(() => {
                    // Try multiple selectors for the totals wrapper
                    const totalsWrapper = document.querySelector('.wc-block-components-totals-wrapper') ||
                                        document.querySelector('.wp-block-woocommerce-checkout-totals-block .wc-block-components-totals-wrapper') ||
                                        document.querySelector('.wc-block-checkout__totals');
                    
                    const existingNotice = document.querySelector('.vat-exempt-notice');
                    
                    if (existingNotice) {
                        existingNotice.remove();
                    }
                    
                    if (isVatExempt && totalsWrapper && vatExemptMessage) {
                        const notice = document.createElement('div');
                        notice.className = 'wc-block-components-totals-item vat-exempt-notice';
                        notice.style.cssText = `
                            background-color: #e7f7e7;
                            border-left: 4px solid #00a32a;
                            padding: 8px 12px;
                            margin: 8px 0;
                            border-radius: 3px;
                            color: #00a32a;
                            font-weight: bold;
                            text-align: center;
                            font-size: 0.9em;
                        `;
                        notice.innerHTML = '✓ ' + vatExemptMessage;
                        
                        // Insert at the top of totals
                        totalsWrapper.insertBefore(notice, totalsWrapper.firstChild);
                    }
                }, 150);
            }
        };

        // Subscribe to cart and checkout changes
        subscribe(updateVatExemptNotice);
        
        // Also run on initial load and when checkout updates
        setTimeout(updateVatExemptNotice, 500);
        
        // Listen for checkout updates
        document.addEventListener('wc-blocks_checkout_updated', updateVatExemptNotice);
        document.addEventListener('wc-blocks_cart_updated', updateVatExemptNotice);
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initVatGuard);
    } else {
        initVatGuard();
    }
})();