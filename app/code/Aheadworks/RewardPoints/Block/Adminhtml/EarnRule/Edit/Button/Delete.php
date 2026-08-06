<?php
namespace Aheadworks\RewardPoints\Block\Adminhtml\EarnRule\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class Delete
 * @package Aheadworks\RewardPoints\Block\Adminhtml\EarnRule\Edit\Button
 * @codeCoverageIgnore
 */
class Delete extends AbstractButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        $ruleId = $this->context->getRequest()->getParam('id');
        if ($ruleId) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => "deleteConfirm('" . __('Are you sure you want to do this?') .
                    "', '" . $this->getUrl('*/*/delete', ['id' => $ruleId]) . "')",
                'sort_order' => 20,
            ];
        }
        return $data;
    }
}
