define([
    'Magento_Ui/js/grid/paging/paging',
], function (Paging) {
    'use strict';

    return Paging.extend({
        defaults: {
            totalTmpl: 'Aheadworks_RewardPoints/ui/grid/paging-total',
            noMoreSelection: false,
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            this._super().track('noMoreSelection');
            return this;
        },

        /**
         * Get total selected class
         *
         * @returns {string}
         */
        getTotalSelectedClass: function() {
            return this.noMoreSelection ? 'no-more-selection' : '';
        },
    });
});
