define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'mage/cookies'
], function ($, customerData) {
    'use strict';

    return {
        processed: false,
        cookieName: 'nsgpp_reload',

        /**
         * Initialize the handler
         */
        init: function() {
            let self = this;

            setInterval(function() {
                self.checkReloadCookie();
            }, 2000);
        },

        /**
         * Check for the reload cookie and process if found
         */
        checkReloadCookie: function() {
            let cookieValue = $.mage.cookies.get(this.cookieName);

            if (cookieValue && cookieValue.length > 0) {
                try {
                    let data = JSON.parse(cookieValue);

                    if (!this.processed && data && data.action === 'reload_totals') {
                        this.reloadTotals();
                        /** Mark as processed */
                        this.processed = true;

                        this.clearCookie();

                        /** Reset the processed flag after a short delay */
                        let self = this;
                        setTimeout(function() {
                            self.processed = false;
                        }, 5000);
                    }
                } catch (e) {
                    console.error('Error parsing NSGPP reload cookie:', e);
                    /** Clear invalid cookie */
                    this.clearCookie();
                }
            }
        },

        /**
         * Clear the cookie properly
         * Use multiple approaches to ensure cookie is cleared
         */
        clearCookie: function() {
            $.mage.cookies.set(this.cookieName, '', {
                expires: new Date('1970-01-01'),
                path: '/'
            });

            /** Fallback if the above doesn't work */
            document.cookie = this.cookieName +
                '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        },

        /**
         * Reload the cart totals
         */
        reloadTotals: function() {
            let sections = ['cart'];
            customerData.reload(sections, true);

            /** Load necessary modules if on checkout page */
            if (typeof window.checkoutConfig !== 'undefined') {
                require([
                    'Magento_Checkout/js/model/cart/totals-processor/default',
                    'Magento_Checkout/js/model/quote'
                ], function(totalsProcessor, quote) {
                    if (totalsProcessor && quote) {
                        totalsProcessor.estimateTotals(quote.getQuoteId());
                    }
                });
            }

            /** If on cart page, update totals component */
            if (window.location.pathname.indexOf('checkout/cart') !== -1) {
                this.updateCartPage();
            }
        },

        /**
         * Update the cart page
         */
        updateCartPage: function() {
            if ($.mage && $.mage.cookies) {
                let formKey = $.mage.cookies.get('form_key');

                if (formKey) {
                    $.ajax({
                        url: this.getBaseUrl() + 'checkout/cart/updatePost',
                        type: 'POST',
                        data: {
                            'form_key': formKey,
                            'update_cart_action': 'update_qty'
                        },
                        success: function() {
                            customerData.reload(['cart'], true);
                        }
                    });
                }
            }
        },

        /**
         * Get base URL
         *
         * @returns {String}
         */
        getBaseUrl: function() {
            if (typeof window.BASE_URL !== 'undefined') {
                return window.BASE_URL;
            }

            var baseUrl = '';
            var baseElements = document.getElementsByTagName('base');
            if (baseElements.length > 0) {
                baseUrl = baseElements[0].href;
            }

            return baseUrl;
        }
    };
});
