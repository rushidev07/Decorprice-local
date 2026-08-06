<?php
namespace Aheadworks\RewardPoints\Block\Adminhtml\EarnRule\Edit\Button;

use Magento\Backend\Block\Widget\Context;

/**
 * Class AbstractButton
 * @package Aheadworks\RewardPoints\Block\Adminhtml\EarnRule\Edit\Button
 * @codeCoverageIgnore
 */
abstract class AbstractButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
