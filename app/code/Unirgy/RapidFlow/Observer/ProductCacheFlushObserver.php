<?php
/**
 * Created by pp
 * @project magento202
 */

namespace Unirgy\RapidFlow\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductCacheFlushObserver implements ObserverInterface
{
    /**
     * @var \Unirgy\RapidFlow\Helper\ProductCache
     */
    protected $helper;

    /**
     * CategoryUrlUpdateObserver constructor.
     * @param \Unirgy\RapidFlow\Helper\ProductCache $helper
     */
    public function __construct(\Unirgy\RapidFlow\Helper\ProductCache $helper)
    {
        $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Unirgy\RapidFlow\Model\Profile $profile */
        $profile = $observer->getData('profile');
        try {
            $this->helper->flushProductsCache($profile);
        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            $profile->getLogger()->error($e->getLogMessage());
        }
    }
}
