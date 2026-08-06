<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Model\Summary;

use Magento\Framework\DataObject\IdentityInterface;

class Question extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'aw_pquestion_summary_question';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Aheadworks\Pquestion\Model\ResourceModel\Summary\Question::class);
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
