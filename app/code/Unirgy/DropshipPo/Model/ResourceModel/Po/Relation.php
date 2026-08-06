<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Unirgy\DropshipPo\Model\ResourceModel\Po;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;
use Unirgy\DropshipPo\Model\ResourceModel\Po\Item as PoItemResource;
use Unirgy\DropshipPo\Model\ResourceModel\Po\Comment as PoCommentResource;

/**
 * Class Relation
 */
class Relation implements RelationInterface
{
    /**
     * @var poItemResource
     */
    protected $poItemResource;

    /**
     * @var poCommentResource
     */
    protected $poCommentResource;

    /**
     * @param Item $poItemResource
     * @param Track $poTrackResource
     * @param Comment $poCommentResource
     */
    public function __construct(
        PoItemResource $poItemResource,
        PoCommentResource $poCommentResource
    ) {
        $this->poItemResource = $poItemResource;
        $this->poCommentResource = $poCommentResource;
    }

    /**
     * Process relations for po
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     * @throws \Exception
     */
    public function processRelation(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Unirgy\DropshipPo\Model\Po $object */
        if (null !== $object->getItems()) {
            foreach ($object->getItems() as $item) {
                $item->setParentId($object->getId());
                $this->poItemResource->save($item);
            }
        }
        if (null !== $object->getComments()) {
            foreach ($object->getComments() as $comment) {
                $this->poCommentResource->save($comment);
            }
            $object->getOrder()->getStatusHistoryCollection()->save();
        }
    }
}
