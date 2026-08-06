define([
    'uiComponent',
    'ko',
    'jquery'
], function (Component, ko, $) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'WeSupply_Toolbox/norton_shopping_guarantee/package_protection/checkout/nsgpp_checkout',
            nonce: ''
        },

        initialize: function () {
            this._super();
            this.nonce = this.getCookie('nsgpp_nonce') || '';
            return this;
        },

        isNsgPpEnabled: function () {
            return window.checkoutConfig.isNsgPpEnabled
        },

        getNonce: function() {
            return this.nonce || '';
        },

        getCookie: function(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) {
                return parts.pop().split(';').shift();
            }
            return null;
        }
    });
});
