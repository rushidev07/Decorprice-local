define([
    'uiComponent'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            visible: true,
            template: 'Aheadworks_RewardPoints/ui/group/group',
            fieldTemplate: 'ui/form/field',
        },

        /**
         * Init observable.
         *
         * @return {Object}
         */
        initObservable: function () {
            this._super()
                .observe('visible');

            return this;
        },

        /**
         * Called when another element was added to current component.
         *
         * @param {Object} elem - Instance of an element that was added.
         * @returns {Collection} Chainable.
         */
        initElement: function (elem) {
            elem.initContainer(this);

            if (!this.visible()) {
                elem.hide();
            }

            return this;
        },

        /**
         * Show elements.
         *
         * @returns {Object}
         */
        show: function () {
            this.visible(true);
            this.elems.each(function (elem){
                elem.show();
            });

            return this;
        },

        /**
         * Hide elements.
         *
         * @returns {Object}
         */
        hide: function () {
            this.visible(false);
            this.elems.each(function (elem){
                elem.hide();
            });

            return this;
        },
    })
});
