define([
    'jquery',
    'ko',
    'uiComponent',
    'Aheadworks_RewardPoints/js/action/get-cart-metadata',
    'Aheadworks_RewardPoints/js/action/apply-reward-points',
    'Aheadworks_RewardPoints/js/action/remove-reward-points',
    'Aheadworks_RewardPoints/js/model/cart-metadata',
    'Magento_Customer/js/model/customer',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/quote',
    'mage/translate',
    'Magento_Ui/js/lib/validation/utils',
    'jquery/validate',
    'validation'
], function (
    $,
    ko,
    Component,
    getCartMetadataAction,
    applyRewardPointsAction,
    removeRewardPointsAction,
    cartMetadata,
    customer,
    priceUtils,
    quote,
    $t,
    validationUtils
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Aheadworks_RewardPoints/payment/reward-points',
            formId: 'reward-points-form',
            rewardPointsQtyToApply: 0,
            isLoading: false,
            rewardPointsBalanceQty: 0,
            canApplyRewardPoints: false,
            rewardPointsMaxAllowedQtyToApply: false,
            rewardPointsConversionRatePointToCurrencyValue: 0,
            areRewardPointsApplied: false,
            appliedRewardPointsQty: 0,
            appliedRewardPointsAmount: 0,
            exports: {
                rewardPointsMaxAllowedQtyToApply: 'rewardPointsQtyToApply'
            }
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            this._super()
                .observe([
                    'rewardPointsQtyToApply',
                    'isLoading',
                    'rewardPointsBalanceQty',
                    'canApplyRewardPoints',
                    'rewardPointsMaxAllowedQtyToApply',
                    'rewardPointsConversionRatePointToCurrencyValue',
                    'areRewardPointsApplied',
                    'appliedRewardPointsQty',
                    'appliedRewardPointsAmount',
                    'isMetadataUpdated'
                ]);

            return this;
        },

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();

            this._addCallbackToGetCartMetadataAction();
            this._addCallbackToApplyRewardPointsAction();
            this._addCallbackToRemoveRewardPointsAction();

            return this;
        },

        /**
         * @inheritdoc
         */
        initConfig: function (config) {
            this._super(config);
            this._addValidationRules();
            return this;
        },

        /**
         * Add component-specific validation rules
         *
         * @private
         */
        _addValidationRules: function(){
            $.validator.addMethod(
                'aw-reward-points__validate-integer-greater-than-zero',
                function (value, element, params) {
                    if (validationUtils.isEmptyNoTrim(value)) {
                        return true;
                    }
                    var parsedValue = validationUtils.parseNumber(value);
                    return !isNaN(parsedValue) && /^\s*-?\d*\s*$/.test(value) && value > 0;
                },
                $.mage.__('Please enter a non-decimal number greater than 0 in this field.')
            );
        },

        /**
         * Add callbacks to the getCartMetadata action
         *
         * @returns {Component} Chainable.
         * @private
         */
        _addCallbackToGetCartMetadataAction: function() {
            var self = this;
            getCartMetadataAction.registerBeforeActionCallback(function () {
                self.showLoader();
            });
            getCartMetadataAction.registerFailCallback(function () {
                self.hideLoader();
            });
            getCartMetadataAction.registerAlwaysCallback(function () {
                self.hideLoader();
            });
            getCartMetadataAction.registerSuccessCallback(function () {
                self.updateMetadata();
            });
            return this;
        },

        /**
         * Add callbacks to the applyRewardPoints action
         *
         * @returns {Component} Chainable.
         * @private
         */
        _addCallbackToApplyRewardPointsAction: function() {
            var self = this;
            applyRewardPointsAction.registerBeforeActionCallback(function () {
                self.showLoader();
            });
            applyRewardPointsAction.registerFailCallback(function () {
                self.hideLoader();
            });
            return this;
        },

        /**
         * Add callbacks to the removeRewardPoints action
         *
         * @returns {Component} Chainable.
         * @private
         */
        _addCallbackToRemoveRewardPointsAction: function() {
            var self = this;
            removeRewardPointsAction.registerBeforeActionCallback(function () {
                self.showLoader();
            });
            removeRewardPointsAction.registerFailCallback(function () {
                self.hideLoader();
            });
            return this;
        },

        /**
         * Show block loader
         *
         * @returns {Component} Chainable.
         */
        showLoader: function () {
            this.isLoading(true);
            return this;
        },

        /**
         * Hide block loader
         *
         * @returns {Component} Chainable.
         */
        hideLoader: function () {
            this.isLoading(false);
            return this;
        },

        /**
         * Update reward points metadata
         *
         * @returns {Component} Chainable.
         */
        updateMetadata: function () {
            this.rewardPointsBalanceQty(cartMetadata.getRewardPointsBalanceQty());
            this.canApplyRewardPoints(cartMetadata.getCanApplyRewardPoints());
            this.rewardPointsMaxAllowedQtyToApply(cartMetadata.getRewardPointsMaxAllowedQtyToApply());
            this.rewardPointsConversionRatePointToCurrencyValue(cartMetadata.getRewardPointsConversionRatePointToCurrencyValue());
            this.areRewardPointsApplied(cartMetadata.getAreRewardPointsApplied());
            this.appliedRewardPointsQty(cartMetadata.getAppliedRewardPointsQty());
            this.appliedRewardPointsAmount(cartMetadata.getAppliedRewardPointsAmount());
            return this;
        },

        /**
         * Check if need to display current component
         *
         * @returns {boolean}
         */
        isNeedToDisplayComponent: function () {
            return this.canApplyRewardPoints() || this.areRewardPointsApplied();
        },

        /**
         * Check if customer is logged in
         *
         * @returns {boolean}
         */
        isCustomerLoggedIn: function () {
            return customer.isLoggedIn();
        },

        /**
         * Retrieve conversion rate text
         *
         * @return {string}
         */
        getConversionRateText: function() {
            return $t('1 point = ')
                + this.getFormattedPrice(
                    this.rewardPointsConversionRatePointToCurrencyValue()
                );
        },

        /**
         * Get price, formatted according to the current checkout price config
         *
         * @return {string}
         */
        getFormattedPrice: function(price) {
            return priceUtils.formatPrice(price, quote.getPriceFormat());
        },

        /**
         * Check if need to show max allowed qty to apply text
         *
         * @returns {boolean}
         */
        isNeedToShowMaxAllowedQtyToApplyText() {
            return (
                this.rewardPointsBalanceQty() > 0
                && this.rewardPointsBalanceQty() > this.rewardPointsMaxAllowedQtyToApply()
                && !this.areRewardPointsApplied()
            );
        },

        /**
         * Retrieve max allowed qty to apply text
         *
         * @returns {string}
         */
        getMaxAllowedQtyToApplyText: function () {
            if (this.rewardPointsMaxAllowedQtyToApply() > 0) {
                return $t('You can apply up to ') + this.rewardPointsMaxAllowedQtyToApply() + $t(' points');
            } else {
                return '';
            }
        },

        /**
         * Apply reward points
         *
         * @return {void}
         */
        applyRewardPoints: function() {
            if (this.validate()) {
                applyRewardPointsAction(this.rewardPointsQtyToApply());
            }
        },

        /**
         * Validate form
         *
         * @return {boolean}
         */
        validate: function() {
            var formSelector = '#' + this.formId;

            return $(formSelector).validation() && $(formSelector).validation('isValid');
        },

        /**
         * Retrieve text with applied reward points description
         *
         * @return {String}
         */
        getAppliedRewardPointsText: function() {
            if (this.areRewardPointsApplied()) {
                return $t('Used ') + this.appliedRewardPointsQty() + $t(' Reward Points')
                    + ' (' + this.getFormattedPrice(this.appliedRewardPointsAmount()) + ')';
            } else {
                return '';
            }
        },

        /**
         * Remove reward points
         *
         * @return {void}
         */
        removeRewardPoints: function() {
            if (this.areRewardPointsApplied()) {
                removeRewardPointsAction();
            }
        },
    });
});
