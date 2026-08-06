<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Block\Answer;

class Popup extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Aheadworks\Pquestion\Helper\Config
     */
    protected $_configHelper;

    /**
     * @var \Aheadworks\Pquestion\Helper\Request
     */
    protected $_requestHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Aheadworks\Pquestion\Model\Question
     */
    protected $_question;

    /**
     * @param \Aheadworks\Pquestion\Helper\Config $configHelper
     * @param \Aheadworks\Pquestion\Helper\Request $requestHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Aheadworks\Pquestion\Model\Question $question
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Aheadworks\Pquestion\Helper\Config $configHelper,
        \Aheadworks\Pquestion\Helper\Request $requestHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Aheadworks\Pquestion\Model\Question $question,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_configHelper = $configHelper;
        $this->_requestHelper = $requestHelper;
        $this->_customerSession = $customerSession;
        $this->_question = $question;
    }

    /**
     * @return bool
     */
    public function canShow()
    {
        $popupData = $this->_requestHelper->getPopupData();
        if (!is_array($popupData)) {
            return false;
        }
        if (count($popupData) == 0) {
            return false;
        }
        $this->setData($popupData);

        if ((int)$this->getCustomerId() !== (int)$this->_customerSession->getCustomerId()) {
            return false;
        }

        if ($this->getCustomerEmail() === null) {
            return false;
        }
        if ($this->getQuestion()->getId() === null) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    public function getAnswerFormHtml()
    {
        /** @var \Aheadworks\Pquestion\Block\Answer\Popup\Form $block */
        $block = $this->getChildBlock('aw_pq_add_answer_popup_form')
            ->setQuestionId($this->getQuestionId())
            ->setCustomerName($this->getCustomerName())
            ->setCustomerEmail($this->getCustomerEmail())
        ;
        return $block->toHtml();
    }

    /**
     * @return string
     */
    public function getSalutation()
    {
        $salutation = '';
        if ($this->getCustomerName()) {
            $salutation .= __('Dear %1!', $this->getCustomerName()) . ' ';
        }
        $salutation .= __('Could you please answer the question?');
        return $salutation;
    }

    /**
     * @return $this
     */
    public function getQuestion()
    {
        return $this->_question->load($this->getQuestionId());
    }

    /**
     * @return mixed
     */
    public function getQuestionContent()
    {
        return $this->getQuestion()->getContent();
    }
}
