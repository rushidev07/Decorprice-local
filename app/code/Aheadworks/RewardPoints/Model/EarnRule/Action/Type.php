<?php
namespace Aheadworks\RewardPoints\Model\EarnRule\Action;

/**
 * Class Type
 * @package Aheadworks\RewardPoints\Model\EarnRule\Action
 */
class Type implements TypeInterface
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * @var string[]
     */
    private $attributeCodes;

    /**
     * @param $title
     * @param ProcessorInterface $processor
     * @param array $attributeCodes
     */
    public function __construct(
        $title,
        ProcessorInterface $processor,
        $attributeCodes = []
    ) {
        $this->title = $title;
        $this->processor = $processor;
        $this->attributeCodes = $attributeCodes;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeCodes()
    {
        return $this->attributeCodes;
    }
}
