/*global define,alert*/
define(
    [
        'jquery',
        'Aheadworks_RewardPoints/js/model/resource-url-manager',
        'Aheadworks_RewardPoints/js/model/payment/reward-points-messages',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/action/get-payment-information',
        'mage/storage',
        'mage/translate'
    ],
    function (
            $, 
            urlManager,
            messageContainer,
            totals,
            errorProcessor,
            getPaymentInformationAction, 
            storage, 
            $t
    ) {
        'use strict';

        var successCallbackList = [],
            failCallbackList = [],
            alwaysCallbackList = [],
            beforeActionCallbackList = [],
            action;

        action = function () {
            var url = urlManager.getRemoveRewardPointsUrl(),
                message = $t('Reward points were successfully removed.');
            
            messageContainer.clear();

            beforeActionCallbackList.forEach(function (callback) {
                callback();
            });

            return storage.delete(
                url,
                true
            ).done(
                function (response) {
                    var totalsDeferred = $.Deferred();

                    totals.isLoading(true);
                    getPaymentInformationAction(totalsDeferred);
                    $.when(totalsDeferred).done(function () {
                        totals.isLoading(false);
                    });
                    messageContainer.addSuccessMessage({
                        'message': message
                    });

                    successCallbackList.forEach(function (callback) {
                        callback(response);
                    });
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
         * Before attempt to remove points
         *
         * @param {Function} callback
         */
        action.registerBeforeActionCallback = function (callback) {
            beforeActionCallbackList.push(callback);
        };

        /**
         * When successfully removed points
         *
         * @param {Function} callback
         */
        action.registerSuccessCallback = function (callback) {
            successCallbackList.push(callback);
        };

        /**
         * When failed to remove points
         *
         * @param {Function} callback
         */
        action.registerFailCallback = function (callback) {
            failCallbackList.push(callback);
        };

        /**
         * After every points removed
         *
         * @param {Function} callback
         */
        action.registerAlwaysCallback = function (callback) {
            alwaysCallbackList.push(callback);
        };

        return action;
    }
);
