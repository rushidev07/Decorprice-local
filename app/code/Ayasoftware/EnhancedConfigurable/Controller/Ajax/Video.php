<?php

namespace Ayasoftware\EnhancedConfigurable\Controller\Ajax;

class Video extends \Ayasoftware\EnhancedConfigurable\Controller\Ajax
{
    public function execute()
    {
      $product = $this->_initProduct();
        if (!empty($product)) {
           $block =  $this->_view->loadLayout()->getLayout()->getBlock('product.video')->toHtml();
            $this->getResponse()->setBody($block);
        }
    }
}
