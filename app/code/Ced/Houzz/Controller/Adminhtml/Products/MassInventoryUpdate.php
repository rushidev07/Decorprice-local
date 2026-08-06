<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Houzz
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Houzz\Controller\Adminhtml\Products;

class MassInventoryUpdate extends \Magento\Backend\App\Action
{
    const CHUNK_SIZE = 1;
    /**
     * Result Page Factory
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * Filter
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    public $dataHelper;

    /**
     * MassInventory constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Ced\Houzz\Helper\Data $dataHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
        $this->dataHelper = $dataHelper;
        $this->session =  $context->getSession();
        $this->resultJsonFactory = $resultJsonFactory;
        $this->product = $product;
        $this->registry = $registry;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     * @throws \DOMException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        if(!$this->dataHelper->checkForConfiguration()) {
            $this->messageManager->addErrorMessage(__('Products Inventory Sync Failed . Houzz API not enabled or Invalid. Please check Houzz Configuration.'));
            return $this->_redirect('*/*/index');
        }
        $batchid = $this->getRequest()->getParam('batchid');
        if (isset($batchid)) {
            $resultJson = $this->resultJsonFactory->create();
            $productids = $this->session->getHouzzProducts();
            $lastElement = end($productids);
            //$lastElementKey = key($productids);
            if(empty($this->session->getResponseSession()))
                $this->session->setResponseSession([]);
            $this->session->setAllBatchCompleted(false);
            if($lastElement[0] == $productids[$batchid][0]) {
                $this->session->setAllBatchCompleted(true);
            }
            $product = $this->product->load($productids[$batchid][0]);
            if (isset($productids[$batchid]) && $this->dataHelper->updateInventoryOnHouzz($productids[$batchid])) {
                return $resultJson->setData([
                    'success' => "<b> SKU : " . $product->getSku() . "</b> Product(s) Inventory Updated successfully",
                ]);
            }
            return $resultJson->setData([
                'error' => "<b> SKU : " . $product->getSku() . "</b> Product(s) Inventory Updation Failed",
            ]);
        }
        $collection = $this->filter->getCollection($this->_objectManager->create('Magento\Catalog\Model\Product')
            ->getCollection());
        $productids = $collection->getAllIds();
        if (count($productids) == 0) {
            $this->messageManager->addErrorMessage('No Product selected to upload.');
            $resultRedirect = $this->resultFactory->create('redirect');
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        if (count($productids) < self::CHUNK_SIZE - 1) {
            if ( $this->dataHelper->updateInventoryOnHouzz($productids)) {
                $this->messageManager->addSuccessMessage(count($productids) . ' Product(s) Inventory Updated Successfully');
            } else {
                $this->messageManager->addErrorMessage('Product(s) Inventory Updation Failed.');
            }

            $resultRedirect = $this->resultFactory->create('redirect');
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
        $productids = array_chunk($productids, self::CHUNK_SIZE);
        $this->registry->register('productids', count($productids));
        $this->session->setHouzzProducts($productids);
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Houzz::Houzz');
        $resultPage->getConfig()->getTitle()->prepend(__('Update Inventory'));
        return $resultPage;
    }

    /**
     * IsALLowed
     * @return boolean
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_Houzz::Houzz');
    }

}
