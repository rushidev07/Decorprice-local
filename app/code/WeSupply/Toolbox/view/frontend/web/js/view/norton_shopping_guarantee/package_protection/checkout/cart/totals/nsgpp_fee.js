define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals'
], function (Component, quote, priceUtils, totals) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'WeSupply_Toolbox/norton_shopping_guarantee/package_protection/checkout/cart/totals/nsgpp_fee'
        },
        totals: quote.getTotals(),

        /** @return {*|Boolean} */
        isDisplayed: function () {
            return this.getValue() > 0;
        },

        /** @returns {*} */
        getValue: function () {
            let price = 0;

            if (this.totals()) {
                /** Try to get the segment first */
                let segment = totals.getSegment('nsgpp_fee');

                if (segment) {
                    return segment.value;
                }

                /** Fall back to checking quote data directly */
                let quoteData = window.checkoutConfig.quoteData;
                if (quoteData && quoteData.nsgpp_fee) {
                    price = quoteData.nsgpp_fee;
                }
            }

            return price;
        },

        /** * @return {*|String} */
        getFormattedPrice: function () {
            return priceUtils.formatPrice(this.getValue(), quote.getPriceFormat());
        }
    });
});
