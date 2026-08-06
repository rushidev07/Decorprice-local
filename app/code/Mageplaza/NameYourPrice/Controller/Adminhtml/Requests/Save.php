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
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\NameYourPrice\Controller\Adminhtml\Requests;
use Mageplaza\NameYourPrice\Helper\Data;

/**
 * Class Save
 * @package Mageplaza\NameYourPrice\Controller\Adminhtml\Requests
 */
class Save extends Requests
{
    /**
     * @return Redirect|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $data = $this->getRequest()->getPost('request');
        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }

        /** @var \Mageplaza\NameYourPrice\Model\Requests $request */
        $request = $this->_initRequest();

        $type = $this->getRequest()->getParam('type');
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('request_id', $request->getId())
            ->setPageSize(1)
            ->getFirstItem();

        try {
            if ($type === 'save_approve') {
                $collection->setStatus(Data::STATUS_APPROVED)->save();
                $collection->setTimeUse($this->getTimeUse())->save();

                $this->_email->sendEmail(
                    $this->_email->getApproveTemplate(),
                    $request->getCustomerEmail(),
                    $this->getApproveTemplateParams($request, $data['admin_message'])
                );

                $this->messageManager->addSuccessMessage(__('The bargain has been approved.'));
            } elseif ($type === 'save_reject') {
                $collection->setStatus(Data::STATUS_REJECT)->save();
                $this->_email->sendEmail(
                    $this->_email->getRejectTemplate(),
                    $request->getCustomerEmail(),
                    $this->getApproveTemplateParams($request, $data['admin_message'])
                );

                $this->messageManager->addSuccessMessage(__('The request has been rejected.'));
            } else {
                $this->messageManager->addSuccessMessage(__('You saved the request.'));
            }

            $request->setData($data)->save();
        } catch (Exception $e) {
            $resultRedirect->setPath('*/*/edit', ['id' => $request->getId(), '_current' => true]);
        }

        $resultRedirect->setPath('*/*/');

        return $resultRedirect;
    }
}
