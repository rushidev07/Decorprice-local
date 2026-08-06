<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Model\Serialize;

use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class Serializer
 * @package Aheadworks\Pquestion\Model\Serialize
 */
class Serializer
{
    /**
     * Serialized pattern
     */
    const SERIALIZED_PATTERN = '/^((s|i|d|b|a|O|C):|N;)/';

    /**
     * @var Serialize
     */
    private $phpSerialize;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param Serialize $phpSerialize
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Serialize $phpSerialize,
        SerializerInterface $serializer
    ) {
        $this->phpSerialize = $phpSerialize;
        $this->serializer = $serializer;
    }

    /**
     * Unserialize data
     *
     * @param $data
     * @return array|bool|float|int|mixed|string|null
     */
    public function unserialize($data)
    {
        if ($this->isSerialized($data)) {
            return $this->phpSerialize->unserialize($data);
        } else {
            return $this->serializer->unserialize($data);
        }
    }

    /**
     * Serialize data
     *
     * @param $data
     * @return bool|string
     */
    public function serialize($data)
    {
        return $this->serializer->serialize($data);
    }

    /**
     * Check if value is a serialized string
     *
     * @param string $value
     * @return boolean
     */
    private function isSerialized($value)
    {
        return (boolean) preg_match(self::SERIALIZED_PATTERN, $value);
    }
}
