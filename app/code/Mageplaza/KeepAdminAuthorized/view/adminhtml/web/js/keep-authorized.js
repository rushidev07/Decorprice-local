/**
 * Copyright © Mageplaza. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */

define(['jquery'], function ($) {
    'use strict';

    $.widget('mageplaza.keepAuthorized', {

        options: {
            url: '',
            interval: 60000,
            maxErrorTry: 5
        },
        timer: null,
        errorTry: 0,

        _create: function () {
            this.timer = setInterval(this.updateSession.bind(this), this.options.interval);
        },

        updateSession: function () {
            var self = this;
            $.ajax({
                url: this.options.url,
                cache: false,
                data: {form_key: window.FORM_KEY},
                method: 'GET',
                success: function (data) {
                    if (data !== 'Ok') {
                        self.errorTry++;
                        console.log(data);
                        if (self.errorTry === self.options.maxErrorTry) {
                            clearInterval(self.timer);
                        }
                    } else {
                        self.errorTry = 0;
                    }
                }
            });
        }
    });

    return $.mageplaza.keepAuthorized;
});
