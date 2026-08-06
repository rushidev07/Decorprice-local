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

namespace Magetrend\Eop\Block\Popup\Field;

/**
 * Additional field checkbox block class
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Checkbox extends \Magetrend\Eop\Block\Popup\Field
{
    /**
     * Template file path
     *
     * @var string
     */
    //@codingStandardsIgnoreLine
    protected $_template = 'eop/popup/field/checkbox.phtml';

    public function getJsonErrorMessage()
    {
        $data = $this->getData();
        $jsonErrorMessage = [];
        foreach ($data as $key => $value) {
            if (strpos($key, 'error_message_') === false) {
                continue;
            }
            $jsonErrorMessage[str_replace('error_message_', '', $key)] = $value;
        }

        return $this->jsonHelper->jsonEncode($jsonErrorMessage);
    }
}
