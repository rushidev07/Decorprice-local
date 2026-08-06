define([
    'Magento_Ui/js/form/provider',
], function (Provider) {
    'use strict';

    return Provider.extend({
        /**
         * @inheritdoc
         */
        save: function (options) {
            var data = this.get('data');

            data.customer_selections = JSON.stringify(data.customer_selections);

            this.client.save(data, options);

            return this;
        },
    });
});
