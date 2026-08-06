/*global define,alert*/
define(
    [
        'ko',
        'jquery',
        'Aheadworks_RewardPoints/js/model/resource-url-manager',
        'Aheadworks_RewardPoints/js/model/payment/reward-points-messages',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/action/get-payment-information',
        'mage/storage'
    ],
    function (
        ko,
        $,
        urlManager,
        messageContainer,
        totals,
        errorProcessor,
        getPaymentInformationAction,
        storage
    ) {
        'use strict';

        var successCallbackList = [],
            failCallbackList = [],
            alwaysCallbackList = [],
            beforeActionCallbackList = [],
            action;

        /**
         * Apply specific qty of reward points to the current quote
         *
         * @param qty
         * @returns {*}
         */
        action = function (qty) {
            var url = urlManager.getApplyRewardPointsUrl(qty);

            beforeActionCallbackList.forEach(function (callback) {
                callback();
            });
            
            return storage.put(
                url,
                {},
                true
            ).done(
                function (response) {
                    if (response[0] != 'undefined' && response[0].success) {
                        var totalsDeferred = $.Deferred();

                        totals.isLoading(true);
                        getPaymentInformationAction(totalsDeferred);
                        $.when(totalsDeferred).done(function () {
                            totals.isLoading(false);
                        });
                        messageContainer.addSuccessMessage({'message': response[0].message});

                        successCallbackList.forEach(function (callback) {
                            callback(response);
                        });
                    }
                }
            ).fail(
                function (response) {
                    totals.isLoading(false);
                    errorProcessor.process(response, messageContainer);

                    failCallbackList.forEach(function (callback) {
                        callback(response);
                    });
                }
            ).always(
                function (response) {
                    alwaysCallbackList.forEach(function (callback) {
                        callback(response);
                    });
                }
            );
        };

        /**
         * Before attempt to apply points
         *
         * @param {Function} callback
         */
        action.registerBeforeActionCallback = function (callback) {
            beforeActionCallbackList.push(callback);
        };

        /**
         * When successfully applied points
         *
         * @param {Function} callback
         */
        action.registerSuccessCallback = function (callback) {
            successCallbackList.push(callback);
        };

        /**
         * When failed to apply points
         *
         * @param {Function} callback
         */
        action.registerFailCallback = function (callback) {
            failCallbackList.push(callback);
        };

        /**
         * After every points applying
         *
         * @param {Function} callback
         */
        action.registerAlwaysCallback = function (callback) {
            alwaysCallbackList.push(callback);
        };

        return action;
    }
);
