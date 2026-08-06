<?php
namespace Aheadworks\RewardPoints\Block\Adminhtml\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;
use Aheadworks\RewardPoints\Model\ThirdPartyModule\Manager as ThirdPartyModuleManager;

/**
 * Class Subscription
 *
 * @package Aheadworks\RewardPoints\Block\Adminhtml\Form\Field
 */
class Subscription extends Field
{
    /**
     * @var ThirdPartyModuleManager
     */
    private $thirdPartyModuleManager;

    /**
     * @param Context $context
     * @param ThirdPartyModuleManager $thirdPartyModuleManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        ThirdPartyModuleManager $thirdPartyModuleManager,
        array $data = []
    ) {
        $this->thirdPartyModuleManager = $thirdPartyModuleManager;
        parent::__construct($context, $data);
    }

    /**
     * Display field if SARP2 is enabled
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        return $this->thirdPartyModuleManager->isSarp2ModuleEnabled() ? parent::render($element) :  '';
    }
}
