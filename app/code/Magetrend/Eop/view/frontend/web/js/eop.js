/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.3 or later
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */

define(['jquery'], function ($) {
        'use strict';

    var eop = function() {

        var isOpenFlag = false;

        var processingFlag = false;

        var lastTopPositionFlag = 0;

        var disabled = true;

        var config ={
            actionUrl: '',
            campaignId: 0,
            popupSelector: '#mteo_popup_bg_layer',
            popupContentSelector: '#mteo_popup_content',
            popupCloseSelector: '.mteo-close',
            popupShowCoupon: '.show_coupon',
            layerClose: false,
            cookieLifeTime: 3,
            showOnLoadCookieName: "eop_solcn",
            showInLast: true,
            showDevice: 'all',
            showEvent: 'all',
            delayTime: "0",
            mobileTrigger: 'both',
            autoPosition: true,
            emailFieldSelector: '#mteo_email',
            submitButtonSelector: '#mteo_submit',
            contactSubmitButtonSelector: '#mteo_contact_submit',
            errorMsgSelector: '#mteo_msg_error',
            successMsgSelector: '#mteo_subscription_success',
            successMsgWithCouponSelector: '#mteo_subscription_success_with_coupon',
            subscriptionFormSelector: '#mteo_subcription_form',
            couponSelector: '#mteo_coupon',
            couponCodeSelector: '#mteo_coupon span',
            pageUrl: '',
            translate: {},
            rest: '',
            pageId: '',
            validateConditions: false
        };

        var init = function(options) {
            $.extend(config, options );
            if (!canShowPopup()) {
                return;
            }

            initEvents();
            countTab(1);

            if (config.validateConditions) {
                validateConditions();
            } else {
                eop.disabled = false;
            }
        };

        var validateConditions = function () {
            $.ajax({
                url: config.rest,
                type: 'GET',
                dataType: 'json',
                success: function(responseData) {
                    if (responseData == true) {
                        eop.disabled = false;
                    }
                }
            });
        }

        var canShowPopup = function()
        {
            var isMobileDevice = isMobile();

            //disable, visible on desktop only
            if (isMobileDevice && config.showDevice == 'desktop') {
                return false;
            }

            //disable, visible on mobile only
            if (!isMobileDevice && config.showDevice == 'mobile') {
                return false;
            }

            return true;
        };

        var initEvents = function() {

            if (config.showEvent == 'exit_intent' || config.showEvent == 'all') {
                if (isMobile()) {
                    initMobileTrigger();
                }

                $(document).on('mouseleave', function (e){
                    if( e.clientY <= 6)  {
                        initPopup();
                    }
                });
            }

            if (config.showEvent == 'on_load' || config.showEvent == 'all') {
                setTimeout(function () {
                    if (!getCookie(config.showOnLoadCookieName)) {
                        initPopup();
                        setCookie(config.showOnLoadCookieName, true);
                        deleteCookie(config.cookieName);
                    }
                }, (config.delayTime * 1000));
            }

            if (config.layerClose) {
                $(config.popupSelector).click(function(e) {
                    if ('#'+e.target.id == config.popupSelector) {
                        popupClose();
                    }
                });
            }

            $(config.popupCloseSelector).click(function() {
                popupClose();
            });

            $(config.popupShowCoupon).click(function() {
                yesButtonAction();
            });

            $(window).unload(function(){
                countTab(-1);
            });
        };

        var initMobileTrigger = function () {

            var lastScrollTop = $(window).scrollTop();
            var disableMobileTrigger = false;
            var canShow = false;
            var canShowThanTop = $(document).height()*0.2;
            $(window).scroll(function() {
                if (disableMobileTrigger) {
                    return;
                }
                var scroll = $(window).scrollTop();
                if (!canShow && scroll > canShowThanTop) {
                    canShow = true;
                }
                var diff =  Math.abs(scroll) - Math.abs(lastScrollTop);
                var showPopup = false;
                if (canShow) {
                    if (config.mobileTrigger == 'both') {
                        if ((diff < -100 || (scroll < 50 && diff < 0))) {
                            showPopup = true;
                        }
                    } else if (config.mobileTrigger == 'top') {
                        if (scroll < 50 && diff < 0) {
                            showPopup = true;
                        }
                    } else if (config.mobileTrigger == 'scroll') {
                        if (diff < -100 ) {
                            showPopup = true;
                        }
                    }
                }

                if (showPopup) {
                    initPopup();
                    disableMobileTrigger = true;
                }

                lastScrollTop = scroll;
            });
        };

        var initPopup = function () {
            if (config.showInLast == true && getTabCounter() > 1) {
                return false;
            }
            if (eop.disabled) {
                return false;
            }

            if (!getCookie(config.cookieName)) {
                popupOpen();
                setCookie(config.cookieName, true);
            }

            $(config.emailFieldSelector).keypress(function (e) {
                if (e.which == 13) {
                    $(config.submitButtonSelector).trigger('click');
                    return false;
                }
            });

            $(config.submitButtonSelector).click(function () {
                if (!validateForm()) {
                    return false;
                }
                hideErrorMsg();
                processSubscription();
            });

            $(config.contactSubmitButtonSelector).click(function () {
                if (!validateForm()) {
                    return false;
                }
                hideErrorMsg();
                sendContactRequest();
            });

            $(config.popupCloseSelector).click(function(e){
                e.preventDefault();
                popupClose();
            });

            if (config.autoPosition) {
                $(document).scroll(function() {
                    eventScrollPopup();
                });

                $(window).resize(function() {
                    eventResizePopup();
                });
            }


        };

        var yesButtonAction = function()
        {
            $('.mteo_yes').html(config.translate.wait+'<span>'+config.translate.coupon_is_generating+'</span>');
            getCouponCode(function(couponCode){
                $('#mteo_subcription_form').hide();
                $('.mteo_buttons').hide();
                $('#mteo_coupon_code span').text(couponCode);
                $('#mteo_subscription_success_with_coupon').show();
            });
        };

        var getCouponCode = function(callback) {

            $.ajax({
                url: config.actionUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    id: config.campaignId
                },
                success: function(responseData) {
                    callback(responseData.couponCode);
                }
            });
        };


        var processSubscription = function() {
            if (!startProcess()) {
                return;
            }
            showLoading();
            var data = getFormData();

            $.ajax({
                url: config.actionUrl,
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function(responseData) {
                    if (responseData.errorMsg) {
                        showErrorMsg(responseData.errorMsg);
                    } else {
                        hideSubscriptionForm();
                        if (responseData.couponCode && $(config.couponSelector).length > 0) {
                            showSuccessMsg(responseData.successMsg, responseData.couponCode);
                        } else {
                            showSuccessMsg(responseData.successMsg);
                            setTimeout(function() {
                                popupClose();
                            }, 6000);
                        }
                    }
                    hideLoading();
                    endProcess();
                }
            });
        };

        var sendContactRequest = function()
        {
            if (!startProcess()) {
                return;
            }
            showContactLoading();
            var data = getFormData();
            data['page'] = config.pageUrl;

            $.ajax({
                url: config.actionUrl,
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function(responseData) {
                    if (responseData.errorMsg) {
                        showErrorMsg(responseData.errorMsg);
                    } else {
                        hideSubscriptionForm();
                        if (responseData.couponCode && $(config.couponSelector).length > 0) {
                            showSuccessMsg(responseData.successMsg, responseData.couponCode);
                        } else {
                            showSuccessMsg(responseData.successMsg);
                            setTimeout(function() {
                                popupClose();
                            }, 6000);
                        }
                    }
                    hideContactLoading();
                    endProcess();
                }
            });
        };

        var getFormData = function() {

            var data = {
                'campaign_id':  config.campaignId
            };

            if ($(config.emailFieldSelector).length > 0) {
                data['email'] = $(config.emailFieldSelector).val();
            }

            $('.mteo-additional-field').each(function() {
                var name = $(this).attr('name');
                if ($(this).attr('type') == 'radio') {
                    if ($(this).is(':checked')) {
                        data[name] = $(this).val();
                    }
                } else if ($(this).attr('type') == 'checkbox' && !$(this).is(':checked')) {
                    data[name] = 0;
                } else {
                    data[name] = $(this).val();
                }
            });

            return data;
        };


        var validateForm = function() {
            var error = 0;

            var fieldList = $('.mt-validator-required');

            for (var i = fieldList.length - 1; i >= 0; i--) {
                var field = $(fieldList[i]);
                if (field.val().length == 0 || field.data('field-name') == field.val()) {
                    showErrorMsg(translate('field')+ ' "'+field.data('field-name')+'" '+translate('is_required'));
                    error = 1;
                } else if (field.is(':checkbox') && field.is(':checked') == false) {
                    if (field.data('error-message')) {
                        var customErrorMessage = field.data('error-message');
                        if (customErrorMessage.is_required) {
                            showErrorMsg(customErrorMessage.is_required);
                        } else {
                            showErrorMsg(translate('field')+ ' "'+field.data('field-name')+'" '+translate('must_be_checked'));
                        }
                    } else {
                        showErrorMsg(translate('field')+ ' "'+field.data('field-name')+'" '+translate('must_be_checked'));
                    }
                    error = 1;
                } else if (field.is(':radio') ) {
                    if ($('#mteo_popup_content input[name='+field.attr('name')+']:checked').length == 0) {
                        showErrorMsg(translate('field')+ ' "'+field.data('field-name')+'" '+translate('must_be_selected'));
                        error = 1;
                    }
                }
            }

            if (error == 0) {
                $('.mt-validator-email').each(function() {
                    var field = $(this);
                    var tmp = field.val().split('@');
                    if (tmp.length < 2 || tmp[1].split('.').length < 2) {
                        showErrorMsg(translate('error_email_not_valid'));
                        error = 1;
                    }
                });
            }

            return error == 0;
        };

        var translate = function($key) {
            if (config.translate[$key]) {
                return config.translate[$key];
            }
            return $key;
        };

        var showSuccessMsg = function(msg, couponCode) {
            hideErrorMsg();
            if (couponCode) {
                $(config.successMsgWithCouponSelector+' #mteo_msg_success').html(msg);
                $(config.successMsgWithCouponSelector+' #mteo_coupon_code span').html(couponCode);
                $(config.successMsgWithCouponSelector).show();
            } else {
                $(config.successMsgSelector+' .mteo-success-msg').html(msg);
                $(config.successMsgSelector).show();
            }
        };

        var valingMiddle = function () {
            if (!$('.mteo-valing-middle')) {
                return;
            }

            $('.mteo-valing-middle').each(function () {
                var contentH = $(this).height();
                var boxH = $(config.popupContentSelector).height();
                $(this).css('margin-top', ((contentH-boxH)/2) + 'px');
            });
        };

        var hideSuccessMsg = function() {
            $(config.successMsgSelector).hide();
            $(config.successMsgWithCouponSelector).hide();
        };

        var showErrorMsg = function(msg) {
            hideSuccessMsg();
            $(config.errorMsgSelector).html(msg).css('display', 'inline-block');
        };

        var hideErrorMsg = function() {
            $(config.errorMsgSelector).hide();
        };

        var showCouponCode = function(code) {
            $(config.couponCodeSelector).text(code);
            $(config.couponSelector).show();
        };

        var startProcess = function() {
            if (processingFlag == true)
                return false;
            processingFlag = true;
            return true;
        };

        var endProcess = function() {
            processingFlag = false;
        };

        var showLoading = function() {
            $(config.submitButtonSelector).text(translate('wait'));
        };
        var showContactLoading = function() {
            $(config.contactSubmitButtonSelector).text(translate('wait'));
        };

        var hideContactLoading = function() {
            $(config.contactSubmitButtonSelector).text(translate('send'));
        };

        var hideLoading = function() {
            $(config.submitButtonSelector).text(translate('subscribe'));
        };

        var hideSubscriptionForm = function() {
            $(config.subscriptionFormSelector).hide();
        };

        var getTabCounter = function() {
            var openedTab = getCookie('tabCounter');
            if (!openedTab) {
                return 0;
            }
            return parseInt(openedTab);
        };

        var countTab = function(val) {
            if (config.showInLast) {
                var openedTab = getCookie('tabCounter');
                if (!openedTab || openedTab < 0) {
                    openedTab = 0;
                } else {
                    openedTab = parseInt(openedTab);
                }
                setCookie('tabCounter', (openedTab+val), true);
            }
        };

        var popupOpen = function () {
            if (!isOpenFlag) {
                $(config.popupSelector).css('height', $(document).height()+'px').show();
                $(config.popupContentSelector).css('margin-top', getTopPositionPopup()+'px');
                isOpenFlag = true;
            }
        };

        var popupClose = function () {
            if (isOpenFlag) {
                $(config.popupSelector).fadeOut();
                isOpenFlag = false;
            }
        };

        var getTopPositionPopup = function() {
            var popupBox = $(config.popupContentSelector);
            var scrollTop = jQuery(document).scrollTop();
            var windowH = jQuery(window).height();
            var boxH = popupBox.height();
            var boxTop = 0;
            if (windowH <= boxH) {
                boxTop = scrollTop;
            } else {
                boxTop = scrollTop + ((windowH - boxH ) /2);
            }
            return boxTop;
        };

        var eventScrollPopup = function()
        {
            var popupBox = $(config.popupContentSelector);
            var windowH = $(window).height();
            var boxH = popupBox.height();
            var scrollTop = $(document).scrollTop();
            var diff = Math.abs(lastTopPositionFlag - scrollTop);
            if (windowH <= boxH) {
                return;
            }

            if (diff > 20
                || scrollTop == 0
                || scrollTop + $(window).height() == $(document).height()
            ) {
                lastTopPositionFlag = scrollTop;
                popupBox.css('margin-top', getTopPositionPopup()+'px');
            }
        };

        var eventResizePopup = function() {
            var popupBox = $(config.popupContentSelector);
            var windowH = $(window).height();
            var boxH = popupBox.height();
            if (windowH <= boxH) {
                return;
            }
            popupBox.css('margin-top', getTopPositionPopup()+'px');
        };

        var setCookie = function(key, value, session) {

            var now = new Date();
            var time = now.getTime();
            time += 3600 * 1000 *24 * config.cookieLifeTime;
            now.setTime(time);
            if (session && session == true) {
                var expires = "; expires="+0;
            } else {
                var expires = "; expires="+now.toUTCString();
            }
            document.cookie = escape(key)+"="+escape(value)+expires+"; path=/";
        };

        var getCookie = function(key) {
            var nameEQ = escape(key) + "=";
            var ca = document.cookie.split(';');
            for(var i=0;i < ca.length;i++) {
                var c = ca[i];
                while (c.charAt(0)==' ') c = c.substring(1,c.length);
                if (c.indexOf(nameEQ) == 0) return unescape(c.substring(nameEQ.length,c.length));
            }
            return null;
        };

        var deleteCookie = function(key) {
            var value = "";
            var now = new Date();
            var time = now.getTime();
            time += 3600 * 1000 * 24 * -1;
            now.setTime(time);
            var expires = "; expires="+now.toUTCString();
            document.cookie = escape(key)+"="+escape(value)+expires+"; path=/";
        };

        var isMobile = function() {
            if( navigator.userAgent.match(/Android/i)
                || navigator.userAgent.match(/webOS/i)
                || navigator.userAgent.match(/iPhone/i)
                || navigator.userAgent.match(/iPad/i)
                || navigator.userAgent.match(/iPod/i)
                || navigator.userAgent.match(/BlackBerry/i)
                || navigator.userAgent.match(/Windows Phone/i)
            ){
                return true;
            }
            else {
                return false;
            }
        };

        return {
            init: init,
            disabled: disabled
        }
    }();

    $.widget('mage.eop', {
        options: {},
        _create: function() {
            eop.init(this.options);
        }
    });

    return $.mage.eop;
});
