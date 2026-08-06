<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Model\ResourceModel;

use Magento\Framework\DataObject;
use Aheadworks\Pquestion\Model\Serialize\Serializer;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Question
 * @package Aheadworks\Pquestion\Model\ResourceModel
 */
class Question extends AbstractDb
{
    /**
     * @var array
     */
    protected $_serializableFields = ['sharing_value' => [null, []]];

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @param Context $context
     * @param Serializer $serializer
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        Serializer $serializer,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->serializer = $serializer;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('aw_pq_question', 'entity_id');
    }

    /**
     * {@inheritdoc}
     */
    protected function _unserializeField(DataObject $object, $field, $defaultValue = null)
    {
        $value = $object->getData($field);
        if ($value && is_string($value)) {
            $value = $this->serializer->unserialize($object->getData($field));
            if (empty($value)) {
                $object->setData($field, $defaultValue);
            } else {
                $object->setData($field, $value);
            }
        } else {
            $object->setData($field, $defaultValue);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _serializeField(DataObject $object, $field, $defaultValue = null, $unsetEmpty = false)
    {
        $value = $object->getData($field);
        if (empty($value) && $unsetEmpty) {
            $object->unsetData($field);
        } else {
            $object->setData($field, $this->serializer->serialize($value ?: $defaultValue));
        }

        return $this;
    }
}
