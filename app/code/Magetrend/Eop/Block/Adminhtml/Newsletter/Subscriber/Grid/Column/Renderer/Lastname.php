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

namespace Magetrend\Eop\Block\Adminhtml\Newsletter\Subscriber\Grid\Column\Renderer;

use Magento\Framework\DataObject;

/**
 * Newsletter subscribers last name coulumn renderer
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Lastname extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * Renders grid column
     *
     * @param DataObject $row
     * @return mixed|string
     */
    public function render(DataObject $row)
    {
        if ($row->getData('customer_lastname') == '' && $row->getData('subscriber_lastname') != '') {
            return $row->getData('subscriber_lastname');
        }

        return parent::render($row);
    }
}
