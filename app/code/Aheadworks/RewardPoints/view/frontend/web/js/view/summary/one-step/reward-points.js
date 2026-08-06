define(
    [
        'ko',
        'Aheadworks_RewardPoints/js/view/summary/reward-points',
        'Aheadworks_RewardPoints/js/action/remove-reward-points',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (
        ko,
        Component,
        removeRewardPointsAction,
        fullScreenLoader
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Aheadworks_RewardPoints/summary/one-step/reward-points'
            },

            /**
             * @inheritdoc
             */
            initialize: function () {
                this._super();

                this._addCallbackToRemoveRewardPointsAction();

                return this;
            },

            /**
             * Remove reward points
             */
            remove: function () {
                removeRewardPointsAction();
            },

            /**
             * Add callbacks to the removeRewardPoints action
             *
             * @returns {Component} Chainable.
             * @private
             */
            _addCallbackToRemoveRewardPointsAction: function() {
                removeRewardPointsAction.registerBeforeActionCallback(function () {
                    fullScreenLoader.startLoader();
                });
                removeRewardPointsAction.registerAlwaysCallback(function () {
                    fullScreenLoader.stopLoader();
                });
                return this;
            },
        });
    }
);
