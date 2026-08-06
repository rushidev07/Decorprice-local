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
    'mageplaza/core/jquery/popup'
], function ($) {
    'use strict';

    $.widget('mageplaza.detailRequest', {
        /**
         * @inheritDoc
         */
        _create: function () {
            var btnDetail = $('.mpb-see-detail');

            btnDetail.each(function () {
                var el = $(this);

                el.on('click', function (e) {
                    var url = el.attr('data-url');

                    e.preventDefault();

                    $.ajax({
                        type: "POST",
                        url: url,
                        cache: false,
                        success: function (res) {
                            $('.mbp-detail-content').html(res.success);
                            $('#mpb_overlay').hide();
                        }
                    });
                });
            });

            $('.mpb-requests-history').magnificPopup({
                delegate: 'a.mpb-see-detail',
                midClick: true,
                callbacks: {
                    beforeOpen: function () {
                        $('#mpb_overlay').show();
                    }
                }
            });
        }
    });

    return $.mageplaza.detailRequest;
});

