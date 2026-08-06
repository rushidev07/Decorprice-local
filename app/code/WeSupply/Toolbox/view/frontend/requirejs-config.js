var config = {
    map: {
        '*': {
            wesupplyestimations: 'WeSupply_Toolbox/js/wesupplyestimations',
            wesupplyOrderView: 'WeSupply_Toolbox/js/embedded/wesupplyOrderView',
            wesupplyStoreLocator: 'WeSupply_Toolbox/js/embedded/wesupplyStoreLocator',
            iframeLoader: 'WeSupply_Toolbox/js/embedded/iframeLoader',
            deliveryEstimate: 'WeSupply_Toolbox/js/estimations/delivery'
        }
    },
    shim: {
        wesupplyestimations: {
            deps: ['jquery']
        },
        wesupplyOrderView: {
            deps: ['jquery']
        },
        wesupplyStoreLocator: {
            deps: ['jquery']
        },
        iframeLoader: {
            deps: ['jquery']
        },
        deliveryEstimate: {
            deps: ['jquery']
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/model/shipping-save-processor/payload-extender': {
                'WeSupply_Toolbox/js/estimations/model/shipping-save-processor/payload-extender': true
            }
        }
    }
};
