<?php

namespace {
    include_once __DIR__ . '/ViewAssetImage.php';
    include_once __DIR__ . '/ViewAssetImageFactory.php';
}

namespace Unirgy\Dropship\Model {
    class VendorAssetImageFactory extends \Magento\Catalog\Model\View\Asset\ImageFactory
    {
        protected $_objectManager;
        protected $_instanceName;
        public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $instanceName = '\\Unirgy\\Dropship\\Model\\VendorAssetImage')
        {
            $this->_objectManager = $objectManager;
            $this->_instanceName = $instanceName;
        }

        public function create(array $data = array())
        {
            return $this->_objectManager->create($this->_instanceName, $data);
        }
    }
}
