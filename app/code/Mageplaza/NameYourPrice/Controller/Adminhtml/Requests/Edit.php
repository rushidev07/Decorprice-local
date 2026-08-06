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

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\NameYourPrice\Controller\Adminhtml\Requests;

/**
 * Class Edit
 * @package Mageplaza\NameYourPrice\Controller\Adminhtml\Requests
 */
class Edit extends Requests
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $requestId = $this->getRequest()->getParam('request_id');
        $request = $this->_initRequest();

        if ($requestId && !$request->getId()) {
            $this->messageManager->addErrorMessage(__('This request no longer exists.'));
            $this->_redirect('*/*/');

            return;
        }

        $this->_view->loadLayout();
        $this->_setActiveMenu('Mageplaza_NameYourPrice::requests');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Edit Bargain Price'));

        $this->_view->renderLayout();
    }
}
