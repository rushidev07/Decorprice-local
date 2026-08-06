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

namespace Magetrend\Eop\Block\Adminhtml\Popup\Edit\Tab\Field\Type;

use \Magento\Backend\Block\Widget;

/**
 * Backend additional field abstract class
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class AbstractType extends Widget
{
    /**
     * @var \Magento\Catalog\Model\Config\Source\Product\Options\Price
     */
    public $optionPrice;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\Config\Source\Product\Options\Price $optionPrice
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\Config\Source\Product\Options\Price $optionPrice,
        array $data = []
    ) {
        $this->optionPrice = $optionPrice;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    //@codingStandardsIgnoreLine
    protected function _prepareLayout()
    {
        $this->setChild(
            'option_price_type',
            $this->getLayout()->addBlock(
                'Magento\Framework\View\Element\Html\Select',
                $this->getNameInLayout() . '.option_price_type',
                $this->getNameInLayout()
            )->setData(
                [
                    'id' => 'product_option_<%- data.option_id %>_price_type',
                    'class' => 'select product-option-price-type',
                ]
            )
        );

        $this->getChildBlock(
            'option_price_type'
        )->setName(
            'product[options][<%- data.option_id %>][price_type]'
        )->setOptions(
            $this->optionPrice->toOptionArray()
        );

        return parent::_prepareLayout();
    }

    /**
     * Get html of Price Type select element
     *
     * @return string
     */
    public function getPriceTypeSelectHtml()
    {
        if ($this->getCanEditPrice() === false) {
            $this->getChildBlock('option_price_type')->setExtraParams('disabled="disabled"');
        }
        return $this->getChildHtml('option_price_type');
    }
}
