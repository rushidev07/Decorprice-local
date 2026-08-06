<?php

namespace Unirgy\Dropship\Controller\Adminhtml\Shipment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory;

class ResendPo extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Unirgy\Dropship\Helper\Data
     */
    protected $_hlp;

    /**
     * ResendPo constructor.
     * @param \Unirgy\Dropship\Helper\Data $udropshipHelper
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->_hlp = $udropshipHelper;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $filter);
    }
    protected function massAction(AbstractCollection $collection)
    {
        $countResendTotals = 0;
        foreach ($collection as $shipment) {
            $shipment->afterLoad();
            $shipment->setResendNotificationFlag(true);
            $this->_hlp->sendVendorNotification($shipment);
            $countResendTotals++;
        }
        $countNonResendTotals = $collection->count() - $countResendTotals;

        if ($countNonResendTotals && $countResendTotals) {
            $this->messageManager->addError(__('%1 shipments(s) cannot be resent.', $countNonResendTotals));
        } elseif ($countNonResendTotals) {
            $this->messageManager->addError(__('You cannot resent the shipment(s).'));
        }

        if ($countResendTotals) {
            $this->messageManager->addSuccess(__('We resent %1 shipment(s).', $countResendTotals));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/shipment/');
        return $resultRedirect;
    }
}
