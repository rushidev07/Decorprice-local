<?php
namespace Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules;

use Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\Condition\Factory as RuleConditionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

/**
 * Class NewConditionHtml
 * @package Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules
 */
class NewConditionHtml extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_RewardPoints::aw_reward_points_earning_rules';

    /**
     * Default value of prefix for conditions
     */
    const DEFAULT_CONDITIONS_PREFIX = 'conditions';

    /**
     * @var RuleConditionFactory
     */
    private $conditionFactory;

    /**
     * @param Context $context
     * @param RuleConditionFactory $conditionFactory
     */
    public function __construct(
        Context $context,
        RuleConditionFactory $conditionFactory
    ) {
        parent::__construct($context);
        $this->conditionFactory = $conditionFactory;
    }

    /**
     * New condition html action
     *
     * @return void
     */
    public function execute()
    {
        $request = $this->getRequest();
        $id = $request->getParam('id');
        $prefix = $request->getParam('prefix', self::DEFAULT_CONDITIONS_PREFIX);
        $typeArr = explode('|', str_replace('-', '/', $request->getParam('type')));
        $type = $typeArr[0];
        $attribute = !empty($typeArr[1]) ? $typeArr[1] : null;
        $formName = $request->getParam('form_namespace');
        $jsFormObject = $request->getParam('form');

        try {
            $conditionModel = $this->conditionFactory->create(
                $type,
                $id,
                $prefix,
                $attribute,
                $jsFormObject,
                $formName
            );

            $html = $conditionModel->asHtmlRecursive();
        } catch (\Exception $e) {
            $html = '';
        }

        $this->getResponse()->setBody($html);
    }
}
