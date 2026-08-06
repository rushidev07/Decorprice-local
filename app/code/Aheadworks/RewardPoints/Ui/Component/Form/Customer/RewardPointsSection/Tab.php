<?php
namespace Aheadworks\RewardPoints\Ui\Component\Form\Customer\RewardPointsSection;

use Magento\Framework\View\Element\ComponentVisibilityInterface;

/**
 * Class Tab
 *
 * @package Aheadworks\RewardPoints\Ui\Component\Form\Customer\RewardPointsSection
 */
class Tab extends AclResourceFieldset implements ComponentVisibilityInterface
{
    /**
     * @inheridoc
     */
    public function isComponentVisible(): bool
    {
        $customerId = $this->context->getRequestParam('id');
        return (bool)$customerId
            && parent::isComponentVisible();
    }
}
