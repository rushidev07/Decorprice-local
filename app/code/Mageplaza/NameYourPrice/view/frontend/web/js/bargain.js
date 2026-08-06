/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_NameYourPrice
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'underscore',
    'mage/translate',
    'Magento_Customer/js/customer-data',
    'validation',
    'Mageplaza_Core/js/jquery.magnific-popup.min'
], function ($, _, $t, customerData) {
    'use strict';

    $.widget('mageplaza.pricebargain', {
        /**
         * @inheritDoc
         */
        _create: function () {
            var self = this;

            customerData.reload(['bargainData'], false);

            var getBargainData = setInterval(function () {
                if (customerData.get('bargainData')()) {
                    var bargainData = JSON.parse(customerData.get('bargainData')().bargainData);

                    if (bargainData) {
                        self.bargainButtonListener();
                        self.actionBargain();

                        clearInterval(getBargainData);
                    }
                }
            }, 500);
        },

        actionBargain: function () {
            if (this.options.productType === 'configurable') {
                this.showYourBargain();
                this.setOption();
            } else if (this.options.productType === 'bundle') {
                this.showBargain(this.options.productId);
            } else {
                this.showBargain(this.options.productId);
                this.createInputRequest(this.options.requestId);
            }

            /** check if product is bargained and approved with bundle product*/
            if (this.options.bundleData && !this.isLogin()) {
                var self             = this,
                    serializeBargain = JSON.parse(this.options.bundleData).serializeData;

                this.setQtyBundleProduct();
                this.updateForm(null, null, serializeBargain);

                $('.bundle-options-wrapper').on('change', function () {
                    self.updateForm(null, null, serializeBargain);
                });
            }

        },

        /**
         * event when click Bargain price button
         */
        bargainButtonListener: function () {
            var self            = this,
                popup           = $('#mpb_popup'),
                btnBargain      = $('#mpb_bargain_button'),
                btnBargainClass = $('.mpb-bargain-button');

            btnBargainClass.on('click', function (e) {
                e.preventDefault();

                var productCustomOptions = $('div#product-options-wrapper').length;

                /**
                 * set input selected option bundle product
                 */

                if(self.options.productType !== 'bundle'){
                    if(productCustomOptions){
                        self.setProductOriginalPrice();
                    }
                }

                if (self.options.productType === 'bundle') {
                    self.setInputSelectedBundle();
                    self.setSerializeBundleData();
                    self.setInputOriginalPrice();
                }


                if (self.options.productType === 'configurable') {
                    /** validate form */
                    var dataForm = $('form#product_addtocart_form'),
                        validate = dataForm.validation('isValid');

                    if (!validate) {
                        return;
                    }
                    /** set input selected option configurable product */
                    self.setInputSelectedConfigurable();
                }

                /** show popup */
                if (self.options.isPopup) {
                    btnBargain.magnificPopup({
                        delegate: 'a.mpb-bargain-button',
                        removalDelay: 0,
                        midClick: true
                    });
                } else {
                    popup.attr('style', 'display: block !important');
                }
            });
        },

        /**
         * set input selected option bundle product
         */
        setInputSelectedConfigurable: function () {
            var optionSelectedId = [],
                optionSelectedEl = $('.swatch-option.selected'),
                inputEl          = $('#mp_selected_configurable');

            optionSelectedEl.each(function () {
                optionSelectedId.push($(this).attr('option-id'));
            });

            inputEl.val(JSON.stringify(optionSelectedId));
        },

        /**
         * show your bargain when select options (configurable product)
         */
        showYourBargain: function () {
            var self                = this,
                productIdsBargained = [],
                btnBargain          = $('#mpb_bargain_button'),
                bargainInfo         = $('.mpb-bargain-info'),
                productIdInput      = $('#mp_product_id'),
                yourBargainContent  = $('#mpb_your_bargain'),
                swatchOptions       = $('.product-options-wrapper .swatch-opt');

            btnBargain.show();
            swatchOptions.on('click', function () {
                var productId = self.getProductIdFromOptions(),
                    price     = parseFloat($('.product-info-main .price-wrapper').attr('data-price-amount')),
                    minPrice  = self.getMinPrice(price);

                /** set range **/
                $('#mp_bargain_price')
                .removeClass('number-range-0-')
                .addClass('number-range-' + minPrice + '-');

                /** set value input product id */
                productIdInput.val(productId);

                /** show bargain info if customer logged */
                if (self.isLogin()) {
                    productIdsBargained = self.getProductIdsBargain();
                } else {
                    productIdsBargained = self.options.productIdsBargained;
                }

                if ($.inArray(productId, productIdsBargained) !== -1) {
                    var cartForm     = $('#product_addtocart_form'),
                        inputChildId = '<input type="hidden" name="mpb-child-product" value="' + productId + '" id="mpb-child-product">';

                    if (!self.isLogin()) {
                        $.ajax({
                            url: self.options.yourBargainUrl,
                            dataType: 'json',
                            cache: false,
                            data: {'child_id': productId, 'request_id': self.options.requestId},
                            showLoader: true,
                            success: function (result) {
                                btnBargain.hide();
                                self.createInputRequest(self.options.requestId);
                                yourBargainContent.html(result.success);
                                yourBargainContent.trigger('contentUpdated');
                                cartForm.prepend(inputChildId);
                            }
                        });
                    } else {
                        self.showBargain(productId);
                    }

                    self.createInputRequest(self.options.requestId);
                    cartForm.prepend(inputChildId);
                } else {
                    $('#mpb-child-product').remove();
                    $('#mpb-request-id').remove();
                    btnBargain.show();
                    bargainInfo.hide();
                    yourBargainContent.empty();
                }
            });
        },

        /**
         * get All product ids has been bargained
         * @returns {Array}
         */
        getProductIdsBargain: function () {
            if (!this.isLogin()) {
                return [];
            }

            return JSON.parse(customerData.get('bargainData')().productIds);
        },

        /**
         * process to show button or detail your bargain
         * @param productId
         */
        showBargain: function (productId) {
            var self       = this,
                btnBargain = $('#mpb_bargain_button');

            if (!self.isLogin()) {
                btnBargain.show();
                return this;
            }

            var bargainData         = JSON.parse(customerData.get('bargainData')().bargainData),
                productIdsBargained = self.getProductIdsBargain();

            if ($.inArray(productId, productIdsBargained) !== -1) {
                self.updateHtml(productId, bargainData);

                /** process bundle product when approved */
                if (self.options.productType === 'bundle' && bargainData[productId]['status'] === 'approved') {
                    self.updateHtmlBundleProduct(productId, bargainData);
                    this.setQtyBundleProduct();
                }
            } else {
                btnBargain.show();
            }
        },

        /**
         * update html on product view page with simple & configurable product
         * @param productId
         * @param bargainData
         */
        updateHtml: function (productId, bargainData) {
            var urlCancel     = this.options.urlCancel,
                bargainInfo   = $('.mpb-bargain-info'),
                bargainPrice  = $('.mpb-bargain-price'),
                bargainQty    = $('.mpb-bargain-qty'),
                bargainCancel = $('.mpb-cancel-bargain'),
                btnBargain    = $('#mpb_bargain_button'),
                notePending   = $('.mpb-bargain-note-pending'),
                noteApproved  = $('.mpb-bargain-note-approve');

            bargainInfo.show();
            bargainPrice.html($t('Your Bargain Price: ') +
                bargainData[productId]['bargain_price']);
            bargainQty.html($t('Your Committed Min. Qty: ') +
                bargainData[productId]['qty']);

            if (bargainData[productId]['status'] === 'pending') {
                notePending.show();
                noteApproved.hide();
            } else {
                notePending.hide();
                noteApproved.show();
            }

            bargainCancel.attr('href', urlCancel + 'request_id/' + bargainData[productId]['request_id']);
            btnBargain.hide();

            bargainCancel.on('click', function () {
                customerData.set('bargainData', []);
            })
        },

        /**
         * update html with bundle product
         * @param productId
         * @param bargainData
         */
        updateHtmlBundleProduct: function (productId, bargainData) {
            var self = this;

            this.updateForm(productId, bargainData);

            $('.bundle-options-wrapper').on('change', function () {
                self.updateForm(productId, bargainData);
            });
        },

        /**
         * set serialize bundle data
         */
        setSerializeBundleData: function () {
            var serializeData = $('#product_addtocart_form')
            .find('select, input[name!=qty]')
            .not('input[name=form_key]')
            .serialize();

            $('#mp_serialize_bundle').val(serializeData);
        },

        /**
         * set original price for bundle product
         */
        setInputOriginalPrice: function () {
            var priceEl            = $('.price-box.price-configured_price .price-wrapper'),
                originPrice        = priceEl.find('.price').text().replace(/[^0-9 .]/gi, ''),
                originalPriceInput = $('#mp_original_price');

            originalPriceInput.val(originPrice);
        },

        setProductOriginalPrice: function () {
            var priceEl            = $('.product-info-price .price-wrapper[data-price-type="finalPrice"]'),
                originPrice        = priceEl.find('.price').text().replace(/[^0-9 .]/gi, ''),
                originalPriceInput = $('#mp_original_price');

            originalPriceInput.val(originPrice);
        },

        /**
         * Set qty for options bundle product
         */
        setQtyBundleProduct: function () {
            var self = this,
                data = $.parseJSON(self.options.bundleData).qty;

            $.each(data, function (el, qty) {
                $('#' + el).val(qty).trigger('change');
            });
        },

        /**
         * Update bargain price with bundle product when select correct options and qty
         * @param productId
         * @param bargainData
         * @param serializeBargain
         */
        updateForm: function (productId = null, bargainData = null, serializeBargain = null) {
            var form          = $('#product_addtocart_form'),
                bargainPrice  = 0,
                serializeForm = form.find('select, input[name!=qty]').not('input[name=form_key]').serialize();

            if (serializeBargain) {
                bargainPrice = JSON.parse(this.options.bundleData).price;
            } else {
                bargainPrice     = bargainData[productId]['bargain_price'];
                serializeBargain = bargainData[productId].serializeData;
            }

            /** set html price when select options correct **/
            if (serializeForm === serializeBargain) {
                var price      = bargainPrice,
                    priceEl    = $('.price-box.price-configured_price .price-wrapper'),
                    verifyVal  = this.options.requestId ? this.options.requestId : 'ok',
                    verifyHtml = '<input type="hidden" ' +
                        'name="mpb-verify-options" ' +
                        'id="mpb-verify-options" ' +
                        'value="' + verifyVal + '">';

                /** set price html */
                priceEl.html(price);
                form.prepend(verifyHtml);
            } else {
                /** remove input verify options */
                $('#mpb-verify-options').remove();
            }
        },

        /**
         * set input selected option bundle product
         */
        setInputSelectedBundle: function () {
            var qty          = {},
                inputEl      = $('#mp_selected_bundle'),
                selectedIds  = [],
                singleOption = $('.bundle-options-wrapper .radio, .bundle-options-wrapper input.checkbox'),
                singleSelect = $('.bundle-options-wrapper .bundle-option-select'),
                singleMulti  = $('.bundle-options-wrapper .multiselect option'),
                singleQty    = $('.bundle-options-wrapper .input-text.qty');

            /** bundle type = checkbox, radio */
            $.each(singleOption, function () {
                var el = $(this);

                if (el.is(':checked')) {
                    selectedIds.push(el.val());
                }
            });

            $.each(singleQty, function () {
                var el    = $(this),
                    qtyEl = el.attr('id');

                qty[qtyEl] = el.val();
            });

            /** bundle type = select */
            $.each(singleSelect, function () {
                var el = $(this);

                selectedIds.push(el.find(":selected").val());
            });

            /** bundle type = multiselect */
            $.each(singleMulti, function () {
                var el = $(this);

                if (el.is(':selected')) {
                    selectedIds.push(el.val());
                }
            });

            var selected = {selectedIds: selectedIds, qty: qty};

            inputEl.val(JSON.stringify(selected));
        },

        /**
         * get product id after select options
         * @returns {number}
         */
        getProductIdFromOptions: function () {
            var prodId           = 0,
                selected_options = {};

            $('div.swatch-attribute').each(function (k, attr) {
                var attribute_id    = $(attr).attr('attribute-id'),
                    option_selected = $(attr).attr('option-selected');

                if (!attribute_id || !option_selected) {
                    return;
                }
                selected_options[attribute_id] = option_selected;
            });

            var product_id_index = $('[data-role=swatch-options]').data('mageSwatchRenderer').options.jsonConfig.index;

            $.each(product_id_index, function (product_id, attributes) {
                if (_.isEqual(attributes, selected_options)) {
                    prodId = product_id;
                }
            });

            return prodId;
        },

        /**
         * Set default option configurable product
         */
        setOption: function () {
            var self = this;

            if (self.options.isSetOptions) {
                var swatchOpsEls = JSON.parse(self.options.swatchOpsEls);

                self.options.isLogin = true;

                $('[data-gallery-role=gallery-placeholder]').on('gallery:loaded', function () {
                    $.each(swatchOpsEls, function (key, value) {
                        $('#product-options-wrapper').find('[option-id=' + value + ']').trigger('click');
                    });

                    self.createInputRequest(self.options.requestId);
                    self.options.isSetOptions = false;
                });
            }
        },

        /** create input to send request_id **/
        createInputRequest: function (requestId) {
            var cartForm         = $('#product_addtocart_form'),
                inputCheckSetOps = '<input type="hidden" id="mpb-request-id" name="mpb-request-id" value="' + requestId + '">';

            cartForm.prepend(inputCheckSetOps);
        },

        /**
         * check login
         * @returns boolean
         */
        isLogin: function () {
            return JSON.parse(customerData.get('bargainData')().isLogin);
        },

        /**
         * get Min Price config
         * @param price
         * @returns {*}
         */
        getMinPrice: function (price) {
            var type  = this.options.minPriceType,
                value = this.options.minPriceValue;

            if (type === 'fixed') {
                return value;
            }

            return price * value / 100;
        }
    });

    return $.mageplaza.pricebargain;
});

