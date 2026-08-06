<?php
namespace Aheadworks\RewardPoints\Ui\Component\Form\Field\EarnRule;

use Magento\Ui\Component\Container;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class LabelsDynamicRows
 *
 * @package Aheadworks\RewardPoints\Ui\Component\Form\Field\EarnRule
 * @codeCoverageIgnore
 */
class LabelsDynamicRows extends Container
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ContextInterface $context
     * @param StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        if ($this->isNeedToDisableAddingRows()) {
            $this->disableAddingRows();
        }
        parent::prepare();
    }

    /**
     * Check if need to disable adding new rows
     *
     * @return bool
     */
    private function isNeedToDisableAddingRows()
    {
        return $this->storeManager->isSingleStoreMode();
    }

    /**
     * Disable adding new rows
     */
    private function disableAddingRows()
    {
        $config = $this->getConfig();
        $config['addButton'] = false;
        $this->setConfig($config);
    }
}
