<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Block\Adminhtml\Question\Edit\Tab\General;

class Answers extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Pquestion::answers/container.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Answers constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Block\Template\Context $context
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Template\Context $context
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function getFormHtml()
    {
        $block = $this->getLayout()
            ->createBlock(\Aheadworks\Pquestion\Block\Adminhtml\Question\Edit\Tab\General\Answers\Form::class)
        ;
        return $block->toHtml();
    }

    /**
     * @return \Aheadworks\Pquestion\Model\ResourceModel\Answer\Collection
     */
    public function getAnswerCollection()
    {
        /** @var \Aheadworks\Pquestion\Model\Question $questionModel */
        $questionModel = $this->_coreRegistry->registry('current_question');
        return $questionModel->getAnswerCollection()
            ->setOrder('entity_id', \Magento\Framework\DB\Select::SQL_DESC)
        ;
    }

    /**
     * @param \Aheadworks\Pquestion\Model\Answer $answer
     *
     * @return string
     */
    public function renderAnswerForm(\Aheadworks\Pquestion\Model\Answer $answer)
    {
        $block = $this->getLayout()
            ->createBlock(
                \Aheadworks\Pquestion\Block\Adminhtml\Question\Edit\Tab\General\Answers\ElementRenderer::class
            );
        $block->setAnswer($answer);
        return $block->toHtml();
    }
}
