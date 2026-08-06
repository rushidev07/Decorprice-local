define([
    'Magento_Ui/js/view/messages',
    '../../model/payment/reward-points-messages'
], function (Component, messageContainer) {
    'use strict';
    return Component.extend({
        
        /**
         * Initialize component
         * 
         * @return {Object}
         */
        initialize: function (config) {
            return this._super(config, messageContainer);
        }
    });
});