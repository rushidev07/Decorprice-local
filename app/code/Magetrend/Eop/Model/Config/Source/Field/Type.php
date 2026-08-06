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

namespace Magetrend\Eop\Model\Config\Source\Field;

/**
 * Additional field values source class
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Type implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Returns field types
     *
     * @return array
     */
    public function getAll()
    {
        return [
            [
                'label' => 'Text',
                'renderer' => '\Magetrend\Eop\Block\Adminhtml\Popup\Edit\Tab\Field\Type\Text',
                'name' => 'text',
                'types' => [
                    ['value' => 'field',  'label' => __('Text Field')],
                    ['value' => 'area',    'label' => __('Textarea')],
                ]
            ],
            [
                'name' => 'select',
                'label' => 'Select',
                'renderer' => '\Magetrend\Eop\Block\Adminhtml\Popup\Edit\Tab\Field\Type\Select',
                'types' => [
                    ['value' => 'drop_down',    'label' => __('Drop Down')],
                ]
            ],
            [
                'name' => 'checkbox',
                'label' => 'Checkbox',
                'renderer' => '\Magetrend\Eop\Block\Adminhtml\Popup\Edit\Tab\Field\Type\Checkbox',
                'types' => [
                    ['value' => 'checkbox',    'label' => __('Checkbox')],
                ]
            ],
            [
                'name' => 'hidden',
                'label' => 'Hidden',
                'renderer' => '\Magetrend\Eop\Block\Adminhtml\Popup\Edit\Tab\Field\Type\Hidden',
                'types' => [
                    ['value' => 'hidden',    'label' => __('Hidden')],
                ]
            ],
        ];
    }

    /**
     * Returns field types as array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        $groups = [['value' => '', 'label' => __('-- Please select --')]];
        foreach ($this->getAll() as $option) {
            $types = [];
            if (isset($option['types'])) {
                foreach ($option['types'] as $type) {
                    $types[] = ['label' => __($type['label']), 'value' => $type['value']];
                }
            }
            $groups[] = ['label' => __($option['label']), 'value' => $types];
        }
        return $groups;
    }
}
