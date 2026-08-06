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

class MassActive extends \Magento\Backend\App\Action
{
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
        \Ced\Houzz\Helper\Data $dataHelper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \DOMException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        if(!$this->dataHelper->checkForConfiguration()) {
            $this->messageManager->addErrorMessage(__('Products Inventory Sync Failed . Houzz API not enabled or Invalid. Please check Houzz Configuration.'));
            return $this->_redirect('*/*/index');
        }
        $collection = $this->filter->getCollection($this->_objectManager->create('Magento\Catalog\Model\Product')
            ->getCollection());
        $productids = $collection->getAllIds();
        if (count($productids) == 0) {
            $this->messageManager->addErrorMessage('No Product selected for Inventory Update.');
            $this->_redirect('houzz/products/index');
        }
        $response=$this->dataHelper->changeProductStatusOnHouzz($productids , "active");
        $countsuccess=0;
        $counterror=0;
        if(!empty($response)) {
            foreach ($response as $key => $value) {
                if (strpos($value, 'Success') !== false) {
                    $countsuccess++;
                } else if (strpos($value, 'Error') !== false) {
                    $counterror++;
                }
            }
            if (!empty($response)) {
                $this->messageManager->addSuccessMessage("Total " . $countsuccess . ' Product(s) Active On Houzz Successfully.');

            }
            $this->messageManager->addErrorMessage("Total " . $counterror . ' Product(s) Active On Houzz Failed.');
            return $this->_redirect('houzz/products/index');
        }else{
            $this->messageManager->addErrorMessage("please select the product");
            return $this->_redirect('houzz/products/index');

        }

    }

}
