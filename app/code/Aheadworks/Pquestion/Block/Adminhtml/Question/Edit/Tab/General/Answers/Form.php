<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Block\Adminhtml\Question\Edit\Tab\General\Answers;

class Form extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Pquestion::answers/form.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getActionUrl()
    {
        return $this->getUrl('productquestion/answer/save');
    }

    /**
     * @return mixed
     */
    public function getQuestionId()
    {
        $question = $this->_coreRegistry->registry('current_question');
        return $question->getId();
    }

    /**
     * @return bool
     */
    public function isCanShowButton()
    {
        return $this->getQuestionId() > 0;
    }
}
