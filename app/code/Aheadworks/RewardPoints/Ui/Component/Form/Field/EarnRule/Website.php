<?php
namespace Aheadworks\RewardPoints\Ui\Component\Form\Field\EarnRule;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Website
 * @package Aheadworks\RewardPoints\Ui\Component\Form\Field\EarnRule
 * @codeCoverageIgnore
 */
class Website extends Field
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        if ($this->isNeedToHideWebsite()) {
            $this->hideWebsite();
        }
        parent::prepare();
    }

    /**
     * Check if need to hide website field
     *
     * @return bool
     */
    private function isNeedToHideWebsite()
    {
        return $this->storeManager->isSingleStoreMode();
    }

    /**
     * Hide website field
     */
    private function hideWebsite()
    {
        $config = $this->getConfig();
        $config['visible'] = false;
        $this->setConfig($config);
    }
}
