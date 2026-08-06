<?php

namespace Unirgy\DropshipPo\Observer;

use Magento\CatalogInventory\Model\StockFactory;
use Magento\Catalog\Model\ProductFactory as ModelProductFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Config;
use Psr\Log\LoggerInterface;
use Unirgy\DropshipPo\Helper\Data as DropshipPoHelperData;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\Dropship\Model\Vendor\ProductFactory;

class SalesOrderSaveBefore extends AbstractObserver implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    public function __construct(
        RequestInterface $request,
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        \Unirgy\DropshipPo\Helper\Data $udpoHelper,
        \Psr\Log\LoggerInterface $logger,
        \Unirgy\Dropship\Model\Vendor\ProductFactory $vendorProductFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Quote\Model\Quote\Config $quoteConfig
    )
    {
        $this->_request = $request;

        parent::__construct($udropshipHelper, $udpoHelper, $logger, $vendorProductFactory, $productFactory, $quoteConfig);
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        if (($postOrderData =$this->_request->getPost('order'))
            && !empty($postOrderData['noautopo_flag'])
        ) {
            $order->setData('noautopo_flag', $postOrderData['noautopo_flag']);
        }
    }
}
