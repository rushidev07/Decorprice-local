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
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime;
use Mageplaza\NameYourPrice\Block\Product\View\Bargain;
use Mageplaza\NameYourPrice\Helper\Data as HelperData;
use Mageplaza\NameYourPrice\Helper\Email;
use Mageplaza\NameYourPrice\Model\Requests;
use Mageplaza\NameYourPrice\Model\RequestsFactory;
use Mageplaza\NameYourPrice\Model\ResourceModel\RequestsFactory as ResourceFactory;

/**
 * Class Request
 * @package Mageplaza\NameYourPrice\Controller\Index
 */
class Request extends Action
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var RequestsFactory
     */
    protected $_requestsFactory;

    /**
     * @var Bargain
     */
    protected $_bargain;

    /**
     * @var TypeListInterface
     */
    protected $_cache;

    /**
     * @var ResourceFactory
     */
    protected $_resourceFactory;

    /**
     * @var Email
     */
    protected $_email;

    /**
     * @var Image
     */
    protected $_imageHelper;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * Request constructor.
     *
     * @param Context $context
     * @param HelperData $helperData
     * @param RequestsFactory $requestsFactory
     * @param Bargain $bargain
     * @param TypeListInterface $cache
     * @param ResourceFactory $resourceFactory
     * @param Email $email
     * @param Image $imageHelper
     * @param Validator $formKeyValidator
     */
    public function __construct(
        Context $context,
        HelperData $helperData,
        RequestsFactory $requestsFactory,
        Bargain $bargain,
        TypeListInterface $cache,
        ResourceFactory $resourceFactory,
        Email $email,
        Image $imageHelper,
        Validator $formKeyValidator
    ) {
        $this->_helperData = $helperData;
        $this->_requestsFactory = $requestsFactory;
        $this->_bargain = $bargain;
        $this->_cache = $cache;
        $this->_resourceFactory = $resourceFactory;
        $this->_email = $email;
        $this->_imageHelper = $imageHelper;
        $this->formKeyValidator = $formKeyValidator;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $dataForm = $this->_request->getPostValue();
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect->setUrl($this->_redirect->getRefererUrl());

        if ($dataForm && $this->formKeyValidator->validate($this->getRequest())) {
            $data = $this->getData($dataForm);
            $requestFactory = $this->_resourceFactory->create();

            if ($requestFactory->checkUnique($data['sku'], $data['customer_email'])) {
                $this->messageManager->addErrorMessage(__('This email has bargained for this product.'));

                return $redirect;
            }

            $request = $this->_requestsFactory->create();

            try {
                $request->addData($data)->save();
                $this->sendMail($request, $dataForm);
                $this->messageManager->addSuccessMessage(
                    __('This request has been sent to the admin. Please wait for the response.')
                );
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        } else {
            $this->messageManager->addErrorMessage(__('Invalid Form Key.'));
        }

        return $redirect;
    }

    /**
     * @param $dataForm
     *
     * @return mixed
     * @throws Exception
     */
    public function getData($dataForm)
    {
        $productId = $dataForm['product_id'];
        $productInfo = $this->_helperData->getProductInfo($productId);

        $data['product_id'] = $productId;
        $data['product_name'] = $productInfo['name'];
        $data['sku'] = $productInfo['sku'];
        $data['options_bundle'] = $dataForm['options_bundle'];
        $data['serialize_bundle'] = $dataForm['serialize_bundle'];
        $data['options_configurable'] = $dataForm['options_configurable'];
        $data['original_price'] = $dataForm['original_price'] ?: $productInfo['price'];
        $data['bargain_price'] = $dataForm['bargain_price'];
        $data['bargain_qty'] = $this->_helperData->isBargainQty() ? $dataForm['bargain_qty'] : 1;
        $data['status'] = HelperData::STATUS_PENDING;
        $data['customer_name'] = $dataForm['customer_name'];
        $data['customer_email'] = $dataForm['customer_email'];
        $data['store_ids'] = $dataForm['store_id'];
        $data['submitted_date'] = date(DateTime::DATETIME_PHP_FORMAT);

        $fields = explode(',', $this->_helperData->getAdditional());
        foreach ($fields as $field) {
            if ($this->_bargain->checkAdditionalInfo($field)) {
                $data[$field] = $dataForm[$field];
            }
        }

        return $data;
    }

    /**
     * @param Requests $request
     * @param $dataForm
     *
     * @throws NoSuchEntityException
     */
    public function sendMail($request, $dataForm)
    {
        /** send mail confirm to customer */
        $this->_email->sendEmail(
            $this->_email->getConfirmTemplate(),
            $request->getCustomerEmail(),
            $this->getConfirmTemplateParams($dataForm)
        );

        /** send mail to admin */
        $adminEmails = $this->_email->getToAdminEmail();

        if ($adminEmails) {
            $product = $this->_helperData->getProductById($dataForm['product_id']);
            foreach ($adminEmails as $adminEmail) {
                $this->_email->sendEmail(
                    'mppricebargain_email_notify_admin',
                    $adminEmail,
                    ['productName' => $product->getName()]
                );
            }
        }
    }

    /**
     * @param array $dataForm
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getConfirmTemplateParams($dataForm)
    {
        /** @var Product $product */
        $product = $this->_helperData->getProductById($dataForm['product_id']);
        $image_url = $this->_imageHelper->init($product, 'product_thumbnail_image')->getUrl();

        return [
            'productName' => $product->getName(),
            'thumbnail' => $image_url,
            'customerName' => $dataForm['customer_name'],
            'bargainPrice' => $this->_helperData->getCurrency() . $dataForm['bargain_price'],
            'bargainQty' => $this->_helperData->isBargainQty() ? $dataForm['bargain_qty'] : null
        ];
    }
}
