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

namespace Mageplaza\NameYourPrice\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Mageplaza\NameYourPrice\Helper\Email;
use Mageplaza\NameYourPrice\Model\RequestsFactory;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests\CollectionFactory;
use Mageplaza\NameYourPrice\Model\ResourceModel\RequestsFactory as ResourceFactory;

/**
 * Class Requests
 * @package Mageplaza\NameYourPrice\Controller\Adminhtml
 */
abstract class Requests extends Action
{
    const ADMIN_RESOURCE = 'Mageplaza_NameYourPrice::requests';

    /**
     * @type PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @type Registry
     */
    protected $_coreRegistry;

    /**
     * @var RequestsFactory
     */
    protected $_requestsFactory;

    /**
     * @var ResourceFactory
     */
    protected $_resourceFactory;

    /**
     * @var Email
     */
    protected $_email;

    /**
     * @var Configurable
     */
    protected $_productConfigurable;

    /**
     * Mass Action Filter
     * @var Filter
     */
    public $filter;

    /**
     * @var CollectionFactory
     */
    public $collectionFactory;

    /**
     * Requests constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     * @param RequestsFactory $requestsFactory
     * @param ResourceFactory $resourceFactory
     * @param Email $email
     * @param Configurable $productConfigurable
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        RequestsFactory $requestsFactory,
        ResourceFactory $resourceFactory,
        Email $email,
        Configurable $productConfigurable,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->_coreRegistry        = $coreRegistry;
        $this->_resultPageFactory   = $resultPageFactory;
        $this->_requestsFactory     = $requestsFactory;
        $this->_resourceFactory     = $resourceFactory;
        $this->_email               = $email;
        $this->_productConfigurable = $productConfigurable;
        $this->filter               = $filter;
        $this->collectionFactory    = $collectionFactory;

        parent::__construct($context);
    }

    /**
     * @return bool|\Mageplaza\NameYourPrice\Model\Requests
     */
    protected function _initRequest()
    {
        $requestId      = (int) $this->getRequest()->getParam('id');
        $bargainRequest = $this->_requestsFactory->create();

        if ($requestId) {
            $bargainRequest->load($requestId);
            if (!$bargainRequest->getId()) {
                $this->messageManager->addErrorMessage(__('This bargain no longer exists.'));

                return false;
            }
        }

        if (!$this->_coreRegistry->registry('current_request')) {
            $this->_coreRegistry->register('current_request', $bargainRequest);
        }

        return $bargainRequest;
    }

    /**
     * @param \Mageplaza\NameYourPrice\Model\Requests $request
     * @param string $adminMessage
     *
     * @return array
     */
    public function getApproveTemplateParams($request, $adminMessage)
    {
        $limitTime       = $this->_email->getLimitTime();
        $productId       = $request->getProductId();
        $parentProductId = $this->_productConfigurable->getParentIdsByChild($productId);

        if (isset($parentProductId[0])) {
            $productId = $parentProductId[0];
        }

        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $getCustomerCofig = $objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface')
                ->getValue('mppricebargain/general/customer_groups',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        } catch (\Exception $e) {

        }
        $product = $this->_email->getProductById($productId);

        if(isset($getCustomerCofig) && !stristr($getCustomerCofig, '0')) {
            return [
                'customer_name'       => $request->getCustomerName(),
                'product_name'        => $request->getProductName(),
                'limit_time_label'    => $limitTime ? __('This price lasts for %1 days.', $limitTime) : '',
                'bargain_product_url' => rtrim($product->getProductUrl(), '/') . '?request_id=' . $request->getId(),
                'admin_message'       => $adminMessage,
                'available_not_login' => 'true'
            ];
        }

        return [
            'customer_name'       => $request->getCustomerName(),
            'product_name'        => $request->getProductName(),
            'limit_time_label'    => $limitTime ? __('This price lasts for %1 days.', $limitTime) : '',
            'bargain_product_url' => rtrim($product->getProductUrl(), '/') . '?request_id=' . $request->getId(),
            'admin_message'       => $adminMessage
        ];
    }

    /**
     * @param $request
     * @param $adminMessage
     *
     * @return array
     */
    public function getRejectTemplateParams($request, $adminMessage)
    {
        return [
            'customer_name' => $request->getCustomerName(),
            'product_name'  => $request->getProductName(),
            'admin_message' => $adminMessage
        ];
    }

    /**
     * @return false|string|null
     */
    public function getTimeUse()
    {
        $limitTimeUse = $this->_email->getLimitTime();

        if ($limitTimeUse) {
            $now    = date('Y-m-d h:i:s');
            $toTime = strtotime('+' . $limitTimeUse . 'day', strtotime($now));

            return date('Y-m-d h:i:s', $toTime);
        }

        return null;
    }
}
