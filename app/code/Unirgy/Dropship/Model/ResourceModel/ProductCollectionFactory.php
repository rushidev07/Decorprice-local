<?php

namespace Unirgy\Dropship\Model\ResourceModel;

class ProductCollectionFactory extends \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName = null;
    /**
     * @var \Unirgy\Dropship\Helper\Data
     */
    protected $dropshipHelper;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Unirgy\Dropship\Helper\Data $dropshipHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = '\\Unirgy\\Dropship\\Model\\ResourceModel\\ProductCollection')
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
        $this->dropshipHelper = $dropshipHelper;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function create(array $data = [])
    {
        $collection = $this->_objectManager->create($this->_instanceName, $data);
        if ($this->dropshipHelper->isVendorPortalAction()) {
            $collection->setFlag('udskip_price_index',1)
                ->setFlag('udskip_shared_catalog',1);
        }
        return $collection;
    }
}
