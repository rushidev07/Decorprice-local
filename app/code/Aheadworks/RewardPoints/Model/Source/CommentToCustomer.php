<?php
namespace Aheadworks\RewardPoints\Model\Source;

use Aheadworks\RewardPoints\Model\Comment\CommentPool;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class Aheadworks\RewardPoints\Model\Source\CommentToCustomer
 */
class CommentToCustomer implements ArrayInterface
{
    /**
     * @var CommentPool
     */
    private $commentPool;

    /**
     * @var array
     */
    private $comments;

    /**
     * @param CommentPool $commentPool
     */
    public function __construct(CommentPool $commentPool)
    {
        $this->commentPool = $commentPool;
    }

    /**
     *  {@inheritDoc}
     */
    public function toOptionArray()
    {
        if ($this->comments == null) {
            $this->comments = [];
            foreach ($this->commentPool->getAllComments() as $commentInstance) {
                $this->comments[] = [
                    'value' => $commentInstance->getComment(),
                    'label' => $commentInstance->getLabel()
                ];
            }
        }
        return $this->comments;
    }
}
