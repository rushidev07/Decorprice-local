<?php
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

namespace Mageplaza\NameYourPrice\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Mageplaza\NameYourPrice\Helper\Data as HelperData;

/**
 * Class Requests
 * @package Mageplaza\NameYourPrice\Model\ResourceModel
 */
class Requests extends AbstractDb
{
    const SHOW_ON_PRODUCT_PAGE = 'show';
    const SHOW_AND_APPLY_PRICE = 'show_and_apply';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageplaza_mppricebargain_requests', 'request_id');
    }

    /**
     * @param string $sku
     * @param string $email
     *
     * @return bool
     * @throws LocalizedException
     */
    public function checkUnique($sku, $email)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where('customer_email = ?', $email)
            ->where('sku = ?', $sku)
            ->where('status IN (?)', [HelperData::STATUS_APPROVED, HelperData::STATUS_PENDING]);

        return (bool)$adapter->fetchCol($select);
    }

    /**
     * @param $request
     *
     * @return array
     */
    public function getMatchingOrderIds($request)
    {
        $status = ['complete', 'pending', 'processing'];
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getTable('sales_order'))
            ->where('customer_email = ?', $request->getCustomerEmail())
            ->where('total_qty_ordered >= ?', (int)$request->getBargainQty())
            ->where('status IN (?)', $status)
            ->where('created_at >= ?', $request->getSubmittedDate());

        return array_unique($adapter->fetchCol($select));
    }
}
