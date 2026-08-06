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

namespace Mageplaza\NameYourPrice\Controller\Index;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\NameYourPrice\Helper\Data;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests\CollectionFactory;

/**
 * Class Request
 * @package Mageplaza\NameYourPrice\Controller\Index
 */
class Cancel extends Action
{
    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Cancel constructor.
     *
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory
    ) {
        $this->_collectionFactory = $collectionFactory;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        /** @var Requests $requestFactory */
        $requestId = $this->_request->getParam('request_id');
        $collection = $this->_collectionFactory->create()
            ->addFieldToFilter('request_id', $requestId)
            ->setPageSize(1)
            ->getFirstItem();

        try {
            $collection->setStatus(Data::STATUS_REJECT_BY_CUSTOMER)->save();
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect->setUrl($this->_redirect->getRefererUrl());

        return $redirect;
    }
}
