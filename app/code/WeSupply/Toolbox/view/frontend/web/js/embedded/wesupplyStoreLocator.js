define([
    'jquery',
    'WeSupply_Toolbox/js/embedded/iframeLoader'
], function ($, iframeLoader) {
    'use strict';

    return {
        init: function(container)
        {
            this.waitUntilExists(function() { return $('.embedded-iframe').length; }, () => {
                var iframe = $('.embedded-iframe').first();
                iframe.parent().attr('id', container);

                iframeLoader.load(iframe.attr('src'), container);
            });
        },
        waitUntilExists: function(isReady, success, error, count, interval){
            if (count === undefined) {
                count = 300;
            }
            if (interval === undefined) {
                interval = 20;
            }
            if (isReady()) {
                success();
                return;
            }
            setTimeout(function(){
                if (!count) {
                    if (error !== undefined) {
                        error();
                    }
                } else {
                    this.waitUntilExists(isReady, success, error, count -1, interval);
                }
            }, interval);
        }
    }
});
