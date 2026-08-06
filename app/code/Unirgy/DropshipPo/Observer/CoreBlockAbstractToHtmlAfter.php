<?php

namespace Unirgy\DropshipPo\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Block\Adminhtml\Order\Create\Totals;

class CoreBlockAbstractToHtmlAfter extends AbstractObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $block = $observer->getBlock();
        $transport = $observer->getTransport();
        if ($block instanceof Totals
            && $block->getNameInLayout() == 'totals'
        ) {
            $transport->setHtml(
                $transport->getHtml()
                .$block->getLayout()
                    ->createBlock(
                        'core/template', 'noautopo_flag',
                        ['template'=>'udpo/sales/createorder/noautopo_flag.phtml']
                    )->toHtml()
            );
        }
    }
}
