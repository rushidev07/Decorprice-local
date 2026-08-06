<?php
namespace Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules;

use Aheadworks\RewardPoints\Api\EarnRuleManagementInterface;
use Aheadworks\RewardPoints\Model\Data\ProcessorInterface;
use Aheadworks\RewardPoints\Ui\DataProvider\EarnRule\FormDataProvider as RuleFormDataProvider;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Save
 * @package Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules
 */
class Save extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_RewardPoints::aw_reward_points_earning_rules';

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var ProcessorInterface
     */
    private $postDataProcessor;

    /**
     * @var EarnRuleManagementInterface
     */
    private $ruleManagement;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param ProcessorInterface $postDataProcessor
     * @param EarnRuleManagementInterface $ruleManagement
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        ProcessorInterface $postDataProcessor,
        EarnRuleManagementInterface $ruleManagement
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->postDataProcessor = $postDataProcessor;
        $this->ruleManagement = $ruleManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var ResultRedirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $postData = $this->getRequest()->getPostValue();
        if ($postData) {
            try {
                $ruleId = isset($postData['id']) ? $postData['id'] : false;
                $data = $this->postDataProcessor->process($postData);

                $rule = $ruleId
                    ? $this->ruleManagement->updateRule($ruleId, $data)
                    : $this->ruleManagement->createRule($data);

                $this->dataPersistor->clear(RuleFormDataProvider::DATA_PERSISTOR_FORM_DATA_KEY);
                $this->messageManager->addSuccessMessage(__('Rule was saved successfully'));

                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $rule->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the rule'));
            }
            $this->dataPersistor->set(RuleFormDataProvider::DATA_PERSISTOR_FORM_DATA_KEY, $postData);
            if ($ruleId) {
                return $resultRedirect->setPath('*/*/edit', ['id' => $ruleId, '_current' => true]);
            }
            return $resultRedirect->setPath('*/*/new', ['_current' => true]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
