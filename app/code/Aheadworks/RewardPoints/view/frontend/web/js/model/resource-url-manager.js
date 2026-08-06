define(
    [
         'Magento_Checkout/js/model/resource-url-manager'
    ],
    function (urlManager) {
        'use strict';

        return {
            /**
             * Retrieve url to get current cart metadata
             *
             * @return {string}
             */
            getCartMetadataUrl: function  () {
                var urls = {
                    'customer': '/awRp/carts/mine/get-cart-metadata'
                };

                return urlManager.getUrl(urls, {});
            },

            /**
             * Retrieve url to apply specific qty of reward points
             * 
             * @return {string}
             */
            getApplyRewardPointsUrl: function (pointsQty) {
                var urlList = {
                    'customer': '/awRp/carts/mine/apply/:pointsQty'
                };
                return urlManager.getUrl(
                    urlList,
                    {
                        pointsQty: pointsQty
                    });
            },
            
            /**
             * Retrieve remove reward points url
             * 
             * @return {string}
             */
            getRemoveRewardPointsUrl: function  () {
                var urlList = {
                    'customer': '/awRp/carts/mine/remove'
                };
                return urlManager.getUrl(urlList, {});
            }
        };
    }
);