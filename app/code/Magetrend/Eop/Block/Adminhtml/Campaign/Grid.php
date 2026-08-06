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
namespace Magetrend\Eop\Block\Adminhtml\Campaign;

/**
 * Campaign backend grid widget block
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Grid extends \Magento\Backend\Block\Widget\Grid
{
    /**
     * Prepare grid column
     *
     * @return mixed
     */
    public function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        return parent::_prepareColumns();
    }

    public function _prepareCollection()
    {
        parent::_prepareCollection();
        $this->getCollection()
            ->addPopupRelation();
        return $this;
    }

    /**
     * Returns campaign grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('eop/*/grid', ['_current' => true]);
    }

    /**
     * Returns campaign edit url
     *
     * @param $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'eop/*/edit',
            ['id' => $row->getId()]
        );
    }
}
