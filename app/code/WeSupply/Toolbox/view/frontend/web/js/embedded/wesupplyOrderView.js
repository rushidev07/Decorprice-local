define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'WeSupply_Toolbox/js/embedded/iframeLoader'
], function ($, modal, iframeLoader) {
    'use strict';

    var options = {
        type: 'slide',
        responsive: true,
        innerScroll: false,
        modalClass: 'order-view-modal',
        buttons: [{
            text: $.mage.__('Close'),
            class: 'close-order-view',
            click: function () {
                this.closeModal();
            }
        }]
    };

    return {
        init: function(viewContainerId, platform)
        {
            var viewContainer = $('#' + viewContainerId);
            var orderView = modal(options, viewContainer);

            $('.action.view.iframe-view').on('click', function()
            {
                var iframeUrl = $(this).data('url');
                iframeLoader.load(iframeUrl, viewContainerId, platform);

                viewContainer.trigger('processStart');
                viewContainer.on('iframeLoaded', function() {
                    viewContainer.trigger('processStop')
                        .height($('.order-view-modal').height());
                    orderView.openModal();
                });
            });
        }
    }
});
