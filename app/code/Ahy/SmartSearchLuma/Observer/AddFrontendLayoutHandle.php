<?php
declare(strict_types=1);

namespace Ahy\SmartSearchLuma\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Ahy\SmartSearchLuma\Helper\Data;

class AddFrontendLayoutHandle implements ObserverInterface
{
    public function __construct(private Data $helper) {}

    public function execute(Observer $observer): void
    {
        if (!$this->helper->isFrontendEnabled()) {
            return;
        }

        /** @var \Magento\Framework\View\Layout\ProcessorInterface $update */
        $update = $observer->getEvent()->getLayout()->getUpdate();
        $update->addHandle('ahy_smartsearch_active');
    }
}
