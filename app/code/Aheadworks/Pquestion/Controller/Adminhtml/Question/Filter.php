<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Controller\Adminhtml\Question;

class Filter extends \Aheadworks\Pquestion\Controller\Adminhtml\Question
{
    /**
     * @var \Magento\Ui\Api\BookmarkManagementInterface
     */
    protected $bookmarkManagement;

    /**
     * @var \Magento\Ui\Api\BookmarkRepositoryInterface
     */
    protected $bookmarkRepository;

    /**
     * @var \Aheadworks\Pquestion\Helper\Bookmark
     */
    protected $bookmarkHelper;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncode;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Ui\Api\BookmarkManagementInterface $bookmarkManagement
     * @param \Magento\Ui\Api\BookmarkRepositoryInterface $bookmarkRepository
     * @param \Aheadworks\Pquestion\Helper\Bookmark $bookmarkHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncode
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Ui\Api\BookmarkManagementInterface $bookmarkManagement,
        \Magento\Ui\Api\BookmarkRepositoryInterface $bookmarkRepository,
        \Aheadworks\Pquestion\Helper\Bookmark $bookmarkHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncode
    ) {
        parent::__construct($context, $filterManager);
        $this->bookmarkManagement = $bookmarkManagement;
        $this->bookmarkRepository = $bookmarkRepository;
        $this->bookmarkHelper = $bookmarkHelper;
        $this->jsonEncode = $jsonEncode;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Ui\Api\Data\BookmarkInterface $currentBookmark */
        $currentBookmark = $this->bookmarkManagement->getByIdentifierNamespace('current', 'aw_pq_question_listing');
        $config = $this->bookmarkHelper->getQuestionGridDefaultConfigData();
        $config['filters']['applied'] = [
            'product_id' => [
                'from' => $this->_request->getParam('id', ''),
                'to' => $this->_request->getParam('id', '')
            ]
        ];
        $currentConfig = $currentBookmark->getConfig();
        $currentConfig['current'] = $config;
        $currentBookmark->setConfig($this->jsonEncode->encode($currentConfig));

        $this->bookmarkRepository->save($currentBookmark);
        return $this->resultRedirectFactory->create()->setPath('*/*/grid');
    }
}
