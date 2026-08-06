<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_LayeredNavigation
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Weltpixel TEAM
 */

namespace Ahy\WeltpixelOverrides\Block\Navigation;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;

/**
 * Class FilterRenderer
 * @package WeltPixel\LayeredNavigation\Block\Navigation
 */
class FilterRenderer extends \Magento\LayeredNavigation\Block\Navigation\FilterRenderer
{
    /**
     * @var \WeltPixel\LayeredNavigation\Helper\Data
     */
    protected $_wpHelper;

    /**
     * @var \WeltPixel\LayeredNavigation\Model\AttributeOptions
     */
    protected $_attributeOptions;
    protected $_attributeId;
    protected $_attributeOptionsObj;

    protected $maxPrice;
    protected $minPrice;
    protected $maxPriceX;
    protected $minPriceX;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var array
     */
    protected $widthValue;

    /**
     * @var array
     */
    protected $heightValue;

    protected $maxWidth;
    protected $minWidth;
    protected $maxWidthX;
    protected $minWidthX;

    protected $maxHeight;
    protected $minHeight;
    protected $maxHeightX;
    protected $minHeightX;

    protected $isWidth = false;
    protected $isHeight = false;
    protected $isPrice = false;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_currency;

    /**
     * @var \Magento\Theme\Block\Html\Pager
     */
    private $htmlPagerBlock;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    public function __construct(
        ProductMetadataInterface $productMetadata,
        \WeltPixel\LayeredNavigation\Helper\Data $wpHelper,
        \WeltPixel\LayeredNavigation\Model\AttributeOptions $attributeOptions,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Pricing\PriceCurrencyInterface $currency,
        \Magento\Theme\Block\Html\Pager $htmlPagerBlock,
        Template\Context $context,
        array $data = []
    )
    {
        $this->productMetadata = $productMetadata;
        $this->_wpHelper = $wpHelper;
        $this->_attributeOptions = $attributeOptions;
        $this->_registry = $registry;
        $this->_currency = $currency;
        $this->htmlPagerBlock = $htmlPagerBlock;
        parent::__construct($context, $data);
    }

    public function render(FilterInterface $filter)
    {
        $this->filter = $filter;

        if ($this->_wpHelper->isEnabled()) {
            $sliderValue = $filter->getRequestVar();
            if (($filter->getRequestVar() == 'price' && $this->_wpHelper->getPriceIsSliderMode()) || ($filter->getRequestVar() == 'width' && $this->_wpHelper->getPriceIsSliderMode()) || ($filter->getRequestVar() == 'height' && $this->_wpHelper->getPriceIsSliderMode())) {
                if($filter->getRequestVar() == 'price'){
                    $this->isWidth  = false;
                    $this->isHeight = false;
                    $this->isPrice  = true;
                    $collection = $this->filter->getLayer()->getProductCollection();
                    $currentCatId = ($this->_registry->registry('current_category')) ? $this->_registry->registry('current_category')->getId() : "";
                    $basePriceData = $this->_registry->registry('price_filter');

                    if (is_array($basePriceData) && $currentCatId && array_key_exists($currentCatId, $basePriceData)) {
                        $this->minPriceX = $basePriceData[$currentCatId]['min'];
                        $this->maxPriceX = $basePriceData[$currentCatId]['max'];
                    } else {
                        $this->minPriceX = ($this->minPriceX == null) ? $collection->getMinPrice() : $this->minPriceX;
                        $this->maxPriceX = ($this->maxPriceX == null) ? $collection->getMaxPrice() : $this->maxPriceX;
                    }
                    $this->minPrice = ($this->minPrice == null) ? $collection->getMinPrice() : $this->minPrice;
                    $this->maxPrice = ($this->maxPrice == null) ? $collection->getMaxPrice() : $this->maxPrice;
                } elseif($sliderValue == 'width') {
                    $width = [];
                    $widthValue = [];
                    foreach ($this->filter->getItems() as $item) {
                        $label              = $item->getLabel();
                        $value              = $item->getValue();
                        $width[]            = $label;
                        $widthValue[$label] = $value;
                    }
                    $this->widthValue[] = $widthValue;
                    $width              = array_map('intval', $width);
                    $lowest             = min($width);
                    $highest            = max($width);
                    $this->minWidth     = $lowest;
                    $this->maxWidth     = $highest;
                    $this->minWidthX    = $lowest;
                    $this->maxWidthX    = $highest;
                    $this->isWidth      = true;
                    $this->isHeight     = false;
                    $this->isPrice      = false;

                } elseif($sliderValue == 'height'){
                    $width          = [];
                    $heightValue    = [];
                    foreach ($this->filter->getItems() as $item) {
                        $label                  = $item->getLabel();
                        $value                  = $item->getValue();
                        $height[]               = $label;
                        $heightValue[$label]    = $value;
                    }
                    $this->heightValue[] = $heightValue;
                    $height = array_map('intval', $height);
                    $lowest             = min($height);
                    $highest            = max($height);
                    $this->minHeight    = $lowest;
                    $this->maxHeight    = $highest;
                    $this->minHeightX   = $lowest;
                    $this->maxHeightX   = $highest;
                    $this->isHeight     = true;
                    $this->isWidth      = false;
                    $this->isPrice      = false;
                }
                $this->setTemplate('layer/slider.phtml');
            } else {
                $this->setTemplate('layer/filter.phtml');
            }
        }

        $html = parent::render($filter);
        return $html;
    }

    public function getPriceConfigData()
    {
        if($this->isPrice){
            $range = [
                'minX' => floor($this->minPriceX),
                'maxX' => ceil($this->maxPriceX),
                'min' => floor($this->minPrice),
                'max' => ceil($this->maxPrice),
                'step' => 1,
                'currency' => $this->_currency->getCurrencySymbol(),
                'isPrice' => true
            ];
        } elseif($this->isWidth) {
            $range = [
                'minX' => floor($this->minWidthX),
                'maxX' => ceil($this->maxWidthX),
                'min' => floor($this->minWidth),
                'max' => ceil($this->maxWidth),
                'step' => 1,
                'widthValue' => $this->widthValue,
                'currency' => "",
                'isPrice' => false,
                'isWidth' => true,
                'isHeight' => false
            ];
        } elseif($this->isHeight) {
            $range = [
                'minX' => floor($this->minHeightX),
                'maxX' => ceil($this->maxHeightX),
                'min' => floor($this->minHeight),
                'max' => ceil($this->maxHeight),
                'step' => 1,
                'heightValue' => $this->heightValue,
                'currency' => "",
                'isPrice' => false,
                'isWidth' => false,
                'isHeight' => true
            ];
        }

        return $range;
    }

    public function priceIsInputType()
    {
        return $this->_wpHelper->canShowPriceInput();
    }

    public function getSliderApplyUrl()
    {
        if(!$this->isWidth){
            $params = [
                'price' => $this->minPrice . '-' . $this->maxPrice,
                $this->htmlPagerBlock->getPageVarName() => null
            ];
        } else {
            $params = [
                'width' => $this->minWidth . '-' . $this->maxWidth,
                $this->htmlPagerBlock->getPageVarName() => null
            ];
        }
        $urlParams = [];
        $urlParams['_current'] = true;
        $urlParams['_escape'] = false;
        $urlParams['_use_rewrite'] = true;
        $urlParams['_query'] = $params;
        return $this->getUrl('*/*/*', $urlParams);
    }

    public function getPriceStep()
    {
        $step = $this->_wpHelper->getPriceRangeStep();
        if(!$this->isWidth){
            return ($step > $this->maxPrice) ? $this->maxPrice : $step;
        } else {
            return ($step > $this->maxWidth) ? $this->maxWidth : $step;
        }
    }

    public function setAttributeId($filter)
    {
        if ($filter->getRequestVar() == $this->_wpHelper->getCategoryParamLabel()) {
            $this->_attributeId = $this->_wpHelper->getCategoryParamLabel();
        } elseif ($filter->getRequestVar() == $this->_wpHelper->getRatingParamLabel()) {
            $this->_attributeId = $this->_wpHelper->getRatingParamLabel();
        } else {
            $this->_attributeId = $filter->getAttributeModel()->getAttributeId();
        }
    }

    public function getWpAttributeOptions()
    {
        $this->_attributeOptionsObj = ($this->_attributeId > 0) ? $this->_attributeOptions->getDisplayOptionsByAttribute($this->_attributeId) : '';
        return $this->_attributeOptionsObj;
    }

    public function getAttributeId()
    {
        return $this->_attributeId;
    }

    public function getVisibleItems()
    {
        return ($this->_attributeId > 0) ? $this->getWpAttributeOptions($this->_attributeId)->getVisibleOptions() : '';
    }

    public function getVisibleItemsStep()
    {
        return ($this->_attributeId > 0) ? $this->getWpAttributeOptions($this->_attributeId)->getVisibleOptionsStep() : '';
    }

    public function getShowQty()
    {
        if ($this->_attributeId == $this->_wpHelper->getRatingParamLabel()) {
            return $this->_wpHelper->getRatingFilterCounter();
        } elseif ($this->_attributeId == $this->_wpHelper->getCategoryParamLabel()) {
            return '';
        } else {
            return $this->getWpAttributeOptions($this->_attributeId)->getShowQuantity();
        }
    }

    public function getIsMultiSelect()
    {
        if ($this->_attributeId == $this->_wpHelper->getRatingParamLabel()) {
            return $this->_wpHelper->isRatingFilterMultiselect();
        } elseif ($this->_attributeId == $this->_wpHelper->getCategoryParamLabel()) {
            return '';
        } else {
            return $this->getWpAttributeOptions($this->_attributeId)->getIsMultiselect();
        }
    }

    public function canShowInstantSearch()
    {
        return ($this->_attributeId > 0) ? $this->getWpAttributeOptions($this->_attributeId)->getInstantSearch() : '';
    }

    public function canShowInstantSearchMobile()
    {
        return ($this->_attributeId > 0) ? $this->getWpAttributeOptions($this->_attributeId)->getInstantSearchMobile() : '';
    }

    public function getCategoryParamLabel()
    {
        return $this->_wpHelper->getCategoryParamLabel();
    }

    public function getRatingParamLabel()
    {
        return $this->_wpHelper->getRatingParamLabel();
    }

    public function getSliderJsWidget()
    {
        $jsSliderWidget = 'jquery/ui-modules/widgets/slider';
        $magentoVersion = $this->productMetadata->getVersion();
        if (version_compare($magentoVersion, '2.4.4', '<')) {
            $jsSliderWidget = 'jquery-ui-modules/slider';
        }

        return $jsSliderWidget;
    }
}