<?php
namespace Ahy\NameYourPricePatch\Plugin;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Response\RedirectInterface;
use Mageplaza\NameYourPrice\Helper\Data as HelperData;

class RequestValidator
{
    protected $messageManager;
    protected $redirectFactory;
    protected $redirect;
    protected $helperData;

    public function __construct(
        ManagerInterface $messageManager,
        RedirectFactory $redirectFactory,
        RedirectInterface $redirect,
        HelperData $helperData
    ) {
        $this->messageManager  = $messageManager;
        $this->redirectFactory = $redirectFactory;
        $this->redirect        = $redirect;
        $this->helperData      = $helperData;
    }

    public function aroundExecute(
        \Mageplaza\NameYourPrice\Controller\Index\Request $subject,
        \Closure $proceed
    ) {
        $dataForm = $subject->getRequest()->getPostValue();
        if (!$dataForm) {
            return $proceed();
        }

        try {
            $productId = (int) $dataForm['product_id'];
            $product   = $this->helperData->getProductById($productId);

            // Eligibility check
            if (!$product->getData('suggest_price')) { // replace with correct attribute/flag
                $this->messageManager->addErrorMessage(__('This product is not eligible for bargain requests.'));
                $resultRedirect = $this->redirectFactory->create();
                $resultRedirect->setUrl($this->redirect->getRefererUrl());
                return $resultRedirect;
            }

            // Email validation
            if (empty($dataForm['customer_email']) || !filter_var($dataForm['customer_email'], FILTER_VALIDATE_EMAIL)) {
                $this->messageManager->addErrorMessage(__('Invalid email address.'));
                $resultRedirect = $this->redirectFactory->create();
                $resultRedirect->setUrl($this->redirect->getRefererUrl());
                return $resultRedirect;
            }

            // Price validation
            if (empty($dataForm['bargain_price']) || !is_numeric($dataForm['bargain_price'])) {
                $this->messageManager->addErrorMessage(__('Invalid bargain price.'));
                $resultRedirect = $this->redirectFactory->create();
                $resultRedirect->setUrl($this->redirect->getRefererUrl());
                return $resultRedirect;
            }

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error validating request: %1', $e->getMessage()));
            $resultRedirect = $this->redirectFactory->create();
            $resultRedirect->setUrl($this->redirect->getRefererUrl());
            return $resultRedirect;
        }

        // If validation passes → continue with original execute()
        return $proceed();
    }
}
