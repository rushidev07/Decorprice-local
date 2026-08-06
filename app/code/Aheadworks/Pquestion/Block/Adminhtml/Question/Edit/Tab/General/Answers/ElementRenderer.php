<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Block\Adminhtml\Question\Edit\Tab\General\Answers;

use Aheadworks\Pquestion\Model\Customer\Checker as CustomerChecker;
use Aheadworks\Pquestion\Model\Answer;
use Magento\Backend\Block\Template;
use Aheadworks\Pquestion\Model\Source\Question\Status;
use Magento\Backend\Block\Template\Context;

/**
 * Class ElementRenderer
 * @package Aheadworks\Pquestion\Block\Adminhtml\Question\Edit\Tab\General\Answers
 * @method Answer getAnswer
 */
class ElementRenderer extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Pquestion::answers/element.phtml';

    /**
     * @var Status
     */
    protected $_sourceStatus;

    /**
     * @var CustomerChecker
     */
    private $customerChecker;

    /**
     * @param Status $sourceStatus
     * @param Context $context
     * @param CustomerChecker $customerChecker
     * @param array $data
     */
    public function __construct(
        Status $sourceStatus,
        Context $context,
        CustomerChecker $customerChecker,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_sourceStatus = $sourceStatus;
        $this->customerChecker = $customerChecker;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->getAnswer()->getId();
    }

    /**
     * @return string
     */
    public function getQuestionId()
    {
        return $this->getAnswer()->getQuestionId();
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->getAnswer()->getContent();
    }

    /**
     * @return string
     */
    public function getCustomerId()
    {
        return $this->getAnswer()->getCustomerId();
    }

    /**
     * @return string
     */
    public function getAuthorName()
    {
        return $this->getAnswer()->getAuthorName();
    }

    /**
     * @return string
     */
    public function getAuthorEmail()
    {
        return $this->getAnswer()->getAuthorEmail();
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return !!$this->getAnswer()->getIsAdmin();
    }

    /**
     * @return string
     */
    public function getRating()
    {
        return $this->getAnswer()->getHelpfulness();
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->_sourceStatus->getOptionByValue($this->getAnswer()->getStatus());
    }

    /**
     * @return bool
     */
    public function isCanPublish()
    {
        return $this->getAnswer()->getStatus() != Status::APPROVED_VALUE;
    }

    /**
     * @return bool
     */
    public function isCanReject()
    {
        return $this->getAnswer()->getStatus() != Status::DECLINE_VALUE;
    }

    /**
     * @return string
     */
    public function getCustomerUrl()
    {
        return $this->getUrl('customer/index/edit', ['id' => $this->getAnswer()->getCustomerId()]);
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('productquestion/answer/delete', ['id' => $this->getAnswer()->getId()]);
    }

    /**
     * @return string
     */
    public function getRejectUrl()
    {
        return $this->getUrl(
            'productquestion/answer/changeStatus',
            [
                'id' => $this->getAnswer()->getId(),
                'status_id' => Status::DECLINE_VALUE
            ]
        );
    }

    /**
     * @return string
     */
    public function getPublishUrl()
    {
        return $this->getUrl('productquestion/answer/save', ['id' => $this->getAnswer()->getId()]);
    }

    /**
     * @return bool
     */
    public function checkCustomerExist()
    {
        return $this->getCustomerId()
            && $this->customerChecker->checkCustomerExistByEmail($this->getAnswer()->getAuthorEmail());
    }
}
