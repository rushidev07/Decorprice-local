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
    'Magento_Ui/js/grid/columns/select',
    'mage/translate',
    'mage/validation'
], function (Column, $t) {
    'use strict';

    return Column.extend({
        getLabel: function (record) {
            var label = this._super(record);

            switch (record.status){
                case 'approved':
                    label = $t('Approved');
                    break;
                case 'reject':
                    label = $t('Rejected by Admin');
                    break;
                case 'closed':
                    label = $t('Closed');
                    break;
                case 'customer_reject':
                    label = $t('Cancelled by Customer');
                    break;
                case 'pending':
                    label = $t('Pending');
                    break;
            }
            return label;
        }
    });
});