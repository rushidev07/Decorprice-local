<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Controller\Adminhtml\Question;

use Aheadworks\Pquestion\Model\Source\Question\Sharing\Type as SharingType;
use Aheadworks\Pquestion\Controller\Adminhtml\Question;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Filter\FilterManager;
use Magento\Backend\App\Action\Context;
use Aheadworks\Pquestion\Model\AnswerFactory;
use Aheadworks\Pquestion\Model\Answer;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Aheadworks\Pquestion\Model\Source\Question\Status;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Pquestion\Model\DateTime\Formatter as DateTimeFormatter;

/**
 * Class Save
 * @package Aheadworks\Pquestion\Controller\Adminhtml\Question
 */
class Save extends Question
{
    /**
     * @var DateTimeFormatter
     */
    private $dateTimeFormatter;

    /**
     * @var AnswerFactory
     */
    private $answerFactory;

    /**
     * @var AuthSession
     */
    private $authSession;

    /**
     * @param Context $context
     * @param FilterManager $filterManager
     * @param DateTimeFormatter $dateTimeFormatter
     * @param AnswerFactory $answerFactory
     * @param AuthSession $authSession
     */
    public function __construct(
        Context $context,
        FilterManager $filterManager,
        DateTimeFormatter $dateTimeFormatter,
        AnswerFactory $answerFactory,
        AuthSession $authSession
    ) {
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->answerFactory = $answerFactory;
        $this->authSession = $authSession;
        parent::__construct($context, $filterManager);
    }

    /**
     * Saving edited question information
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($formData = array_filter($this->getRequest()->getPostValue())) {
            try {
                $preparedData = $this->getPreparedData($formData['data']);
                $questionModel = $this->_initQuestion();

                $questionModel->addData($preparedData);
                $questionModel->save();

                $this->saveAnswerList($formData['data'], $questionModel->getId());

                $this->messageManager->addSuccess(
                    __('Question %1 saved successfully.', $this->getQuestionEditLink())
                );
                $this->_getSession()->setPQFormData(null);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        ['id' => $questionModel->getId(), 'tab' => $this->getRequest()->getParam('tab', null)]
                    );
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_getSession()->setPQFormData($formData);

                return $resultRedirect->setPath(
                    '*/*/edit',
                    [
                        'id'          => $this->getRequest()->getParam('id', null),
                        'customer_id' => $this->getRequest()->getParam('customer_id', null),
                        'product_id'  => $this->getRequest()->getParam('product_id', null),
                        'tab'         => $this->getRequest()->getParam('tab', null)
                    ]
                );
            }
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Saving edited answer information
     *
     * @param mixed $data
     * @param mixed $questionId
     *
     * @return $this
     */
    private function saveAnswerList(&$data, $questionId)
    {
        if (array_key_exists('answer', $data)) {
            foreach ($data['answer'] as $answerId => $answerData) {
                /** @var Answer $answerModel */
                $answerModel = $this->answerFactory->create();
                $answerModel->load($answerId);
                if (null === $answerModel) {
                    continue;
                }
                $answerModel->addData($answerData);
                $answerModel->save();
            }
            unset($data['answer']);
        }
        if (array_key_exists('new_answer', $data)
            && array_key_exists('content', $data['new_answer'])
            && !empty(trim($data['new_answer']['content']))
        ) {
            /** @var Answer $answerModel */
            $answerModel = $this->answerFactory->create();
            $adminSessionUser = $this->authSession->getUser();
            $answerModel->setIsAdmin(true);
            $currentDate = new \Zend_Date;
            $answerModel
                ->setAuthorName(
                    trim($adminSessionUser->getFirstname() . ' ' . $adminSessionUser->getLastname())
                )
                ->setStatus(Status::APPROVED_VALUE)
                ->setCreatedAt($currentDate->toString(DateTime::DATETIME_INTERNAL_FORMAT))
                ->setAuthorEmail($adminSessionUser->getEmail())
                ->setCustomerId(0)
                ->setHelpfulness(0)
            ;
            $answerModel->addData($data['new_answer']);
            $answerModel->setQuestionId($questionId);
            $answerModel->save();
            unset($data['new_answer']);
        }

        return $this;
    }

    /**
     * Prepare data before save
     *
     * @param array $data
     * @return array
     * @throws LocalizedException
     */
    private function getPreparedData($data)
    {
        if (isset($data['created_at'])) {
            try {
                $data['created_at'] = $this->dateTimeFormatter->getDateTimeInDbFormat(
                    $data['created_at'],
                    $this->_localeResolver->getLocale()
                );
            } catch (\Exception $exception) {
                throw new LocalizedException(__('Please enter a valid date for "Created At" field'));
            }
        }
        if ($statusId = $this->_request->getParam('status_id', false)) {
            $data['status'] = $statusId;
        }
        if ($data['sharing_type'] == SharingType::SPECIFIED_PRODUCTS_VALUE) {
            $data['sharing_value'] = $this->getRequest()->getParam('parameters', []);
            $data['product_id'] = "";
        } else if ($data['sharing_type'] == SharingType::ORIGINAL_PRODUCT_VALUE) {
            $data['sharing_value'] = $data['product_id'] ? [$data['product_id']] : [];
        } else if ($data['sharing_type'] == SharingType::ALL_PRODUCTS_VALUE) {
            $data['product_id'] = "";
        }
        unset($data['entity_id']);

        return $data;
    }
}
