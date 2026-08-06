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

namespace Mageplaza\NameYourPrice\Controller\Adminhtml\Requests;

use Exception;
use Magento\Framework\Controller\Result\Redirect;
use Mageplaza\NameYourPrice\Controller\Adminhtml\Requests;

/**
 * Class Delete
 * @package Mageplaza\NameYourPrice\Controller\Adminhtml\Requests
 */
class Delete extends Requests
{
    /**
     * @return Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $requestId = $this->getRequest()->getParam('id');

        if ($requestId) {
            try {
                $this->_requestsFactory->create()->load($requestId)->delete();
                $this->messageManager->addSuccessMessage(__('The bargain has been deleted.'));
            } catch (Exception $e) {
                /** display error message */
                $this->messageManager->addErrorMessage($e->getMessage());
                /** go back to edit form */
                $resultRedirect->setPath('*/*/edit', ['id' => $requestId]);

                return $resultRedirect;
            }
        } else {
            /** display error message */
            $this->messageManager->addErrorMessage(__('Bargain to delete was not found.'));
        }

        /** goto grid */
        $resultRedirect->setPath('*/*/');

        return $resultRedirect;
    }
}
