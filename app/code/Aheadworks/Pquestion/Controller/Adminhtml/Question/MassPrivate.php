<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Controller\Adminhtml\Question;

class MassPrivate extends \Aheadworks\Pquestion\Controller\Adminhtml\Question
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
            if ($question->getVisibility() == \Aheadworks\Pquestion\Model\Source\Question\Visibility::PRIVATE_VALUE) {
                continue;
            }
            $question
                ->setVisibility(\Aheadworks\Pquestion\Model\Source\Question\Visibility::PRIVATE_VALUE)
                ->save()
            ;
            $count++;
        }
        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been made private.', $count)
        );
        return $this->resultRedirectFactory->create()->setRefererUrl();
    }
}
