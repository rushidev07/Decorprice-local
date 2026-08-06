<?php
namespace MageNJ\HideManualPayment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;

class ObserverforDisabledFrontendPg implements ObserverInterface
{
    protected $_appState;
    public function __construct(
        \Magento\Framework\App\State $appState
    ) {
        $this->_appState = $appState;
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
      $result = $observer->getEvent()->getResult();
      $method_instance = $observer->getEvent()->getMethodInstance();
      $quote = $observer->getEvent()->getQuote();

      if(null !== $quote){
          /* 
          *  During Checkout magento call payment methods from both
          *   area frontend and Web_api are
          */

          if($method_instance->getCode() =='checkmo' && 
            in_array($this->_appState->getAreaCode(), $this->getDisableAreas()))
            {
               $result->setData('is_available',false);
          }
      }

    }      

    protected function getDisableAreas(){

        return array( \Magento\Framework\App\Area::AREA_FRONTEND, \Magento\Framework\App\Area::AREA_WEBAPI_REST);
    }


}