<?php
namespace Aheadworks\RewardPoints\Block\Adminhtml\EarnRule\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class Back
 * @package Aheadworks\RewardPoints\Block\Adminhtml\EarnRule\Edit\Button
 * @codeCoverageIgnore
 */
class Back extends AbstractButton implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getUrl('*/*/')),
            'class' => 'back',
            'sort_order' => 10
        ];
    }
}
