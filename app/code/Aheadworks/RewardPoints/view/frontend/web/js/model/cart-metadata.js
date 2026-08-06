define([
    'ko',
    'domReady!'
], function (ko) {
    'use strict';

    var rewardPointsBalanceQty = ko.observable(0),
        canApplyRewardPoints = ko.observable(false),
        rewardPointsMaxAllowedQtyToApply = ko.observable(0),
        rewardPointsConversionRatePointToCurrencyValue = ko.observable(0),
        areRewardPointsApplied = ko.observable(false),
        appliedRewardPointsQty = ko.observable(0),
        appliedRewardPointsAmount = ko.observable(0)
    ;

    return {
        rewardPointsBalanceQty: rewardPointsBalanceQty,
        canApplyRewardPoints: canApplyRewardPoints,
        rewardPointsMaxAllowedQtyToApply: rewardPointsMaxAllowedQtyToApply,
        rewardPointsConversionRatePointToCurrencyValue: rewardPointsConversionRatePointToCurrencyValue,
        areRewardPointsApplied: areRewardPointsApplied,
        appliedRewardPointsQty: appliedRewardPointsQty,
        appliedRewardPointsAmount: appliedRewardPointsAmount,

        /**
         * @return {*}
         */
        getRewardPointsBalanceQty: function () {
            return rewardPointsBalanceQty();
        },

        /**
         * @param {*} rewardPointsBalanceQtyValue
         */
        setRewardPointsBalanceQty: function (rewardPointsBalanceQtyValue) {
            rewardPointsBalanceQty(rewardPointsBalanceQtyValue);
        },

        /**
         * @return {Boolean}
         */
        getCanApplyRewardPoints: function () {
            return canApplyRewardPoints();
        },

        /**
         * @param {Boolean} canApplyRewardPointsFlag
         */
        setCanApplyRewardPoints: function (canApplyRewardPointsFlag) {
            canApplyRewardPoints(canApplyRewardPointsFlag);
        },

        /**
         * @return {*}
         */
        getRewardPointsMaxAllowedQtyToApply: function () {
            return rewardPointsMaxAllowedQtyToApply();
        },

        /**
         * @param {*} rewardPointsMaxAllowedQtyToApplyValue
         */
        setRewardPointsMaxAllowedQtyToApply: function (rewardPointsMaxAllowedQtyToApplyValue) {
            rewardPointsMaxAllowedQtyToApply(rewardPointsMaxAllowedQtyToApplyValue);
        },

        /**
         * @return {*}
         */
        getRewardPointsConversionRatePointToCurrencyValue: function () {
            return rewardPointsConversionRatePointToCurrencyValue();
        },

        /**
         * @param {*} rewardPointsConversionRatePointToCurrencyNewValue
         */
        setRewardPointsConversionRatePointToCurrencyValue: function (rewardPointsConversionRatePointToCurrencyNewValue) {
            rewardPointsConversionRatePointToCurrencyValue(rewardPointsConversionRatePointToCurrencyNewValue);
        },

        /**
         * @return {Boolean}
         */
        getAreRewardPointsApplied: function () {
            return areRewardPointsApplied();
        },

        /**
         * @param {Boolean} areRewardPointsAppliedFlag
         */
        setAreRewardPointsApplied: function (areRewardPointsAppliedFlag) {
            areRewardPointsApplied(areRewardPointsAppliedFlag);
        },

        /**
         * @return {*}
         */
        getAppliedRewardPointsQty: function () {
            return appliedRewardPointsQty();
        },

        /**
         * @param {*} appliedRewardPointsQtyValue
         */
        setAppliedRewardPointsQty: function (appliedRewardPointsQtyValue) {
            appliedRewardPointsQty(appliedRewardPointsQtyValue);
        },

        /**
         * @return {*}
         */
        getAppliedRewardPointsAmount: function () {
            return appliedRewardPointsAmount();
        },

        /**
         * @param {*} appliedRewardPointsAmountValue
         */
        setAppliedRewardPointsAmount: function (appliedRewardPointsAmountValue) {
            appliedRewardPointsAmount(appliedRewardPointsAmountValue);
        }
    };
});