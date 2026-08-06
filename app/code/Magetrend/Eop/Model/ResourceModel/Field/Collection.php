<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.3 or later
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */

namespace Magetrend\Eop\Model\ResourceModel\Field;

/**
 * Field Resource Collection
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var String
     */
    public $fieldOptionTable;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $jsonHelper;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->jsonHelper = $jsonHelper;
        return parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    //@codingStandardsIgnoreLine
    protected function _construct()
    {
        $this->_init('Magetrend\Eop\Model\Field', 'Magetrend\Eop\Model\ResourceModel\Field');
    }

    /**
     * Join field-options table
     *
     * @return $this
     */
    public function joinOptionCollection()
    {
        $this->getSelect()->joinLeft(
            ['fo' => $this->getFieldOptionTable()],
            'fo.field_id = main_table.entity_id',
            [
                'option_id' => 'fo.entity_id',
                'option_value' => 'fo.value',
                'option_label' => 'fo.label',
                'option_position' => 'fo.position',
            ]
        );
        return $this;
    }

    /**
     * Group collection by name
     *
     * @return $this
     */
    public function groupByName()
    {
        //@codingStandardsIgnoreStart
        $this->getSelect()->group('main_table.name');
        //@codingStandardsIgnoreEnd
        return $this;
    }

    /**
     * Sort collection by possition
     *
     * @return $this
     */
    public function sortByPositionCollection()
    {
        $this->getSelect()->order([
            'main_table.position ASC',
            'fo.position ASC'
        ]);
        return $this;
    }

    /**
     * Filter fields by popup ID
     *
     * @param int $popupId
     * @return $this
     */
    public function setPopupFilter($popupId)
    {
        $this->addFieldToFilter('main_table.popup_id', $popupId);
        return $this;
    }

    /**
     * Returns field options table name
     *
     * @return string
     */
    public function getFieldOptionTable()
    {
        if (empty($this->fieldOptionTable)) {
            $this->fieldOptionTable = $this->getTable('mt_eop_field_option');
        }
        return $this->fieldOptionTable;
    }

    /**
     * Group data
     *
     * @return array
     */
    public function getGroupedData()
    {
        $groupedData = [];
        $data = $this->getData();
        if (!empty($data)) {
            foreach ($data as $item) {
                if (!isset($groupedData[$item['entity_id']])) {
                    $groupedData[$item['entity_id']] = [
                        'id' => $item['entity_id'],
                        'option_id' => $item['entity_id'],
                        'popup_id' => $item['popup_id'],
                        'type' => $item['type'],
                        'name' => $item['name'],
                        'label' => $item['label'],
                        'default_value' => $item['default_value'],
                        'frontend_label' => $item['frontend_label'],
                        'is_require' => $item['is_required'],
                        'after_email_field' => $item['after_email_field'],
                        'sort_order' => $item['position'],
                    ];

                    if (isset($item['error_message']) && !empty($item['error_message'])) {
                        $item['error_message'] = $this->jsonHelper->jsonDecode($item['error_message']);
                        if (!empty($item['error_message'])) {
                            foreach ($item['error_message'] as $key => $message) {
                                $groupedData[$item['entity_id']]['error_message_'.$key] = $message;
                            }
                        }
                    }
                }

                if (isset($item['entity_id']) && $item['option_id'] > 0) {
                    if (isset($groupedData[$item['entity_id']]['item_count'])) {
                        $groupedData[$item['entity_id']]['item_count']++;
                    } else {
                        $groupedData[$item['entity_id']]['item_count'] = 1;
                    }

                    $groupedData[$item['entity_id']]['optionValues'][] = [
                        'item_count' => $groupedData[$item['entity_id']]['item_count'],
                        'option_id' => $item['entity_id'],
                        'option_type_id' => $item['option_id'],
                        'value' => $item['option_value'],
                        'label' => $item['option_label'],
                        'sort_order' => $item['option_position'],
                    ];
                }
            }
        }
        return $groupedData;
    }

    /**
     * Filter by popup type
     * @param $type
     * @return $this
     */
    public function addPopupTypeFilter($type)
    {
        $this->getSelect()
            ->join(
                ['popup' => 'mt_eop_popup'],
                "main_table.popup_id=popup.entity_id",
                []
            );
        $this->addFieldToFilter('popup.content_type', $type);
        return $this;
    }
}
