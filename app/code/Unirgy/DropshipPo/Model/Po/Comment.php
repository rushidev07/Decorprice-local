<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_DropshipPo
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipPo\Model\Po;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Sales\Model\AbstractModel;
use Magento\Store\Model\StoreManagerInterface;
use Unirgy\DropshipPo\Model\Po;

class Comment extends AbstractModel
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    public function __construct(
        StoreManagerInterface $storeManager,
        Context $context,
        Registry $registry, 
        ExtensionAttributesFactory $extensionFactory, 
        AttributeValueFactory $customAttributeFactory, 
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null, 
        array $data = [])
    {
        $this->_storeManager = $storeManager;

        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $resource, $resourceCollection, $data);
    }

    protected $_eventPrefix = 'udpo_po_comment';
    protected $_eventObject = 'po_comment';
    
    protected $_po;

    protected function _construct()
    {
        $this->_init('Unirgy\DropshipPo\Model\ResourceModel\Po\Comment');
    }

    public function setPo(Po $po)
    {
        $this->_po = $po;
        return $this;
    }

    public function getPo()
    {
        return $this->_po;
    }

    public function getStore()
    {
        if ($this->getPo()) {
            return $this->getPo()->getStore();
        }
        return $this->_storeManager->getStore();
    }

    public function beforeSave()
    {
        parent::beforeSave();

        if (!$this->getParentId() && $this->getPo()) {
            $this->setParentId($this->getPo()->getId());
        }

        return $this;
    }
}
