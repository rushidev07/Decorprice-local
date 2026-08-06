<?php
namespace Ahy\ThemeCustomizations\Block\Navigation;


use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;

class FilterRenderer extends \WeltPixel\LayeredNavigation\Block\Navigation\FilterRenderer
{
    private   $htmlPagerBlock;

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
        $this->htmlPagerBlock = $htmlPagerBlock;
        parent::__construct($productMetadata, $wpHelper, $attributeOptions, $registry, $currency, $htmlPagerBlock, $context, $data );
    }

    /**
     * @param FilterInterface $filter
     * @return string
     */
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
                    // Convert strings to integers for comparison
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
                    // Convert strings to integers for comparison
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

    /**
     * @return array
     */
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
                'isPrice' => true,
                'isWidth' => $this->isWidth,
                'isHeight' => $this->isHeight,
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

    /**
     * @return bool
     */
    public function priceIsInputType()
    {
        return $this->_wpHelper->canShowPriceInput();
    }


    /**
     * @return string
     */
    public function getSliderApplyUrl()
    {
        if($this->isPrice){
            $params = [
                'price' => $this->minPrice . '-' . $this->maxPrice,
                $this->htmlPagerBlock->getPageVarName() => null
            ];
        } elseif($this->isWidth) {
            $params = [
                'width' => $this->minWidth . '-' . $this->maxWidth,
                $this->htmlPagerBlock->getPageVarName() => null
            ];
        } elseif($this->isHeight) {
            $params = [
                'height' => $this->minHeight . '-' . $this->maxHeight,
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

    /**
     * @return mixed
     */
    public function getPriceStep()
    {
        $step = $this->_wpHelper->getPriceRangeStep();
        if(!$this->isWidth){
            return ($step > $this->maxPrice) ? $this->maxPrice : $step;
        } else {
            return ($step > $this->maxWidth) ? $this->maxWidth : $step;
        }
    }

    /**
     * @param $filter
     */
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

    /**
     * Return wp attribute options
     *
     * @param $attributeId
     * @return mixed
     */
    public function getWpAttributeOptions()
    {
        $this->_attributeOptionsObj = ($this->_attributeId > 0) ? $this->_attributeOptions->getDisplayOptionsByAttribute($this->_attributeId) : '';

        return $this->_attributeOptionsObj;
    }

    /**
     * @return mixed
     */
    public function getAttributeId()
    {
        return $this->_attributeId;
    }

    /**
     * return the 'Visible Options' attribute configuration value
     *
     * @return mixed
     */
    public function getVisibleItems()
    {
        return ($this->_attributeId > 0) ? $this->getWpAttributeOptions($this->_attributeId)->getVisibleOptions() : '';
    }

    /**
     * return the 'Visible Options Step' attribute configuration value
     *
     * @return mixed
     */
    public function getVisibleItemsStep()
    {
        return ($this->_attributeId > 0) ? $this->getWpAttributeOptions($this->_attributeId)->getVisibleOptionsStep() : '';
    }

    /**
     * return the 'Show Qty' attribute configuration value
     *
     * @return mixed
     */
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

    /**
     * return the 'Is Multiselect' attribute configuration value
     *
     * @return mixed
     */
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

    /**
     * return the 'Instant Search' attribute configuration value
     *
     * @return string
     */
    public function canShowInstantSearch()
    {
        return ($this->_attributeId > 0) ? $this->getWpAttributeOptions($this->_attributeId)->getInstantSearch() : '';
    }

    /**
     * return the 'Instant Search Mobile' attribute configuration value
     *
     * @return string
     */
    public function canShowInstantSearchMobile()
    {
        return ($this->_attributeId > 0) ? $this->getWpAttributeOptions($this->_attributeId)->getInstantSearchMobile() : '';
    }

    /**
     * @return string
     */
    public function getCategoryParamLabel()
    {
        return $this->_wpHelper->getCategoryParamLabel();
    }

    /**
     * @return string
     */
    public function getRatingParamLabel()
    {
        return $this->_wpHelper->getRatingParamLabel();
    }

    /**
     * @return string
     */
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
