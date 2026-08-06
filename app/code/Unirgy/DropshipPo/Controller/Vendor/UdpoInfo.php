<?php

namespace Unirgy\DropshipPo\Controller\Vendor;

use Magento\Store\Model\StoreManagerInterface;

class UdpoInfo extends AbstractVendor
{

    public function execute()
    {
        $view = $this->_hlp->createObj('\Magento\Framework\App\ViewInterface');
        $this->_setTheme();
        $view->addActionLayoutHandles();

        /** @var \Unirgy\DropshipPo\Block\Vendor\Po\Info $infoBlock */
        $infoBlock = $view->getLayout()->getBlock('info');
        if (($url = $this->_registry->registry('udropship_download_url'))) {
            $infoBlock->setDownloadUrl($url);
        }
        $view->getLayout()->initMessages();

        return $this->_resultRawFactory->create()->setContents($infoBlock->toHtml());
    }

}