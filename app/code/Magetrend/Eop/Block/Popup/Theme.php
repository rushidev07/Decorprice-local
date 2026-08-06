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

namespace Magetrend\Eop\Block\Popup;

use Magetrend\Eop\Helper;

/**
 * Popup template block
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Theme extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magetrend\Eop\Helper\Data|null
     */
    public $helper;

    /**
     * @var \Magetrend\Eop\Model\Popup
     */
    private $popup = null;

    /**
     * Theme constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magetrend\Eop\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * It will return the module helper
     * @return \Magetrend\Eop\Helper\Data|null
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * It will return current campaign popup model
     * @return \Magetrend\Eop\Model\Popup
     */
    public function getPopup()
    {
        if ($this->popup == null) {
            $this->popup = $this->getParentBlock()->getPopup();
        }
        return $this->popup;
    }

    /**
     * It returns color code
     * @param $id
     * @return string
     */
    public function getColor($id)
    {
        $color = $this->getPopup()->getData('color_'.$id);
        return '#'.str_replace('#', '', $color);
    }

    /**
     * It returns popup text
     * @param $id
     * @return string
     */
    public function getText($id)
    {
        $text = $this->getPopup()->getData('text_'.$id);
        return $text;
    }
}
