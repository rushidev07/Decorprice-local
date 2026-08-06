<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    Exit Intent Popup Pro from M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Eop\Model\Config\Source\Eav;

class Campaign extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Magetrend\Eop\Model\ResourceModel\Campaign\CollectionFactory
     */
    public $collectionFactory;

    /**
     * Campaign constructor.
     * @param \Magetrend\Eop\Model\ResourceModel\Campaign\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magetrend\Eop\Model\ResourceModel\Campaign\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllOptions()
    {
        if (null === $this->_options) {
            $this->_options = [
                [
                    'value' => '',
                    'label' => '---- ----'
                ]
            ];

            $collection = $this->collectionFactory->create();
            if ($collection->getSize() > 0) {
                foreach ($collection as $rule) {
                    $this->_options[] = [
                        'value' => $rule->getId(),
                        'label' => $rule->getName()
                    ];
                }
            }
        }
        return $this->_options;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}
