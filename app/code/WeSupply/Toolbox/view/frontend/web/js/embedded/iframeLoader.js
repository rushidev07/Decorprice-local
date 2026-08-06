define([
    'jquery',
    'WeSupply_Toolbox/js/lib/iframeResizer.min'
], function ($, iframeResizer) {
    'use strict';

    return {
        load: function(iframeUrl, containerId, platform)
        {
            var loadingContainer = $('.loading-container');
            var viewContainer = $('#' + containerId);

            if (typeof iframeUrl === 'undefined' || iframeUrl === '') {
                loadingContainer.hide();
                return;
            }

            var iframeId = containerId + '-iframe',
                resizeTo = 0,
                resized = false,
                headerHeight = $('header').outerHeight(),
                windowHeight = $(window).innerHeight(),
                availableHeight = parseInt(windowHeight) - parseInt(headerHeight) - 120,
                isOldIE = (navigator.userAgent.indexOf("MSIE") !== -1);

            viewContainer.trigger('processStart').hide();

            var existingIframe = viewContainer.find('#' + iframeId);

            if (existingIframe.length === 0) {
                viewContainer.html(this.createIframe(iframeUrl, iframeId, platform));
            }

            $('#' + iframeId).on('load', function(){
                $(this).css('min-height', availableHeight + 'px');
                viewContainer.css('min-height', availableHeight + 'px');

                iframeResizer({
                    log: false,
                    minHeight: availableHeight,
                    resizeFrom: 'parent',
                    scrolling: true,
                    inPageLinks: true,
                    autoResize: true,
                    heightCalculationMethod: isOldIE ? 'max' : 'documentElementScroll',
                    onInit: function(iframe) {
                        resizeTo = availableHeight;
                        iframe.style.height = availableHeight + 'px';
                        viewContainer.trigger('iframeLoaded');
                    },
                    onResized: function(messageData) {
                        setTimeout(function() {
                            if (resizeTo && messageData.type === 'resetPage') {
                                resized = true;
                            }
                        }, 300);
                    },
                    onMessage: function(messageData) {
                        if (messageData.message === 'resize') {
                            resizeTo = parseInt(messageData.iframe.offsetHeight);
                            messageData.iframe.style.height = resizeTo + 'px';

                            if (history.pushState && messageData.message.hasOwnProperty('trackNo')) {
                                var newUrl = window.location.protocol
                                    + '//' + window.location.host
                                    + window.location.pathname.replace(/\/$/, '')
                                    + '/' + messageData.message.trackNo;

                                window.history.pushState({ path:newUrl }, '', newUrl);
                            }

                            if (messageData.message === 'stop' || messageData.message === 'pageInfoStop') {
                                resizeTo = 0;
                            }
                        }
                    }
                }, '.embedded-iframe');

                loadingContainer.hide();
                viewContainer.trigger('processStop').show();

                setTimeout(function() {
                    if (!resized) { // Fallback height in case the resizer didn't work
                        $(this).css({'height': '1000px', 'visibility': 'visible'});
                    }
                }, 600);
            });
        },
        createIframe: function (iframeUrl, iframeId, platform) {
            if (platform && platform !== '') {
                var paramConcat = iframeUrl.indexOf('?') === -1 ? '?' : '&';
                iframeUrl = iframeUrl + paramConcat + 'platformType=' + platform;
            }

            return $('<iframe></iframe>', {
                id: iframeId,
                class: 'embedded-iframe',
                src: iframeUrl,
                width: '100%',
                allowfullscreen: true,
                frameborder: 0,
                allow: 'geolocation',
                scrolling: 'no'
            });
        }
    }
});
