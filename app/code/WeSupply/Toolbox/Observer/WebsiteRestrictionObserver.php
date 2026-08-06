<?php
namespace WeSupply\Toolbox\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class WebsiteRestrictionObserver implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }


    /**
     * @inheritDoc
     */
    public function execute(Observer $observer): void
    {
        if ($this->request->getModuleName() == 'wesupply') {
            $result = $observer->getResult();
            $result->setData('should_proceed', false);
        }
    }
}
