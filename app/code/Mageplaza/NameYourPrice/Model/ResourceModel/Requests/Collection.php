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

namespace Mageplaza\NameYourPrice\Model\ResourceModel\Requests;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mageplaza\NameYourPrice\Model\Requests;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests as ResourceRequests;

/**
 * Class Collection
 * @package Mageplaza\NameYourPrice\Model\ResourceModel\Requests
 */
class Collection extends AbstractCollection
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_mppricebargain_requests_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'requests_collection';

    /**
     * @var string
     */
    protected $_idFieldName = 'request_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Requests::class, ResourceRequests::class);
    }
}
