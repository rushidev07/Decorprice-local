define([
    'Magento_Ui/js/dynamic-rows/record'
], function (Record) {
    'use strict';

    return Record.extend({
        defaults: {
            disableForDefaultRecord: false
        },

        /**
         * @inheritdoc
         */
        initElement: function (elem) {
            this._super(elem);

            if (this.isCurrentRecordDefault()) {
                this.disableIfNeededElementForDefaultRecord(elem);
            }

            return this;
        },

        /**
         * Check if current record is default one
         *
         * @returns {boolean}
         */
        isCurrentRecordDefault: function() {
            return (this.recordId === 0);
        },

        /**
         * Disable if needed specific element of default record row
         *
         * @param {Object} elem
         */
        disableIfNeededElementForDefaultRecord: function(elem) {
            if (elem.disableForDefaultRecord === true) {
                elem.disable();
            }
        }
    });
});
