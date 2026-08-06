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

namespace Magetrend\Eop\Block\Adminhtml;

/**
 * Backend campaign grid container block
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Campaign extends \Magento\Backend\Block\Widget\Grid\Container
{
    public $contentType;

    /**
     * Campaign constructor.
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magetrend\Eop\Model\Config\Source\Type $contentType
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magetrend\Eop\Model\Config\Source\Type $contentType,
        array $data = []
    ) {
        $this->contentType = $contentType;
        parent::__construct($context, $data);
    }

    /**
     * Initialize object state with incoming parameters
     *
     * @return void
     */
    //@codingStandardsIgnoreLine
    protected function _construct()
    {
        $this->_controller = 'campaign_index';
        $this->_headerText = __('Manage Campaign');
        $this->_addButtonLabel = __('Create New Campaign');
        parent::_construct();
    }
}
