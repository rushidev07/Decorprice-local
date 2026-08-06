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

namespace Magetrend\Eop\Plugin\Newsletter\Block\Adminhtml\Subscriber;

use \Magento\Newsletter\Block\Adminhtml\Subscriber\Grid;

/**
 * Backend subscriber grid plugin
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class GridPlugin
{

    public $ignoreAdditionalField = [
        'firstname' => 1,
        'lastname' => 1
    ];

    public $updateRenderer = [
        'firstname' => 'Magetrend\Eop\Block\Adminhtml\Newsletter\Subscriber\Grid\Column\Renderer\Firstname',
        'lastname' => 'Magetrend\Eop\Block\Adminhtml\Newsletter\Subscriber\Grid\Column\Renderer\Lastname'
    ];

    public $fieldCollectionFactory;

    public $moduleHelper;

    private $fieldCollection = null;

    private $columnCount = 4;

    /**
     * GridPlugin constructor.
     *
     * @param \Magetrend\Eop\Model\ResourceModel\Field\CollectionFactory $collectionFactory
     * @param \Magetrend\Eop\Helper\Data $helper
     */
    public function __construct(
        \Magetrend\Eop\Model\ResourceModel\Field\CollectionFactory $collectionFactory,
        \Magetrend\Eop\Helper\Data $helper
    ) {
        $this->moduleHelper = $helper;
        $this->fieldCollectionFactory = $collectionFactory;
    }

    /**
     * Before susbscriber grid block to html
     *
     * @param Grid $subject
     */
    public function beforeToHtml(Grid $subject)
    {
        $this->addAdditionFieldColumns($subject);
        $this->updateRenderer($subject, 'firstname');
        $this->updateRenderer($subject, 'lastname');
    }

    /**
     * Add additional columns
     *
     * @param Grid $subject
     * @return bool
     */
    public function addAdditionFieldColumns(Grid $subject)
    {
        if (!$this->moduleHelper->isActive()) {
            return false;
        }

        $fieldCollection = $this->getFieldCollection();
        if (!empty($fieldCollection)) {
            $addedFields = [];
            foreach ($fieldCollection as $field) {
                if (!isset($addedFields[$field->getName()])) {
                    $this->addAdditionalColumn($subject, $field->getLabel(), $field->getName(), $field->getType());
                    $addedFields[$field->getName()] = 1;
                }
            }
        }

        $this->addAdditionalColumn(
            $subject,
            'Exit Offer Code',
            \Magetrend\Eop\Model\Popup::DISCOUNT_CODE_FIELD
        );

        $columnBlock = $subject->getLayout()
            ->createBlock('Magento\Backend\Block\Widget\Grid\Column')
            ->setData(
                [
                    'header' => __('Created At'),
                    'index' => 'created_at',
                    'type' => 'date',
                    'header_css_class' => 'col-id',
                    'column_css_class' => 'col-id',
                ]
            );

        $columnSet = $subject->getColumnSet();
        $columnSet->insert(
            $columnBlock,
            ++$this->columnCount,
            'created_at'
        );

        return true;
    }

    /**
     * Update Renderer
     *
     * @param Grid $subject
     * @param $name
     */
    private function updateRenderer(Grid $subject, $name)
    {
        if (!isset($this->updateRenderer[$name])) {
            return;
        }
        $columnList = $subject->getColumnSet()->getColumns();

        if (!isset($columnList[$name])) {
            return;
        }
        $column = $columnList[$name];
        $column->setRendererType($name, $this->updateRenderer[$name]);
        $column->setType($name);
    }

    /**
     * Add additional fields columns
     * @param Grid $subject
     * @param $label
     * @param $name
     * @param $type
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addAdditionalColumn(Grid $subject, $label, $name, $type = 'text')
    {
        if (isset($this->ignoreAdditionalField[$name])) {
            return;
        }
        $index = 'subscriber_'.$name;
        if ($name == \Magetrend\Eop\Model\Popup::DISCOUNT_CODE_FIELD) {
            $index = $name;
        }

        $options = [
            'header' => __($label),
            'index' => $index,
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id',
        ];

        if ($type == 'checkbox') {
            $options['type'] = 'options';
            $options['options'] = [
                [
                    'label' => (string)__('Not Checked'),
                    'value' => 0,
                ],
                [
                    'label' => (string)__('Not Checked'),
                    'value' => '',
                ],
                [
                    'label' => (string)__('Checked'),
                    'value' => 1,
                ]
            ];
        }

        $columnBlock = $subject->getLayout()
            ->createBlock('Magento\Backend\Block\Widget\Grid\Column')
            ->setData($options);

        $columnSet = $subject->getColumnSet();
        //@codingStandardsIgnoreStart
        $columnSet->insert(
            $columnBlock,
            ++$this->columnCount,
            $name
        );
        //@codingStandardsIgnoreEnd
    }

    /**
     * Returns Additional fields collection
     * @return \Magetrend\Eop\Model\ResourceModel\Field\Collection|null
     */
    public function getFieldCollection()
    {
        if ($this->fieldCollection === null) {

            $collection = $this->fieldCollectionFactory->create()
                ->addPopupTypeFilter(\Magetrend\Eop\Model\Popup::TYPE_NEWSLETTER_SUBSCRIPTION);
            $this->fieldCollection = $collection;
        }

        return $this->fieldCollection;
    }
}
