<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Controller\Adminhtml\Question;

class MassPublish extends \Aheadworks\Pquestion\Controller\Adminhtml\Question
{
    /**
     * @var \Aheadworks\Pquestion\Model\ResourceModel\Question\Collection
     */
    protected $_collection;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $_filter;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Aheadworks\Pquestion\Model\ResourceModel\Question\Collection $collection
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Aheadworks\Pquestion\Model\ResourceModel\Question\Collection $collection
    ) {
        parent::__construct($context, $filterManager);
        $this->_collection = $collection;
        $this->_filter = $filter;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $this->_collection = $this->_filter->getCollection($this->_collection);
        $count = 0;
        foreach ($this->_collection->getItems() as $question) {
            if ($question->getStatus() == \Aheadworks\Pquestion\Model\Source\Question\Status::APPROVED_VALUE) {
                continue;
            }
            $question
                ->setStatus(\Aheadworks\Pquestion\Model\Source\Question\Status::APPROVED_VALUE)
                ->save()
            ;
            $count++;
        }
        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been published.', $count)
        );
        return $this->resultRedirectFactory->create()->setRefererUrl();
    }
}
