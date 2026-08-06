<?php
namespace Aheadworks\RewardPoints\Ui\Component\Form\Field\EarnRule;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Store
 *
 * @package Aheadworks\RewardPoints\Ui\Component\Form\Field\EarnRule
 * @codeCoverageIgnore
 */
class Store extends Select
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ContextInterface $context
     * @param StoreManagerInterface $storeManager
     * @param array|null $options
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        StoreManagerInterface $storeManager,
        $options = null,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $options, $components, $data);
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        if ($this->isNeedToHideStore()) {
            $this->hideStore();
        }
        parent::prepare();
    }

    /**
     * Check if need to hide store field
     *
     * @return bool
     */
    private function isNeedToHideStore()
    {
        return $this->storeManager->isSingleStoreMode();
    }

    /**
     * Hide store field
     */
    private function hideStore()
    {
        $config = $this->getConfig();
        $config['visible'] = false;
        $this->setConfig($config);
    }
}
