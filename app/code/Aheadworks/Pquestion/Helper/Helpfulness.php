<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Helper;

class Helpfulness extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Aheadworks\Pquestion\Model\ResourceModel\Answer\Collection
     */
    protected $_answerCollection;

    /**
     * @var \Aheadworks\Pquestion\Model\ResourceModel\Summary\Question\Collection
     */
    protected $_summaryQuestionCollection;

    /**
     * @var \Aheadworks\Pquestion\Model\ResourceModel\Summary\Answer\Collection
     */
    protected $_summaryAnswerCollection;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Aheadworks\Pquestion\Model\ResourceModel\Answer\Collection $answerCollection
     * @param \Aheadworks\Pquestion\Model\ResourceModel\Summary\Question\Collection $summaryQuestionCollection
     * @param \Aheadworks\Pquestion\Model\ResourceModel\Summary\Answer\Collection $summaryAnswerCollection
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Aheadworks\Pquestion\Model\ResourceModel\Answer\Collection $answerCollection,
        \Aheadworks\Pquestion\Model\ResourceModel\Summary\Question\Collection $summaryQuestionCollection,
        \Aheadworks\Pquestion\Model\ResourceModel\Summary\Answer\Collection $summaryAnswerCollection
    ) {
        $this->_customerSession = $customerSession;
        $this->_answerCollection = $answerCollection;
        $this->_summaryQuestionCollection = $summaryQuestionCollection;
        $this->_summaryAnswerCollection = $summaryAnswerCollection;
        parent::__construct($context);
    }

    /**
     * @param array $questionIdList
     *
     * @return array
     */
    public function getVoteMap($questionIdList)
    {
        $voteMap = [
            'question_vote_map' => [],
            'answer_vote_map'   => [],
        ];
        if (count($questionIdList) <= 0) {
            return $voteMap;
        }
        $this->_summaryQuestionCollection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $this->_summaryQuestionCollection->getSelect()->columns(['question_id', 'helpful']);
        $this->_summaryQuestionCollection->addFilterByQuestionIds($questionIdList);

        if ($this->_customerSession->isLoggedIn()) {
            $this->_summaryQuestionCollection
                ->addFieldToFilter('customer_id', $this->_customerSession->getCustomerId());
        } else {
            $visitor = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Customer\Model\Visitor::class)
            ;
            $this->_summaryQuestionCollection
                ->addFieldToFilter('visitor_id', $visitor->getId())
            ;
        }
        $voteMap['question_vote_map'] =  $this->_summaryQuestionCollection->toOptionHash();
        $voteMap['answer_vote_map'] = $this->_getAnswerVoteMap($questionIdList);
        return $voteMap;
    }

    /**
     * @param mixed $questionIdList
     * @return array
     */
    protected function _getAnswerVoteMap($questionIdList)
    {
        $this->_answerCollection->getSelect()
            ->where('question_id in(?)', $questionIdList)
        ;
        $answerIdList = $this->_answerCollection->getAllIds();

        /* @var $collection \Aheadworks\Pquestion\Model\ResourceModel\Summary\Question\Collection*/
        $this->_summaryAnswerCollection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $this->_summaryAnswerCollection->getSelect()->columns(['answer_id', 'helpful']);
        $this->_summaryAnswerCollection->addFilterByAnswerIds($answerIdList);

        if ($this->_customerSession->isLoggedIn()) {
            $this->_summaryAnswerCollection->addFieldToFilter('customer_id', $this->_customerSession->getCustomerId());
        } else {
            $visitor = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Customer\Model\Visitor::class)
            ;
            $this->_summaryAnswerCollection->addFieldToFilter('visitor_id', $visitor->getId());
        }
        return $this->_summaryAnswerCollection->toOptionHash();
    }
}
