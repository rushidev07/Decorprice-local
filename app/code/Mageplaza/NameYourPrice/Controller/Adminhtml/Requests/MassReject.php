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
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\NameYourPrice\Controller\Adminhtml\Requests;
use Mageplaza\NameYourPrice\Helper\Data;

/**
 * Class MassReject
 * @package Mageplaza\NameYourPrice\Controller\Adminhtml\Requests
 */
class MassReject extends Requests
{
    /**
     * @return $this|ResponseInterface|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = 0;
        $notEditStatus = [Data::STATUS_REJECT_BY_CUSTOMER, Data::STATUS_CLOSED, Data::STATUS_REJECT];

        /** @var \Mageplaza\NameYourPrice\Model\Requests $request */
        foreach ($collection->getItems() as $request) {
            if (in_array($request->getStatus(), $notEditStatus, true)) {
                $this->messageManager->addErrorMessage(
                    __(
                        'Cannot reject the bargain request ID %1. It has been approved/rejected, closed or cancelled by customer',
                        $request->getId()
                    )
                );
            } else {
                try {
                    $request->setStatus(Data::STATUS_REJECT)->save();
                    $this->_email->sendEmail(
                        $this->_email->getRejectTemplate(),
                        $request->getCustomerEmail(),
                        $this->getApproveTemplateParams($request, $request->getAdminMessage())
                    );
                    $collectionSize++;
                } catch (Exception $e) {
                    $this->messageManager->addErrorMessage(
                        __(
                            'Cannot reject the bargain request ID %1. It has been approved/rejected, closed or cancelled by customer',
                            $e->getMessage()
                        )
                    );
                }
            }
        }

        if ($collectionSize) {
            $this->messageManager->addSuccessMessage(__(
                'A total of %1 record(s) have been rejected.',
                $collectionSize
            ));
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
