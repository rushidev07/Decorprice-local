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

namespace Magetrend\Eop\Model;

use Magento\Framework\App\Request\Http;
use Magento\Quote\Model\Quote\Address;

/**
 * Campaign model
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Campaign extends \Magento\Rule\Model\AbstractModel
{
    const CATEGORY_ATTRIBUTE_CODE = 'mteop_category_campaign';

    const PRODUCT_ATTRIBUTE_CODE = 'mteop_campaign';

    /**
     * @var CampaignStoreFactory
     */
    public $campaignStoreFactory;

    /**
     * @var CampaignPageFactory
     */
    public $campaignPageFactory;

    /**
     * @var PopupFactory
     */
    public $popupFactory;

    /**
     * @var ResourceModel\CampaignPage\CollectionFactory
     */
    public $pCollectionFactory;

    /**
     * @var ResourceModel\CampaignStore\CollectionFactory
     */
    public $sCollectionFactory;

    /**
     * @var ResourceModel\Campaign\CollectionFactory
     */
    public $campaignCollectionFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Current campaign on the page
     *
     * @var null|Campaign
     */
    private $currentCampaign = null;

    /**
     * Current popup on the page
     *
     * @var null|Popup
     */
    private $currentPopup = null;

    /**
     * Popup asssigned to campaign
     *
     * @var null|Popup
     */
    private $popup = null;

    /**
     * @var \Magetrend\Eop\Helper\Data
     */
    public $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    public $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    public $cookieMetadataFactory;

    /**
     * @var Http
     */
    public $request;

    /** @var \Magento\SalesRule\Model\Rule\Condition\CombineFactory */
    public $condCombineFactory;

    /** @var \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory */
    public $condProdCombineF;

    /**
     * Store already validated addresses and validation results
     *
     * @var array
     */
    public $validatedAddresses = [];

    /**
     * Campaign constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\SalesRule\Model\Rule\Condition\CombineFactory $condCombineFactory
     * @param \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF
     * @param ResourceModel\CampaignPage\CollectionFactory $pCollection
     * @param ResourceModel\CampaignStore\CollectionFactory $sCollection
     * @param ResourceModel\Campaign\CollectionFactory $campaignCollection
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magetrend\Eop\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param Http $request
     * @param CampaignStoreFactory $campaignStoreFactory
     * @param CampaignPageFactory $campaignPageFactory
     * @param PopupFactory $popupFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\SalesRule\Model\Rule\Condition\CombineFactory $condCombineFactory,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF,
        \Magetrend\Eop\Model\ResourceModel\CampaignPage\CollectionFactory $pCollection,
        \Magetrend\Eop\Model\ResourceModel\CampaignStore\CollectionFactory $sCollection,
        \Magetrend\Eop\Model\ResourceModel\Campaign\CollectionFactory $campaignCollection,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magetrend\Eop\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        Http $request,
        \Magetrend\Eop\Model\CampaignStoreFactory $campaignStoreFactory,
        \Magetrend\Eop\Model\CampaignPageFactory $campaignPageFactory,
        \Magetrend\Eop\Model\PopupFactory $popupFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->pCollectionFactory = $pCollection;
        $this->sCollectionFactory = $sCollection;
        $this->campaignCollectionFactory = $campaignCollection;
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->request = $request;
        $this->campaignStoreFactory = $campaignStoreFactory;
        $this->campaignPageFactory = $campaignPageFactory;
        $this->popupFactory = $popupFactory;
        $this->condCombineFactory = $condCombineFactory;
        $this->condProdCombineF = $condProdCombineF;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    //@codingStandardsIgnoreLine
    protected function _construct()
    {
        $this->_init('Magetrend\Eop\Model\ResourceModel\Campaign');
    }

    /**
     * Returns campaing-page related collection
     *
     * @return \Magetrend\Eop\Model\ResourceModel\CampaignPage\Collection
     */
    public function getPageIdsCollection()
    {
        $collection = $this->pCollectionFactory->create()
            ->setCampaignFilter($this->getId());
        return $collection;
    }

    /**
     * Returns pages id list as array
     *
     * @return array
     */
    public function getPageIdsAsArray()
    {
        $dataArray = [];
        $collection = $this->getPageIdsCollection();
        if ($collection->getSize() > 0) {
            foreach ($collection as $item) {
                $dataArray[] = $item->getPageId();
            }
        }
        return $dataArray;
    }

    /**
     * Returns campaign-store related collection
     *
     * @return \Magetrend\Eop\Model\ResourceModel\CampaignStore\Collection
     */
    public function getStoreIdsCollection()
    {
        $collection = $this->sCollectionFactory->create()
            ->setCampaignFilter($this->getId());
        return $collection;
    }

    /**
     * Returns store id list as array
     *
     * @return array
     */
    public function getStoreIdsAsArray()
    {
        $dataArray = [];
        $collection = $this->getStoreIdsCollection();
        if ($collection->getSize() > 0) {
            foreach ($collection as $item) {
                $dataArray[] = $item->getStoreId();
            }
        }
        return $dataArray;
    }

    /**
     * Delete campaign
     *
     * @return void
     */
    public function delete()
    {
        $this->deleteCampaignPageCollection();
        $this->deleteCampaignStoreCollection();
        parent::delete();
    }

    /**
     * Save campaign
     *
     * @return void
     */
    public function save()
    {
        $pageIds = $this->getData('page_ids');
        $soreIds = $this->getData('store_ids');
        parent::save();
        $this->saveCampaignPageCollection($pageIds);
        $this->saveCampaignStoreCollection($soreIds);
    }

    /**
     * Delete campaign-page related objects
     *
     * @return void
     */
    public function deleteCampaignPageCollection()
    {
        $pCollection = $this->pCollectionFactory->create()
            ->setCampaignFilter($this->getId());
        $pCollection->walk('delete');
    }

    /**
     * Delete campaign-store related objects
     *
     * @return void
     */
    public function deleteCampaignStoreCollection()
    {
        $sCollection = $this->sCollectionFactory->create()
            ->setCampaignFilter($this->getId());
        $sCollection->walk('delete');
    }

    /**
     * Save campaign-page related objects
     *
     * @param array $data
     * @return void
     */
    public function saveCampaignPageCollection($data)
    {
        $this->deleteCampaignPageCollection();
        if (!empty($data)) {
            $campaignId = $this->getId();
            foreach ($data as $id) {
                $campaignPage = $this->campaignPageFactory->create();
                $campaignPage->setCampaignId($campaignId);
                $campaignPage->setPageId($id);
                //@codingStandardsIgnoreLine
                $campaignPage->save();
            }
        }
    }

    /**
     * Save campaign-store related objects
     *
     * @param array $data
     * @return void
     */
    public function saveCampaignStoreCollection($data)
    {
        $this->deleteCampaignStoreCollection();
        if (!empty($data)) {
            $campaignId = $this->getId();
            foreach ($data as $id) {
                $campaignPage = $this->campaignStoreFactory->create();
                $campaignPage->setCampaignId($campaignId);
                $campaignPage->setStoreId($id);
                //@codingStandardsIgnoreLine
                $campaignPage->save();
            }
        }
    }

    /**
     * Returns if available current campaign
     *
     * @return \Magetrend\Eop\Model\Campaign | bool
     */
    public function getCurrentCampaign()
    {
        if ($this->currentCampaign == null) {
            $storeId = $this->storeManager->getStore()->getId();
            $pageId = $this->helper->getCurrentPageId();
            $campaignCollection = $this->campaignCollectionFactory->create()
                ->addFieldToFilter('is_active', 1)
                ->addPageIdFilter($pageId)
                ->addDateFilter()
                ->groupById();

            if (!$this->storeManager->isSingleStoreMode()) {
                $campaignCollection->addStoreIdFilter($storeId);
            }

            if ($campaignCollection->getSize() > 0) {
                foreach ($campaignCollection as $campaign) {
                    if ($this->isAvailable($campaign)) {
                        $this->currentCampaign = $campaign;
                        break;
                    }
                }
            } else {
                $this->currentCampaign = false;
            }
        }
        return $this->currentCampaign;
    }

    /**
     * Returns current popup model
     *
     * @return \Magetrend\Eop\Model\Popup | bool
     */
    public function getCurrentPopup()
    {
        if ($this->currentPopup == null) {
            $campaign = $this->getCurrentCampaign();
            if ($campaign) {
                $this->currentPopup = $campaign->getPopup();
            } else {
                $this->currentPopup = false;
            }
        }

        return $this->currentPopup;
    }

    /**
     * Returns assigned popup model
     *
     * @return \Magetrend\Eop\Model\Popup | bool
     */
    public function getPopup()
    {
        if ($this->popup == null) {
            $this->popup  = $this->popupFactory->create()
                ->load($this->getPopupId());
        }
        return $this->popup;
    }

    /**
     * Returns cookiek lifetime
     *
     * @return float
     */
    public function getCookieLifetime()
    {
        if ($this->helper->getIsDevelopmentMode()) {
            return 0.000001;
        }

        return parent::getCookieLifetime();
    }

    /**
     * Returns cookie name
     *
     * @return string
     */
    public function getCookieName()
    {
        //@codingStandardsIgnoreStart
        if ($this->helper->getIsDevelopmentMode()) {

            return 'npdev-'.substr(hash('md5', time()), 0, 5);
        }

        $cookiePrefix = $this->helper->getCookiePrefix();
        if (empty($cookiePrefix)) {
            $cookiePrefix = 'mteo_';
        }
        $storeId = $this->storeManager->getStore()->getId();
        return $cookiePrefix.'_'.hash('md5', $storeId.'_'.$this->getId());
        //@codingStandardsIgnoreEnd
    }

    /**
     * Set indicator about user who got coupon
     *
     * @return void
     */
    public function setYesNoCookie()
    {
        $cookieName = $this->helper->getYesNoButtonCookieName();
        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setHttpOnly(false)
            ->setDuration(2147483647)
            ->setPath('/');
        $this->cookieManager->setPublicCookie($cookieName, 1, $cookieMetadata);
    }

    /**
     * Check for availability
     *
     * @param $campaign
     * @return bool
     */
    public function isAvailable($campaign)
    {
        $campaignParams = $campaign->getParams();
        if (!empty($campaignParams)) {
            $campaignParams = explode('&', $campaignParams);
            $params = $this->request->getParams();
            foreach ($campaignParams as $param) {
                $param = explode('=', $param);
                //@codingStandardsIgnoreStart
                if (count($param) != 2) {
                    continue;
                }
                //@codingStandardsIgnoreEnd

                if (isset($params[$param[0]]) && $params[$param[0]] == $param[1]) {
                    continue;
                }

                return false;
            }
        }

        $pageIds = $campaign->getPageIdsAsArray();
        $currentPageId = $this->helper->getCurrentPageId();

        if (in_array('s_category_page', $pageIds)) {
            if (!$campaign->isAvailableOnCategory() && $currentPageId == 'category_page') {
                return false;
            }
        }

        if (in_array('s_product_page', $pageIds) && $currentPageId == 'product_page') {
            if (!$campaign->isAvailableOnProduct()) {
                return false;
            }
        }

        return true;
    }


    public function isAvailableOnCategory()
    {
        $currentCategory = $this->_registry->registry('current_category');
        if ($currentCategory && $currentCategory->getData(self::CATEGORY_ATTRIBUTE_CODE) == $this->getId()) {
            return true;
        }
        return false;
    }

    public function isAvailableOnProduct()
    {
        $currentProduct = $this->_registry->registry('current_product');
        if ($currentProduct && $currentProduct->getData(self::PRODUCT_ATTRIBUTE_CODE) == $this->getId()) {
            return true;
        }

        return false;
    }

    /**
     * Get rule condition combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->condCombineFactory->create();
    }

    /**
     * Get rule condition product combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Product\Combine
     */
    public function getActionsInstance()
    {
        return $this->condProdCombineF->create();
    }

    /**
     * Check cached validation result for specific address
     *
     * @param Address $address
     * @return bool
     */
    public function hasIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);
        return isset($this->validatedAddresses[$addressId]) ? true : false;
    }

    /**
     * Set validation result for specific address to results cache
     *
     * @param Address $address
     * @param bool $validationResult
     * @return $this
     */
    public function setIsValidForAddress($address, $validationResult)
    {
        $addressId = $this->_getAddressId($address);
        $this->validatedAddresses[$addressId] = $validationResult;
        return $this;
    }

    /**
     * Get cached validation result for specific address
     *
     * @param Address $address
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);
        return isset($this->validatedAddresses[$addressId]) ? $this->validatedAddresses[$addressId] : false;
    }

    /**
     * Return id for address
     *
     * @param Address $address
     * @return string
     */
    private function _getAddressId($address)
    {
        if ($address instanceof Address) {
            return $address->getId();
        }
        return $address;
    }

    /**
     * Check can rule be applied
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    public function canApplyRule($quote)
    {
        if (!$this->hasConditions()) {
            return true;
        }

        $items = $quote->getAllItems();
        if (empty($items)) {
            return false;
        }

        foreach ($items as $item) {
            if ($this->validate($this->getAddress($quote, $item))) {
                return true;
            }
        }

        return false;
    }

    public function getAddress($quote, $item)
    {
        $address = $item->getAddress();
        $address->setData('total_qty', $quote->getData('items_qty'));
        return $address;
    }

    /**
     * @return bool
     */
    public function hasConditions()
    {
        if (!$this->getConditions()) {
            return false;
        }

        $conditions = $this->getConditions()->getConditions();
        if (empty($conditions)) {
            return false;
        }

        return true;
    }

}
