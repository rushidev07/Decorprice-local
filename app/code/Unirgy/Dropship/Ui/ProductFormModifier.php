<?php

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier {
    /*
    $hlp = \Magento\Framework\App\ObjectManager::getInstance()->get('\Unirgy\Dropship\Helper\Data');
    if (!$hlp->compareMageVer('2.1')) {*/
    if (!class_exists('\Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier')) {
        class AbstractModifier {

        }
    }
}