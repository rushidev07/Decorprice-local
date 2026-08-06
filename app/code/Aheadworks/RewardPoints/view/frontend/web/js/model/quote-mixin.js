define([
    'mage/utils/wrapper',
    'Aheadworks_RewardPoints/js/action/add-to-queue',
    'Aheadworks_RewardPoints/js/action/get-cart-metadata'
], function (wrapper, addToQueueAction, getCartMetadataAction) {
    'use strict';

    return function (quote) {
        quote.setTotals = wrapper.wrapSuper(quote.setTotals, function (data) {
            this._super(data);
            addToQueueAction(getCartMetadataAction);
        });

        return quote;
    };
});
