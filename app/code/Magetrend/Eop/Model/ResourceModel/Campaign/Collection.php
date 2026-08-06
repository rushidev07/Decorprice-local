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

namespace Magetrend\Eop\Model\ResourceModel\Campaign;

/**
 * Campaign Resource Collection
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
     * Define resource model
     *
     * @return void
     */
    //@codingStandardsIgnoreLine
    protected function _construct()
    {
        $this->_init('Magetrend\Eop\Model\Campaign', 'Magetrend\Eop\Model\ResourceModel\Campaign');
    }

    /**
     * Filter collection by page id
     *
     * @param string $pageId
     * @return $this
     */
    public function addPageIdFilter($pageId)
    {
        $this->getSelect()
            ->join(
                ['pg' => $this->getTable('mt_eop_campaign_page')],
                "main_table.entity_id = pg.campaign_id AND (pg.page_id='{$pageId}' OR pg.page_id='all' OR page_id = 's_{$pageId}')",
                ['']
            );
        return $this;
    }

    /**
     * Filter collection by store id
     *
     * @param $storeId
     * @return $this
     */
    public function addStoreIdFilter($storeId)
    {
        $this->getSelect()
            ->join(
                ['st' => $this->getTable('mt_eop_campaign_store')],
                "main_table.entity_id = st.campaign_id AND (st.store_id='{$storeId}' OR st.store_id=0)",
                ['']
            );
        return $this;
    }

    /**
     * Filter collection by current date
     *
     * @return $this
     */
    public function addDateFilter()
    {
        $date = date('Y-m-d H:i:s');
        $this->getSelect()
            ->where("main_table.start_date <= '{$date}' OR main_table.start_date is null")
            ->where("main_table.end_date >= '{$date}' OR main_table.end_date is null");

        return $this;
    }

    /**
     * Group collection by id
     *
     * @return $this
     */
    public function groupById()
    {
        //@codingStandardsIgnoreStart
        $this->getSelect()
            ->group('main_table.entity_id');
        //@codingStandardsIgnoreEnd
        return $this;
    }

    /**
     * Assign campaign popup
     *
     * @return $this
     */
    public function addPopupRelation()
    {
        $this->getSelect()
            ->joinLeft(
                ['p' => $this->getTable('mt_eop_popup')],
                "main_table.popup_id = p.entity_id",
                ['popup_name' => 'p.name']
            );
        return $this;
    }
}
