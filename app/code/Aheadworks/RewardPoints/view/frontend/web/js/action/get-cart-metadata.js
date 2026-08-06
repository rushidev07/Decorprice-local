define(
    [
        'jquery',
        'Aheadworks_RewardPoints/js/model/resource-url-manager',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Aheadworks_RewardPoints/js/model/cart-metadata',
        'Magento_Customer/js/model/customer',
    ],
    function (
        $,
        urlBuilder,
        storage,
        errorProcessor,
        rewardPointsCartMetadata,
        customer
    ) {
        'use strict';

        var successCallbackList = [],
            failCallbackList = [],
            alwaysCallbackList = [],
            beforeActionCallbackList = [],
            action;

        /**
         * Fetch cart metadata
         *
         * @param messageContainer
         * @returns {Deferred}
         */
        action =  function (messageContainer) {
            if (customer.isLoggedIn()) {
                var serviceUrl = urlBuilder.getCartMetadataUrl();

                beforeActionCallbackList.forEach(function (callback) {
                    callback();
                });

                return storage.get(
                    serviceUrl, false
                ).done(
                    function (response) {

                        if (response) {
                            rewardPointsCartMetadata.setRewardPointsBalanceQty(
                                response.reward_points_balance_qty
                            );
                            rewardPointsCartMetadata.setCanApplyRewardPoints(
                                response.can_apply_reward_points
                            );
                            rewardPointsCartMetadata.setRewardPointsMaxAllowedQtyToApply(
                                response.reward_points_max_allowed_qty_to_apply
                            );
                            rewardPointsCartMetadata.setRewardPointsConversionRatePointToCurrencyValue(
                                response.reward_points_conversion_rate_point_to_currency_value
                            );
                            rewardPointsCartMetadata.setAreRewardPointsApplied(
                                response.are_reward_points_applied
                            );
                            rewardPointsCartMetadata.setAppliedRewardPointsQty(
                                response.applied_reward_points_qty
                            );
                            rewardPointsCartMetadata.setAppliedRewardPointsAmount(
                                response.applied_reward_points_amount
                            );
                        }

                        successCallbackList.forEach(function (callback) {
                            callback(response);
                        });
                    }
                ).fail(
                    function (response) {
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
            }

            return $.Deferred();
        };

        /**
         * Before attempt to fetch cart metadata
         *
         * @param {Function} callback
         */
        action.registerBeforeActionCallback = function (callback) {
            beforeActionCallbackList.push(callback);
        };

        /**
         * When successfully fetched cart metadata
         *
         * @param {Function} callback
         */
        action.registerSuccessCallback = function (callback) {
            successCallbackList.push(callback);
        };

        /**
         * When failed to fetch cart metadata
         *
         * @param {Function} callback
         */
        action.registerFailCallback = function (callback) {
            failCallbackList.push(callback);
        };

        /**
         * After every fetching of cart metadata
         *
         * @param {Function} callback
         */
        action.registerAlwaysCallback = function (callback) {
            alwaysCallbackList.push(callback);
        };

        return action;
    }
);
