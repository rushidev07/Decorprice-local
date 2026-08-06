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
 * Popup content block
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Content extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magetrend\Eop\Helper\Data|null
     */
    public $helper = null;

    /**
     * Content constructor.
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
     * It will return helper
     * @return \Magetrend\Eop\Helper\Data
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
        return $this->getParentBlock()->getPopup();
    }

    /**
     * It will return html output of input field
     * @param array $field
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFieldHtml(array $field)
    {
        $blockClass = '';
        switch ($field['type']) {
            case 'field':
                $blockClass = 'Magetrend\Eop\Block\Popup\Field\Text';
                break;
            case 'area':
                $blockClass = 'Magetrend\Eop\Block\Popup\Field\Textarea';
                break;
            case 'radio':
                $blockClass = 'Magetrend\Eop\Block\Popup\Field\Radio';
                break;
            case 'checkbox':
                $blockClass = 'Magetrend\Eop\Block\Popup\Field\Checkbox';
                break;
            case 'drop_down':
                $blockClass = 'Magetrend\Eop\Block\Popup\Field\Select';
                break;
            case 'hidden':
                $blockClass = 'Magetrend\Eop\Block\Popup\Field\Hidden';
                break;
        }

        if (empty($blockClass)) {
            return '';
        }

        $block = $this->getLayout()->createBlock($blockClass)->setData($field);
        return $block->toHtml();
    }

    /**
     * It will return additional field list as array
     * @return array|null
     */
    public function getAdditionalFields()
    {
        return $this->getPopup()->getAdditionalFields();
    }

    /**
     * Is only on visible checkbox
     * if yes, we can add it after email field and keep submit button in the same line as email field
     * @return bool
     */
    public function isOnlyOneVisibleCheckbox()
    {
        $additionalFields = $this->getAdditionalFields();
        if (empty($additionalFields)) {
            return false;
        }

        $counter = 0;
        foreach ($additionalFields as $field) {
            if ($field['type'] == 'hidden') {
                continue;
            }

            if ($field['type'] == 'checkbox') {
                $counter++;
                continue;
            }

            return false;
        }

        return $counter == 1;
    }

    /**
     * It returns color code
     * @param $id
     * @return string
     */
    public function getColor($id)
    {
        return $this->getParentBlock()->getColor($id);
    }

    /**
     * It returns popup text
     * @param $id
     * @return string
     */
    public function getText($id)
    {
        return $this->getParentBlock()->getText($id);
    }
}
