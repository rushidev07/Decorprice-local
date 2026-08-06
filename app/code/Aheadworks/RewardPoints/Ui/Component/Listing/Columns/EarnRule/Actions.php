<?php
namespace Aheadworks\RewardPoints\Ui\Component\Listing\Columns\EarnRule;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Actions
 * @package Aheadworks\RewardPoints\Ui\Component\Listing\Columns\EarnRule
 * @codeCoverageIgnore
 */
class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = [
                    'enable' => $this->getActionItem(
                        'Edit',
                        'aw_reward_points/earning_rules/edit',
                        [
                            'id' => $item['id']
                        ]
                    ),
                    'delete' => $this->getActionItem(
                        'Delete',
                        'aw_reward_points/earning_rules/delete',
                        [
                            'id' => $item['id']
                        ],
                        [
                            'confirm' => [
                                'title' => __('Delete'),
                                'message' => __("Are you sure you want to delete selected item?")
                            ]
                        ]
                    )
                ];
            }
        }
        return $dataSource;
    }

    /**
     * Get action item
     *
     * @param string $label
     * @param string $path
     * @param array $params
     * @param array $additionalParams
     * @return array
     */
    private function getActionItem($label, $path, $params, $additionalParams = [])
    {
        $actionItem = [
            'href' => $this->urlBuilder->getUrl(
                $path,
                $params
            ),
            'label' => __($label),
        ];
        $actionItem = array_merge($actionItem, $additionalParams);

        return $actionItem;
    }
}
