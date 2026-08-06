<?php

/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    \Unirgy\RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */
namespace Unirgy\RapidFlow\Model\ResourceModel;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

use Magento\Eav\Model\Config;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Uploader;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Phrase;
use Magento\Staging\Model\VersionManager;
use Magento\Store\Model\StoreManagerInterface;
use Unirgy\RapidFlow\Exception\Row;
use Unirgy\RapidFlow\Exception\Stop;
use Unirgy\RapidFlow\Helper\Data as HelperData;
use Unirgy\RapidFlow\Helper\File as FileHelper;

/**
 * Class AbstractResource
 * @package Unirgy\RapidFlow\Model\ResourceModel
 */
abstract class AbstractResourceBase extends \Magento\Framework\Model\ResourceModel\AbstractResource
{

    const TABLE_CATALOG_CATEGORY_ENTITY                           = 'catalog_category_entity';
    const TABLE_CATALOG_CATEGORY_PRODUCT                          = 'catalog_category_product';
    const TABLE_CATALOG_EAV_ATTRIBUTE                             = 'catalog_eav_attribute';
    const TABLE_CATALOG_PRODUCT_BUNDLE_OPTION_SEQ                 = 'sequence_product_bundle_option';
    const TABLE_CATALOG_PRODUCT_BUNDLE_OPTION                     = 'catalog_product_bundle_option';
    const TABLE_CATALOG_PRODUCT_BUNDLE_OPTION_VALUE               = 'catalog_product_bundle_option_value';
    const TABLE_CATALOG_PRODUCT_BUNDLE_SELECTION_SEQ              = 'sequence_product_bundle_selection';
    const TABLE_CATALOG_PRODUCT_BUNDLE_SELECTION                  = 'catalog_product_bundle_selection';
    const TABLE_CATALOG_PRODUCT_BUNDLE_SELECTION_PRICE            = 'catalog_product_bundle_selection_price';
    const TABLE_CATALOG_PRODUCT_ENTITY                            = 'catalog_product_entity';
    const TABLE_CATALOG_PRODUCT_ENTITY_GROUP_PRICE                = 'catalog_product_entity_group_price';
    const TABLE_CATALOG_PRODUCT_ENTITY_MEDIA_GALLERY              = 'catalog_product_entity_media_gallery';
    const TABLE_CATALOG_PRODUCT_ENTITY_MEDIA_GALLERY_VALUE        = 'catalog_product_entity_media_gallery_value';
    const TABLE_CATALOG_PRODUCT_ENTITY_MEDIA_GALLERY_VALUE_ENTITY = 'catalog_product_entity_media_gallery_value_to_entity';
    const TABLE_CATALOG_PRODUCT_ENTITY_MEDIA_GALLERY_VALUE_VIDEO  = 'catalog_product_entity_media_gallery_value_video';
    const TABLE_CATALOG_PRODUCT_ENTITY_TIER_PRICE                 = 'catalog_product_entity_tier_price';
    const TABLE_CATALOG_PRODUCT_ENTITY_VARCHAR                    = 'catalog_product_entity_varchar';
    const TABLE_CATALOG_PRODUCT_LINK                              = 'catalog_product_link';
    const TABLE_CATALOG_PRODUCT_LINK_ATTRIBUTE                    = 'catalog_product_link_attribute';
    const TABLE_CATALOG_PRODUCT_LINK_TYPE                         = 'catalog_product_link_type';
    const TABLE_CATALOG_PRODUCT_OPTION                            = 'catalog_product_option';
    const TABLE_CATALOG_PRODUCT_OPTION_PRICE                      = 'catalog_product_option_price';
    const TABLE_CATALOG_PRODUCT_OPTION_TITLE                      = 'catalog_product_option_title';
    const TABLE_CATALOG_PRODUCT_OPTION_TYPE_PRICE                 = 'catalog_product_option_type_price';
    const TABLE_CATALOG_PRODUCT_OPTION_TYPE_TITLE                 = 'catalog_product_option_type_title';
    const TABLE_CATALOG_PRODUCT_OPTION_TYPE_VALUE                 = 'catalog_product_option_type_value';
    const TABLE_CATALOG_PRODUCT_RELATION                          = 'catalog_product_relation';
    const TABLE_CATALOG_PRODUCT_SUPER_ATTRIBUTE                   = 'catalog_product_super_attribute';
    const TABLE_CATALOG_PRODUCT_SUPER_ATTRIBUTE_LABEL             = 'catalog_product_super_attribute_label';
    const TABLE_CATALOG_PRODUCT_SUPER_ATTRIBUTE_PRICING           = 'catalog_product_super_attribute_pricing';
    const TABLE_CATALOG_PRODUCT_SUPER_LINK                        = 'catalog_product_super_link';
    const TABLE_CATALOG_PRODUCT_WEBSITE                           = 'catalog_product_website';
    const TABLE_CATALOGINVENTORY_STOCK_ITEM                       = 'cataloginventory_stock_item';
    const TABLE_GIFTCARD_ACCOUNT                                  = 'magento_giftcardaccount';
    const TABLE_GIFTCARD_ACCOUNT_HISTORY                          = 'magento_giftcardaccount_history';
    const TABLE_INVENTORY_SOURCE                                  = 'inventory_source';
    const TABLE_INVENTORY_STOCK                                   = 'inventory_stock';
    const TABLE_INVENTORY_SOURCE_ITEM                             = 'inventory_source_item';
    const TABLE_INVENTORY_SOURCE_STOCK_LINK                       = 'inventory_source_stock_link';
    const TABLE_INVENTORY_STOCK_SALES_CHANNEL                     = 'inventory_stock_sales_channel';
    const TABLE_CATEGORY_SEQUENCE                                 = 'sequence_catalog_category';
    const TABLE_CUSTOMER_GROUP                                    = 'customer_group';
    const TABLE_DOWNLOADABLE_LINK                                 = 'downloadable_link';
    const TABLE_DOWNLOADABLE_LINK_PRICE                           = 'downloadable_link_price';
    const TABLE_DOWNLOADABLE_LINK_TITLE                           = 'downloadable_link_title';
    const TABLE_DOWNLOADABLE_SAMPLE                               = 'downloadable_sample';
    const TABLE_DOWNLOADABLE_SAMPLE_TITLE                         = 'downloadable_sample_title';
    const TABLE_EAV_ATTRIBUTE                                     = 'eav_attribute';
    const TABLE_EAV_ATTRIBUTE_GROUP                               = 'eav_attribute_group';
    const TABLE_EAV_ATTRIBUTE_LABEL                               = 'eav_attribute_label';
    const TABLE_EAV_ATTRIBUTE_OPTION                              = 'eav_attribute_option';
    const TABLE_EAV_ATTRIBUTE_OPTION_SWATCH                       = 'eav_attribute_option_swatch';
    const TABLE_EAV_ATTRIBUTE_OPTION_VALUE                        = 'eav_attribute_option_value';
    const TABLE_EAV_ATTRIBUTE_SET                                 = 'eav_attribute_set';
    const TABLE_EAV_ENTITY_ATTRIBUTE                              = 'eav_entity_attribute';
    const TABLE_EAV_ENTITY_TYPE                                   = 'eav_entity_type';
    const TABLE_PRODUCT_SEQUENCE                                  = 'sequence_product';
    const TABLE_STORE                                             = 'store';
    const TABLE_STORE_GROUP                                       = 'store_group';
    const TABLE_STORE_WEBSITE                                     = 'store_website';

    /**
     * @var \Unirgy\RapidFlow\Helper\ProtectedCode\Context $context
     */
    protected $_context;

    /**
     *
     */
    const IMPORT_ROW_RESULT_ERROR = 'error';

    /**
     *
     */
    const IMPORT_ROW_RESULT_SUCCESS = 'success';

    /**
     *
     */
    const IMPORT_ROW_RESULT_NOCHANGE = 'nochange';

    /**
     *
     */
    const IMPORT_ROW_RESULT_DEPENDS = 'depends';

    /**
     *
     */
    const IMPORT_ROW_RESULT_EMPTY = 'empty';

    const ROW_ID = 'row_id';
    const BUNDLE_SEQ = 'bundle_seq';
    const SUPER_ATTR_ROW_ID = 'super_attr_row_id';
    const BUNDLE_PARENT = 'bundle_parent';

    /**
     * @var
     */
    protected $_frameworkModelLocale;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var HelperData
     */
    protected $_rapidFlowHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Config
     */
    protected $_eavModelConfig;

    /**
     * @var WriteFactory
     */
    protected $_directoryWriteFactory;

    /**
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var
     */
    protected $_exportImageRetainFolders;

    /**
     * @var string
     */
    protected $_translateModule = 'Unirgy_RapidFlow';

    /**
     * @var \Unirgy\RapidFlow\Model\Profile
     */
    protected $_profile;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_res;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected $_read;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected $_write;

    /**
     * @var
     */
    protected $_encodingFrom;

    /**
     * @var
     */
    protected $_encodingTo;

    /**
     * @var string
     */
    protected $_encodingIllegalChar;

    /**
     * @var
     */
    protected $_downloadRemoteImages;

    /**
     * @var
     */
    protected $_downloadRemoteImagesBatch;

    /**
     * @var array
     */
    protected $_remoteImagesBatch = [];

    /**
     * @var
     */
    protected $_missingImageAction;

    /**
     * @var
     */
    protected $_existingImageAction;

    /**
     * @var
     */
    protected $_remoteImageSubfolderLevel;

    /**
     * @var
     */
    protected $_imagesMediaDir;

    /**
     * @var
     */
    protected $_deleteOldImage;

    /**
     * @var
     */
    protected $_deleteOldImageSkipUsageCheck;

    /**
     * @var int
     */
    protected $_pageRowCount = 500;

    /**
     * @var int
     */
    protected $_pageSleepDelay = 0;

    protected $_curlConnectTimeout = 5;
    protected $_curlTimeout = 10;
    protected $_curlUserAgent;
    protected $_curlHeaders;
    protected $_curlCustomRequest;

    /**
     * @var FormatInterface
     */
    protected $_locale;

    /**
     *
     * @var string
     */
    protected $_entityIdField = 'entity_id';

    /**
     * DB Table cache
     *
     * @var array
     */
    protected $_tables = [];

    /**
     * DB table names by attribute type
     *
     * @var array
     */
    protected $_tablesByType = [];

    /**
     * Current data row
     *
     * @var array
     */
    protected $_row;

    /**
     * Current row number
     *
     * @var int
     */
    protected $_rowNum;

    /**
     * Current SQL select object
     *
     * @var \Magento\Framework\Db\Select
     */
    protected $_select;

    /**
     * Current filter
     *
     * @var mixed
     */
    protected $_filter;

    /**
     * SKU->ID cache
     *
     * @var array
     */
    protected $_skus = [];

    /**
     * SKU->SEQUENCE ID cache
     *
     * used only with Magento 2 EE v2.1 and up
     * @var array
     */
    protected $_skuSeq;

    protected $_skuAlt;

    /**
     * product SEQUENCE IDs
     *
     * used only with Magento 2 EE v2.1 and up
     * @var array
     */
    protected $_productSeqIds = [];

    /**
     * Mapping of product id to seq id
     * @var array
     */
    protected $_productIdToSeq = [];

    /**
     * Magento EAV configuration singleton
     *
     * @var Config
     */
    protected $_eav;

    /**
     * Limit number of items in cache to avoid memory problems
     *
     * @var mixed
     */
    protected $_maxCacheItems = array(
        'sku' => 10000,
        self::TABLE_CATALOG_PRODUCT_BUNDLE_OPTION => 1000,
        'custom_option' => 1000,
        'custom_option_selection' => 1000,
    );

    /**
     * @var null
     */
    protected $_rootCatId = null;

    /**
     * Cache of dropdown attribute value labels
     *
     * @var array
     */
    protected $_attrOptionsByValue = [];

    /**
     * @var array
     */
    protected $_attrOptionsByLabel = [];

    /**
     * @var array
     */
    protected $_customerGroups = [];

    /**
     * @var array
     */
    protected $_customerGroupsByName = [];

    /**
     * An optional method to call on each row export
     *
     * @var array
     */
    protected $_exportRowCallback = [];

    /**
     * @var array
     */
    protected $_entityTypes = [];

    /**
     * @var array
     */
    protected $_fieldsIdx = [];

    /**
     * @var null
     */
    protected $_storeIds = null;

    /**
     * @var
     */
    protected $_galleryAttrId;

    /**
     * @var array
     */
    protected $_categoryUrlEntities;

    /**
     * @var array
     */
    protected $_attrOptionsStatus;

    /**
     * @var Profile
     */
    protected $_db;

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    protected $_websiteRepository;

    /**
     * @var \Magento\Store\Api\Data\WebsiteInterface[]
     */
    protected $_websitesById;

    /**
     * @var \Magento\Store\Api\Data\WebsiteInterface[]
     */
    protected $_websitesByCode;
    /**
     * @var \Magento\Staging\Model\VersionManager
     */
    protected $versionManager;
    /**
     * @var \Magento\Staging\Model\Update
     */
    protected $currentVersion;

    public $benchmark=false;

    /**
     * @var \Magento\Framework\Profiler\Driver\Standard\Stat
     */
    protected $_stat;

    protected $_productUpdates = [];
    protected $_mediaUpdates = [];

    /**
     * @var \Unirgy\RapidFlow\Helper\ImageCache
     */
    protected $_imageCacheHelper;
    /**
     * @var \Unirgy\RapidFlow\Helper\ProductCache
     */
    protected $_productCacheHelper;

    protected function _construct()
    {
        /** @var \Unirgy\RapidFlow\Helper\ProtectedCode\Context $context */
        $this->_context = ObjectManager::getInstance()->get('\Unirgy\RapidFlow\Helper\ProtectedCode\Context');
        $this->_db = $this->_context->db;
        $this->_res = $this->_db->getResources();
        $this->_read = $this->getConnection();
        $this->_write = $this->getConnection();

        $this->_locale = $this->_context->formatInterface;
        $this->_filesystem = $this->_context->filesystem;
        $this->_rapidFlowHelper = $this->_context->helper;
        $this->_storeManager = $this->_context->storeManager;
        $this->_eavModelConfig = $this->_context->eavConfig;
        $this->_directoryWriteFactory = $this->_context->writeFactory;
        $this->_eventManager = $this->_context->eventManager;
        $this->_websiteRepository = $this->_context->websiteRepository;
        $this->_stat = $this->_context->profilerStat;
        $this->_imageCacheHelper = $this->_context->imageCacheHelper;
        $this->_productCacheHelper = $this->_context->productCacheHelper;

        $this->_prepareEntityIdField();
    }

    /**
     * Translate a phrase
     *
     * @return string
     */
    public function __()
    {
        $argc = func_get_args();

        $text = array_shift($argc);
        if (!empty($argc) && is_array($argc[0])) {
            $argc = $argc[0];
        }

        return new Phrase($text, $argc);
    }

    /**
     * @param  \Unirgy\RapidFlow\Model\Profile $profile
     * @return $this
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function setProfile($profile)
    {
        $this->_profile = $profile;
        $profileType = $profile->getProfileType();

        $this->_encodingFrom = $profile->getData('options/encoding/from');
        $this->_encodingTo = $profile->getData('options/encoding/to');
        $this->_encodingIllegalChar = $profile->getData('options/encoding/illegal_char');
        $this->_downloadRemoteImages = $profile->getData('options/' . $profileType . '/image_files_remote');
        $this->_downloadRemoteImagesBatch = $profile->getData('options/' . $profileType . '/image_files_remote_batch');
        $this->_missingImageAction = (string)$profile->getData('options/' . $profileType . '/image_missing_file');
        $this->_existingImageAction = (string)$profile->getData('options/' . $profileType . '/image_existing_file');
        $this->_remoteImageSubfolderLevel = $profile->getData('options/' . $profileType . '/image_remote_subfolder_level');
        $this->_imagesMediaDir = $profile->getMediaBaseDir() . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'product';
        $this->_deleteOldImage = $profile->getData('options/' . $profileType . '/image_delete_old');
        $this->_deleteOldImageSkipUsageCheck = $profile->getData('options/' . $profileType . '/image_delete_skip_usage_check');

        $this->benchmark = $this->_scopeConfig->isSetFlag('urapidflow/finetune/debug');

        return $this;
    }

    /**
     * @return array|mixed|null
     */
    protected function _getStoreIds()
    {
        if (null === $this->_storeIds) {
            $ids = $this->_profile->getData('options/store_ids');
            if (empty($ids)) {
                $this->_storeIds = [];
                return $this->_storeIds;
            }
            if (!is_array($ids)) {
                $ids = @explode(',', $ids);
            }
            $this->_storeIds = $ids;
            if ($this->_rapidFlowHelper->hasEeGwsFilter()) {
                $this->_storeIds = $this->_rapidFlowHelper->filterEeGwsStoreIds($this->_storeIds);
            }
        }

        return $this->_storeIds;
    }

    /**
     * Get and validate store ID
     *
     * @param string|int $id
     * @param bool $allowDefault
     * @return int
     * @throws \Exception
     */
    protected function _getStoreId($id, $allowDefault = false)
    {
        $store = $this->_storeManager->getStore($id);
        if (!$store || (!$allowDefault && $store->getId() == 0)) {
            throw new LocalizedException(__('Invalid store'));
        }

        return $store->getId();
    }

    /**
     * @param $id
     * @param bool $allowDefault
     * @return mixed
     * @throws \Exception
     */
    protected function _getWebsiteId($id, $allowDefault = false)
    {
        $website = $this->getWebsite($id);
        if (!$allowDefault && $website->getId() == 0) {
            throw new LocalizedException(__('Invalid website'));
        }

        return $website->getId();
    }

    /**
     * @param int|string $id
     * @return \Magento\Store\Api\Data\WebsiteInterface
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getWebsite($id)
    {
        if(isset($this->_websitesById[$id])){
            return $this->_websitesById[$id];
        } else if(isset($this->_websitesByCode[$id])){
            return $this->_websitesByCode[$id];
        }

        if (is_numeric($id)) {
            $website = $this->_websiteRepository->getById($id);
        } else {
            $website = $this->_websiteRepository->get($id);
        }

        if($website->getId() !== null){
            $this->_websitesById[$website->getId()] = $website;
            $this->_websitesByCode[$website->getCode()] = $website;
        } else {
            throw new LocalizedException(__('Website %1 not found', $id));
        }
        return $website;
    }

    /**
     * @param array $a
     * @param array $b
     * @return bool
     */
    protected function _isChangeRequired(array $a, array $b)
    {
        foreach ($a as $k => $v) {
            if (isset($b[$k]) && $b[$k] != $v) {
                return true;
            }
        }

        return false;
    }

    /**
     * Stop processing if locked
     * @throws Stop
     */
    protected function _checkLock()
    {
        if (!$this->_profile->isLocked()) {
            throw new Stop();
        }
    }

    protected function _prepareEntityIdField()
    {
        if ($this->_rapidFlowHelper->hasMageFeature(self::ROW_ID)) {
            $this->_entityIdField = self::ROW_ID;
            if(isset($this->_fieldAttributes)){
                $this->_fieldAttributes[self::ROW_ID] = self::ROW_ID;
                $this->_fieldAttributes[self::ROW_ID] = self::ROW_ID;
            }

            $this->versionManager = ObjectManager::getInstance()->get(VersionManager::class);
            $this->currentVersion = $this->versionManager->getCurrentVersion();

            $id = $this->currentVersion->getId();
        }
    }

    /**
     * Maintain product SKU->ID cache
     *
     * @param string $sku
     * @return int
     * @throws \Exception
     */
    protected function _getIdBySku($sku, $isSeqId=false)
    {
        // in case we got already resolved id
        if (is_int($sku)) {
            return $sku;
        }
        if (!array_key_exists($sku, $this->_skus)) {
            $productTable = $this->_t(self::TABLE_CATALOG_PRODUCT_ENTITY);
            $rowId = 'entity_id';
            $fetchColumns = ['sku',$rowId];
            if ($this->_rapidFlowHelper->hasMageFeature(self::ROW_ID)) {
                $rowId = $this->_entityIdField;
                $fetchColumns[] = $rowId;
            }
            if ($this->getAltSku()) {
                $altSkuAttr = $this->getAltSkuAttr();
                $altTable = $this->getAttrTable($altSkuAttr->getData());
                $altSelect = $this->_read->select()
                    ->from(['a'=>$altTable], [])
                    ->join(['p'=>$productTable], "p.{$rowId}=a.{$rowId}", $fetchColumns)
                    ->where("a.value=?", $sku)
                    ->where('a.attribute_id in (?)', $altSkuAttr->getId())
                    ->order('a.store_id asc');
                $row = $this->_read->fetchRow($altSelect);
            } else {
                $row = $this->_read->fetchRow("SELECT ".implode(',', $fetchColumns)." FROM $productTable WHERE sku=?", $sku);
            }
            // keep only last used 10000 skus to avoid memory problems
            if (sizeof($this->_skus) >= $this->_maxCacheItems['sku']) {
                reset($this->_skus);
                unset($this->_skus[key($this->_skus)]);
            }
            if ($row) {
                $this->_skuAlt[$sku] = $row['sku'];
                $this->_skus[$sku] = $row[$this->_entityIdField];
                $this->_skuSeq[$sku] = $row['entity_id'];
            } else {
                $this->_skuAlt[$sku] = false;
                $this->_skus[$sku] = false;
                $this->_skuSeq[$sku] = false;
            }
        }
        if (empty($this->_skus[$sku])) {
            throw new LocalizedException(__('Invalid SKU (%1)', $sku));
        }

        return $isSeqId ? $this->_skuSeq[$sku] : $this->_skus[$sku];
    }

    public function getSkuByAlt($altSku)
    {
        $this->_getIdBySku($altSku);
        return $this->_skuAlt[$altSku];
    }

    public function getAltSkuAttr()
    {
        return $this->_eavModelConfig->getAttribute('catalog_product', $this->getAltSku());
    }
    public function getAltSku()
    {
        $altSku = $this->_scopeConfig->getValue('urapidflow/import_options/alt_sku');
        if ($altSku) {
            $altSkuAttr = $this->_eavModelConfig->getAttribute('catalog_product', $altSku);
            if (!$altSkuAttr || !$altSkuAttr->getId()) {
                $altSku = false;
            }
        }
        return $altSku;
    }

    /**
     * Maintain table name cache
     *
     * @param string $table
     * @return string
     */
    protected function _t($table)
    {
        if (empty($this->_tables[$table])) {
            try {
                $this->_tables[$table] = $this->_res->getTableName($table);
            } catch (\Exception $e) {
                $this->_tables[$table] = false;
            }
        }

        return $this->_tables[$table];
    }

    /**
     * @param $attrCode
     * @param string $entityType
     * @return int|mixed|null
     * @throws \Exception
     */
    protected function _getAttributeId($attrCode, $entityType = 'catalog_product')
    {
        $attr = $this->_eavModelConfig->getAttribute($entityType, $attrCode);
        if (!$attr || !$attr->getAttributeId()) {
            throw new \Exception(__('Invalid attribute: %1', $attrCode));
        }

        return $attr->getAttributeId();
    }

    /**
     * @param $entityTypeCode
     * @param null $field
     * @return mixed
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getEntityType($entityTypeCode, $field = null)
    {
        if (empty($this->_entityTypes[$entityTypeCode])) {
            $entityType = $this->_eavModelConfig->getEntityType($entityTypeCode);
            if (!$entityType) {
                throw new LocalizedException(__('Invalid entity type: %1', $entityTypeCode));
            }
            if (is_object($entityType)) {
                $entityType = $entityType->toArray();
            }
            $this->_entityTypes[$entityTypeCode] = $entityType;
        }

        return !(null === $field) ? $this->_entityTypes[$entityTypeCode][$field] : $this->_entityTypes[$entityTypeCode];
    }

    /**
     * @param $attrCode
     * @param string $entityType
     * @return bool
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _fetchAttributeOptions($attrCode, $entityType = 'catalog_product')
    {
        if (isset($this->_attrOptionsStatus[$attrCode])) {
            return $this->_attrOptionsStatus[$attrCode];
        }
        $attr = $this->_eavModelConfig->getAttribute($entityType, $attrCode);
        if (!$attr) {
            throw new \Exception(__('Invalid attribute: %1', $attrCode));
        }
        $aId = $attr->getAttributeId();
        if (!isset($this->_attrOptionsByValue[$aId])) {
            if (!$attr->usesSource()) {
                $this->_attrOptionsStatus[$attrCode] = false;

                return false;
            }
            $options = $attr->getSource()->getAllOptions();
            foreach ($options as $o) {
                if (!$o['value']) {
                    continue;
                }
                $this->_attrOptionsByValue[$aId][$o['value']] = $o['label'];
                $this->_attrOptionsByLabel[$aId][strtolower($o['label'])] = $o['value'];
            }
        }
        $this->_attrOptionsStatus[$attrCode] = true;

        return true;
    }

    /**
     * Apply product filter...
     * @param string $attr
     */
    protected function _applyProductFilter($attr = 'main.entity_id')
    {
        //$attr = str_replace('entity_id', $this->_entityIdField, $attr);
        if (!empty($this->_filter['product_ids'])) {
            $this->_select->where("{$attr} in (?)", $this->_filter['product_ids']);
        }
        $productIds = $this->_profile->getConditionsProductIds();
        if (is_array($productIds)) {
            $this->_select->where("{$attr} in (?)", $productIds);
        }
    }

    /**
     * @param $key
     * @param bool $byName
     * @return mixed
     * @throws Row
     */
    protected function _getCustomerGroup($key, $byName = false)
    {
        if (!$this->_customerGroups) {
            $rows = $this->_read->fetchAll("select * from {$this->_t('customer_group')}");
            $this->_customerGroups = [];
            foreach ($rows as $r) {
                $this->_customerGroups[$r['customer_group_id']] = $r['customer_group_code'];
                $this->_customerGroupsByName[strtolower($r['customer_group_code'])] = $r['customer_group_id'];
            }
        }
        $errorMsg = __('Invalid customer group: %1', $key);
        if ($byName) {
            if (!isset($this->_customerGroupsByName[strtolower($key)])) {
                throw new Row($errorMsg);
            }

            return $this->_customerGroupsByName[strtolower($key)];
        } else {
            if (!isset($this->_customerGroups[$key])) {
                throw new Row($errorMsg);
            }

            return $this->_customerGroups[$key];
        }
    }

    /**
     * @return string
     */
    protected function _getGalleryAttrId()
    {
        if (!$this->_galleryAttrId) {
            $this->_galleryAttrId = $this->_write->fetchOne("select attribute_id from {$this->_t('eav_attribute')} where attribute_code='media_gallery' and frontend_input='gallery'");
        }

        return $this->_galleryAttrId;
    }

    /**
     * @var array map of urls to local file names
     */
    protected $_remoteImagesCache = [];

    protected function convertToAbsoluteUrl($url) {
        $url = urldecode($url);
        $url = str_replace(array('/', '\\'), '/', $url);
        $parts = @explode('/', $url);
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return implode('/', $absolutes);
    }

    /**
     * @param $fromDir
     * @param $toDir
     * @param $filename
     * @param bool $import
     * @param null $oldValue
     * @param bool $noCopyFlag
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws Row
     */
    protected function _copyImageFile(
        $fromDir,
        $toDir,
        &$filename,
        $import = false,
        $oldValue = null,
        $noCopyFlag = false
    ) {
        $ds = '/';

        $remote = preg_match('#^https?:#', $filename??'');
        $isRemoteBatch = $remote && $this->_downloadRemoteImagesBatch;
        if ($remote) {
            if (!$this->_downloadRemoteImages) {
                // when image is remote, and remote images are not allowed, do nothing and reset imported value
                $this->_profile->getLogger()->warning($this->__('Skipping: %1, remote images download is disabled.',
                    $filename));
                $this->_profile->addValue('num_warnings');
                $filename = '';
                return true;
            }
            $filename = $this->convertToAbsoluteUrl($filename);
        }

        $basename = basename($filename??'');
        /*
        if ($remote) {
            $basename = basename(parse_url($filename, PHP_URL_PATH));
        }
        */

        $fromDir = rtrim($fromDir, '/\\');
        $toDir = rtrim($toDir, '/\\');
        if (null === $this->_exportImageRetainFolders) {
            $this->_exportImageRetainFolders = $this->_profile->getData('options/export/image_retain_folders')?:false;
        }
        if (!$import && $this->_exportImageRetainFolders) {
            $prefix = substr($filename??'', 0, -strlen($basename));
            $toDir = $toDir . $ds . trim($prefix, '/\\');
        }

        if ($import && $remote) {
            $slashPos = false;
            $fromFilename = $filename;
            $fromExists = true;
            $fromRemote = true;
            // if remote image and it has been already downloaded, use the existing file instead of downloading
            if (isset($this->_remoteImagesCache[$fromFilename])) {
                $filename = $this->_remoteImagesCache[$fromFilename]['name'];
                $this->_profile->getLogger()->warning($this->__('%1 is downloaded already, using local file: %2.',
                                                                $fromFilename, $filename));
                $fromFilename = $this->_remoteImagesCache[$fromFilename]['path'];
                $fromRemote = false;
                $fromExists = $isRemoteBatch || $this->fileHlp()->isReadable($fromFilename);
                $slashPos = strpos($filename, $ds);
            } else {  // remote file is not yet downloaded
                if ($this->_remoteImageSubfolderLevel) {
                    $filenameArr = @explode('/', $filename);
                    array_pop($filenameArr);
                    $filename = $basename;
                    for ($i = 0; $i < $this->_remoteImageSubfolderLevel; $i++) {
                        $filename = array_pop($filenameArr) . $ds . $filename;
                    }
                    $slashPos = strpos($filename, $ds);
                    $filename = str_replace(
                        [' ', '%', '?',],
                        '-',
                        urldecode($filename));
                } else {
                    $filename = $basename;
                    $filename = str_replace(
                        [' ', '%', '?', '/'],
                        '-',
                        urldecode($filename));
                }
            }
        } else {
            $slashPos = strpos($filename??'', $ds);
            $fromFilename = $fromDir . $ds . ltrim($filename??'', $ds);
            /*
            if ($import && $slashPos===0) {
                // if importing and filename starts with slash, use only basename for source file
                $fromFilename = $fromDir.$ds.basename($filename);
            }
            */
            $fromExists = $this->fileHlp()->isReadable($fromFilename);
            $fromRemote = false;
        }

        if (!$remote && $this->fileHlp()->isDir($fromFilename)) {
            // swatch images are media type attribute but do not have actual image most of the time
            $this->_profile->getLogger()->warning(__('%1 is not valid file, skipping copy', $fromFilename));
            return true;
        }

        $warning = '';
        //error_log('4', 3, '/var/www/html/var/log/unirgy.log');
        $origBasename = basename($filename);
        $cleanBasename = $this->getCorrectFileName($origBasename);
        if ($origBasename!=$cleanBasename) {
            $filename = str_replace($origBasename, $cleanBasename, $filename);
            $warning .= __(' Corrected image name: %1.', $filename);
        }
        //$cleanFilename = $filename;
        $toFilename = $toDir . $ds . ltrim($filename, $ds);
        if ($import) {
            if ($slashPos === false) {
                $prefix = str_replace('\\', $ds, Uploader::getDispretionPath($filename));
                $toDir .= $ds . ltrim($prefix, $ds);
                $toFilename = rtrim($toDir, $ds) . $ds . ltrim($filename, $ds);
                $filename = $prefix . $ds . $filename;
            } elseif ($dirname = dirname($filename)) {
                $toDir .= $ds . ltrim($dirname, $ds);
            }
        } elseif (!$import && $slashPos === 0) {
            $toFilename = $toDir . $ds . basename($filename);
        }
        $toExists = $this->fileHlp()->isReadable($toFilename);

        $filename = $ds . ltrim($filename, $ds);

        if ($noCopyFlag) {
            return true;
        }

        if ($import && $toExists && $this->_existingImageAction) {
            $this->_profile->addValue('num_warnings');
            $warning .= __('Imported image file already exists.');
            if ($filename === $oldValue) {
                // new file name is same as current value
                $warning .= $this->__(' %1 is same as current value, %2.', $filename, $oldValue);
            } else {
                switch ($this->_existingImageAction) {
                    case 'skip':
                        $warning .= __(' Skipping field update');
                        $this->_profile->getLogger()->warning($warning);

                        return false;
                        break;
                    case 'replace' :
                        // basically just notify user that there is
                        $warning .= __(' Replacing existing image');
                        break;
                    case 'save_new':
                        $warning     .= __(' Updating image name and saving as new image.');
                        $toFilename  = $this->_getUniqueImageName($toFilename);
                        $newBasename = basename($toFilename);
                        $oldBasename = basename($filename);
                        if ($newBasename !== $oldBasename) {
                            $filename = str_replace($oldBasename, $newBasename, $filename);
                            $warning  .= __(' New image name: %1', $filename);
                        }
                        break;
                }
            }
            $this->_profile->getLogger()->warning($warning);
        } else if ($import && !$toExists) {
            // if have to import, but image is new
            $this->_getUniqueImageName($toFilename);
        }

        if (!$fromExists) {
            $warning = __('Source image file does not found: %1', $fromFilename);
//            $warning = __('Original file image does not exist');
            if ($this->_missingImageAction === 'error') {
                throw new Row($warning);
            } else {
                $result = false;
                switch ($this->_missingImageAction) {
                    case '':
                    case 'warning_save':
                        $result = true;
                        $warning .= '. ' . PHP_EOL . __('Image field set to: %1', $filename);
                        break;

                    case 'warning_skip':
                        $warning .= '. ' . __('Image field was not updated');
                        $filename = $oldValue; // set import value to be same as old value and avoid update
                        break;

                    case 'warning_empty':
                        $warning .= '. ' . __('Image field was reset');
                        $filename = '';
                        $result = true;
                        break;
                }
                $this->_profile->addValue('num_warnings');
                $this->_profile->getLogger()->warning($warning);

                return $result;
            }
        } elseif ($toExists && $fromExists && !$fromRemote
            && $filename === $oldValue
            && $this->fileHlp()->fileSize($fromFilename) === $this->fileHlp()->fileSize($toFilename)
        ) {
            // no need to copy
            return false;
        }

        $this->_directoryWriteFactory->create($toDir)->create();

        if ($fromRemote) {
            $error = null;
            try {
                if (!function_exists('curl_init')) {
                    throw new \Exception(__('Unable to locate curl module'));
                }
                if (!$this->_downloadRemoteImagesBatch) {
                    $__fromFilename = str_replace(' ', '%20', $fromFilename);
                    if (!($ch = curl_init($__fromFilename))) {
                        throw new \Exception(__('Unable to open remote file: %1', $fromFilename));
                    }
                    if ($this->_curlCustomRequest) curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->_curlCustomRequest);
                    if ($this->_curlUserAgent) curl_setopt($ch, CURLOPT_USERAGENT, $this->_curlUserAgent);
                    if ($this->_curlHeaders) {
                        curl_setopt($ch, CURLOPT_HEADER, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_curlHeaders);
                    }
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_curlConnectTimeout);
                    curl_setopt($ch, CURLOPT_TIMEOUT, $this->_curlTimeout);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
                    curl_setopt($ch, CURLOPT_NOBODY, 1);
                    curl_setopt($ch, CURLOPT_HEADER, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $headResult = curl_exec($ch);
                    if ($headResult === false) {
                        throw new \Exception(__('Testing for remote file "%1" fails', $fromFilename));
                    }
                    if (false !== strpos($headResult, '404 Not Found')) {
                        throw new \Exception(__('"404 Not Found" response for remote file: %1', $fromFilename));
                    }
                    if (false !== strpos($headResult, '400 Bad Request')) {
                        throw new \Exception(__('"400 Bad Request" response for remote file: %1', $fromFilename));
                    }
                    if (!($fp = fopen($toFilename, 'w'))) {
                        throw new \Exception(__('Unable to open local file for writing: %1', $toFilename));
                    }
                    curl_setopt($ch, CURLOPT_NOBODY, 0);
                    curl_setopt($ch, CURLOPT_HTTPGET, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
                    curl_setopt($ch, CURLOPT_FILE, $fp);
                    curl_setopt($ch, CURLOPT_HEADER, 0);

                    if (!curl_exec($ch)) {
                        throw new \Exception(__('Unable to fetch remote file: %1', $fromFilename));
                    }
                } else {
                    $this->_remoteImagesBatch[$fromFilename] = $toFilename;
                }
                $this->_remoteImagesCache[$fromFilename]['name'] = $filename;
                $this->_remoteImagesCache[$fromFilename]['path'] = $toFilename;
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
            if (!empty($ch)) {
                curl_close($ch);
            }
            if (!empty($fp)) {
                fclose($fp);
            }
            if (!$this->_downloadRemoteImagesBatch && !$this->_isValidImageFile($toFilename)) {
                @$this->fileHlp()->unlink($toFilename);
                unset($this->_remoteImagesCache[$fromFilename]);
                $error = __('Remote file is not an image: %1', $fromFilename);
            }

            if (!empty($error)) {
                $this->_profile->addValue('num_warnings');
                $this->_profile->getLogger()->warning($error);

                return false;
            }
        } else {
            if ($fromFilename === $toFilename && ($isRemoteBatch || $this->fileHlp()->fileSize($fromFilename) === $this->fileHlp()->fileSize($toFilename))) {
                return true; // do not try to copy same image over itself
            }
            if($toExists){
                @$this->fileHlp()->unlink($toFilename);
            }
            if (!@$this->fileHlp()->copy($fromFilename, $toFilename)) {
                $errors = error_get_last();
                $error = 'COPY ERROR: ';
                if ($errors && array_key_exists('type', $errors)) {
                    $error .= $errors['type'];
                }
                if ($errors && array_key_exists('message', $errors)) {
                    $error .= PHP_EOL . $errors['message'];
                }
                $this->_profile->addValue('num_warnings');
                $this->_profile->getLogger()->warning(__('Was not able to copy image file: %1', $error));

                return false;
            }
        }
        $eventVars = [
            'basename' => $basename,
            'filename' => $filename,
            'from_dir' => $fromDir,
            'from_filename' => $fromFilename,
            'from_remote' => $fromRemote,
            'to_dir' => $toDir,
            'to_exists' => $toExists,
            'import' => $import,
            'profile' => $this->_profile,
            'old_value' => $oldValue,
        ];

        $this->_eventManager->dispatch('urapidflow_copy_image_file_success', $eventVars);

        return true;
    }

    protected function _isValidImageFile($filename)
    {
        $validator = new \Zend\Validator\File\MimeType('image/png,image/jpeg,image/pjpeg,image/gif');
        try {
            $result = $validator->isValid($filename);
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    protected function _importProcessRemoteImageBatch()
    {

        if (!$this->_remoteImagesBatch) {
            return;
        }
        $t = microtime(1);
        $mh = curl_multi_init();
        $files = [];
        $handles = [];
        foreach ($this->_remoteImagesBatch as $fromFilename => $toFilename) {
            try {
                $__fromFilename = str_replace(' ', '%20', $fromFilename);
                if (!($ch = curl_init($__fromFilename))) {
                    throw new \Exception(__('Unable to open remote file: %1', $fromFilename));
                }
                if (!($fp = fopen($toFilename, 'w'))) {
                    throw new \Exception(__('Unable to open local file for writing: %1', $toFilename));
                }
                //error_log("STARTED: {$fromFilename} => {$toFilename}\n", 3, '/var/www/html/var/log/unirgy.log');
                if ($this->_curlCustomRequest) curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->_curlCustomRequest);
                if ($this->_curlUserAgent) curl_setopt($ch, CURLOPT_USERAGENT, $this->_curlUserAgent);
                if ($this->_curlHeaders) {
                    curl_setopt($ch, CURLOPT_HEADER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_curlHeaders);
                }
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_curlConnectTimeout);
                curl_setopt($ch, CURLOPT_TIMEOUT, $this->_curlTimeout);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.5.4');
                curl_setopt($ch, CURLOPT_NOBODY, 0);
                curl_setopt($ch, CURLOPT_HTTPGET, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                $files[] = $fp;
                $handles[] = $ch;
                curl_multi_add_handle($mh, $ch);
            } catch (\Exception $e) {
                $this->_profile->getLogger()->warning($e->getMessage());
            }
        }

        $active = null;
        do {
            $mrc = curl_multi_exec($mh, $active);
            //error_log('1', 3, '/var/www/html/var/log/unirgy.log');
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($mh) == -1) {
                usleep(100);
            }
            do {
                $mrc = curl_multi_exec($mh, $active);
                //error_log('2', 3, '/var/www/html/var/log/unirgy.log');
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }

        foreach ($handles as $ch) {
            curl_multi_remove_handle($mh, $ch);
            //error_log('0', 3, '/var/www/html/var/log/unirgy.log');
        }
        curl_multi_close($mh);
        foreach ($files as $fp) {
            fclose($fp);
        }
        //error_log("\nTOTAL TIME: " . (microtime(1) - $t) . "\n", 3, '/var/www/html/var/log/unirgy.log');
        $this->_remoteImagesBatch = [];
    }

    /**
     * @param string $toFilename
     *
     * @return mixed|string
     */
    protected function _getUniqueImageName($toFilename)
    {
        $fileInfo = pathinfo($toFilename);
        $newName = Uploader::getNewFileName($toFilename);
        $extension = isset($fileInfo['extension'])? '.' . $fileInfo['extension']: '';
        $toFilename = str_replace($fileInfo['filename'] . $extension, $newName, $toFilename);

        return $toFilename;
    }

    /**
     * @param $filename
     * @param $toDir
     * @param bool $noCopyFlag
     * @return bool
     * @throws Row
     */
    protected function _validateImageFile(&$filename, $toDir, $noCopyFlag = false)
    {
        $ds = '/';
        if (($slashPos = strpos($filename, $ds)) !== false) {
            $filename = ltrim($filename, $ds);
        }
        $result = false;
        if ($this->fileHlp()->isFileExists($toDir . $ds . $filename) && !$this->fileHlp()->isDir($toDir . $ds . $filename)) {
            $filename = $ds . ltrim($filename, $ds);
            $result = true;
        } elseif ($slashPos === false) {
            $prefix = str_replace('\\', $ds, Uploader::getDispretionPath($filename));
            $tempFilename = $ds . trim($prefix, $ds) . $ds . ltrim($filename, $ds);
            if ($this->fileHlp()->isFileExists($toDir . $tempFilename) && !$this->fileHlp()->isDir($toDir . $tempFilename)) {
                $filename = $tempFilename;
                $result = true;
            }
        } else {
            $filename = $ds . ltrim($filename, $ds);
        }

        if ($result || $noCopyFlag) {
            return $result;
        }

        $warning = __('Related file image is not in destination media folder');
        if ($this->_missingImageAction === 'error') {
            throw new Row($warning);
        }
        $result = false;
        switch ($this->_missingImageAction) {
            case '':
            case 'warning_save':
                $result = true;
                break;

            case 'warning_skip':
                $warning .= '. ' . __('Image field was not updated');
                break;

            case 'warning_empty':
                $warning .= '. ' . __('Image field was reset');
                $filename = null;
                $result = true;
                break;
        }
        $this->_profile->addValue('num_warnings');
        $this->_profile->getLogger()->warning($warning);

        return $result;
    }

    /**
     * @param string $value
     * @return array|string
     * @throws \Exception
     */
    protected function _convertEncoding($value)
    {
        if ($value && $this->_encodingFrom && $this->_encodingTo && $this->_encodingFrom != $this->_encodingTo) {
            /*
            $from = $this->_encodingFrom;
            if ($this->_encodingFrom=='auto') {
                $from = mb_detect_encoding($value.'a', 'auto');
                if (!$from) {
                    $from = 'UTF-8';
                }
            }
            */
            if (is_array($value)) {
                foreach ($value as $i => $v) {
                    $value[$i] = $this->_convertEncoding($v);
                }
            } else {
                $encodingTo = $this->_encodingTo . ($this->_encodingIllegalChar ? '//' . $this->_encodingIllegalChar : '');
                try {
                    $value1 = iconv($this->_encodingFrom, $encodingTo, $value);
                } catch (\Exception $e) {
                    if (strpos($e->getMessage(), 'Detected an illegal character in input string') !== false) {
                        $this->_profile->addValue('num_warnings');
                        $this->_profile->getLogger()->warning(__('Illegal character in string: %1', $value));
                        $value1 = $value;
                    } else {
                        throw $e;
                    }
                }
                $value = $value1;
            }
        }

        return $value;
    }

    /**
     * @param $pIds
     * @param bool $useKeys
     * @return $this
     */
    protected function _refreshHasOptionsRequiredOptions($pIds, $useKeys = true)
    {
        if (!empty($pIds)) {
            $entityId = $this->_entityIdField;

            if ($useKeys) {
                $pIds = array_keys($pIds);
            }
            $horoSelect = $this->_write->select()
                ->from(['p' => $this->_t(self::TABLE_CATALOG_PRODUCT_ENTITY)], [$entityId])
                ->joinLeft(['po' => $this->_t(self::TABLE_CATALOG_PRODUCT_OPTION)],
                           'po.product_id=p.' . $entityId, [])
                ->where("p.{$entityId} in (?)", $pIds)
                ->group("p.{$entityId}")
                ->columns('sum(IF(po.option_id is not null, 1, 0)) as has_options');
            $horoSelect->columns('sum(IF(po.option_id is not null and po.is_require!=0, 1, 0)) as required_options');
            $horoRows = $this->_write->fetchAll($horoSelect);

            $horoSelect = $this->_write->select()
                ->from(['p' => $this->_t(self::TABLE_CATALOG_PRODUCT_ENTITY)], [$entityId])
                ->joinLeft(['po' => $this->_t(self::TABLE_CATALOG_PRODUCT_SUPER_ATTRIBUTE)],
                           "po.product_id=p.{$entityId}", [])
                ->where("p.{$entityId} in (?)", $pIds)
                ->where('p.type_id=?', 'configurable')
                ->group("p.{$entityId}")
                ->columns('sum(IF(po.product_super_attribute_id is not null, 1, 0)) as has_options');
            $horoSelect->columns('sum(IF(po.product_super_attribute_id is not null, 1, 0)) as required_options');

            foreach ($this->_write->fetchAll($horoSelect) as $horo) {
                foreach ($horoRows as &$_horo) {
                    if ($_horo[$entityId] == $horo[$entityId]) {
                        $_horo['has_options'] = max($_horo['has_options'], $horo['has_options']);
                        $_horo['required_options'] = max($_horo['required_options'], $horo['required_options']);
                        break;
                    }
                }
                unset($_horo);
            }

            $horoSelect = $this->_write->select()
                ->from(['p' => $this->_t(self::TABLE_CATALOG_PRODUCT_ENTITY)], [$entityId])
                ->joinLeft(['po' => $this->_t(self::TABLE_CATALOG_PRODUCT_BUNDLE_OPTION)], "po.parent_id=p.{$entityId}",
                           [])
                ->where("p.{$entityId} in (?)", $pIds)
                ->where('p.type_id=?', 'bundle')
                ->group("p.{$entityId}")
                ->columns('sum(IF(po.option_id is not null, 1, 0)) as has_options');
            $horoSelect->columns('sum(IF(po.option_id is not null and po.required!=0, 1, 0)) as required_options');
            foreach ($this->_write->fetchAll($horoSelect) as $horo) {
                foreach ($horoRows as &$_horo) {
                    if ($_horo[$entityId] == $horo[$entityId]) {
                        $_horo['has_options'] = max($_horo['has_options'], $horo['has_options']);
                        $_horo['required_options'] = max($_horo['required_options'], $horo['required_options']);
                        break;
                    }
                }
                unset($_horo);
            }

            $query = 'UPDATE ' . $this->_t(self::TABLE_CATALOG_PRODUCT_ENTITY) . ' SET ';
            $hoSql = "`has_options`=CASE `{$entityId}`";
            $roSql = '';
            $roSql = ", `required_options`=CASE `{$entityId}`";
            foreach ($horoRows as $horo) {
                $hoSql .= $this->_write->quoteInto(' WHEN ? ', $horo[$entityId]);
                $hoSql .= $this->_write->quoteInto(' THEN ? ', $horo['has_options'] > 0 ? 1 : 0);
                $roSql .= $this->_write->quoteInto(' WHEN ? ', $horo[$entityId]);
                $roSql .= $this->_write->quoteInto(' THEN ? ', $horo['required_options'] > 0 ? 1 : 0);
            }
            $hoSql .= ' ELSE `has_options` END';
            $roSql .= ' ELSE `required_options` END';

            $query .= $hoSql;
            $query .= $roSql;
            $query .= $this->_write->quoteInto(" WHERE `{$entityId}` IN (?)", $pIds);

            $this->_write->query($query);

        }

        return $this;
    }

    /**
     * @param array $row
     * @param string $dataKey
     * @param string $parentKey
     * @return string
     * @throws LocalizedException
     */
    protected function catBuildPath($row, $dataKey = 'url_key', $parentKey = 'url_key')
    {
        $path = null;
        $eId = isset($row[$this->_entityIdField]) ? $row[$this->_entityIdField] : null;
        $rcID = $this->_getRootCatId();
        $rootPath = $rcID ? '1/' . $rcID . '/' : '1/';
        $entities = $this->_getCategoryUrlEntities();
        if (!empty($row[$dataKey])) {
            //$rootPath    = $this->_rootCatId ? '1/' . $this->_rootCatId . '/' : '1/';
            $ancestorIds = @explode('/', str_replace($rootPath, '', $row['path']));
            $eId = array_pop($ancestorIds); // remove current cat id
            $urlKeys = [];
            if ($row['path'].'/'!=$rootPath) {
                foreach ($ancestorIds as $aid) {
                    if (!isset($entities[$aid])) {
                        // maybe it is EE
                        if ($this->_categoriesBySeqId && isset($this->_categoriesBySeqId[$aid])) {
                            $aid = $this->_categoriesBySeqId[$aid][$this->_entityIdField];
                        }

                        if (!isset($entities[$aid])) {
                            $this->_profile->getLogger()
                                ->warning(sprintf('Parent category with id: %s not found. Category id: %s', $aid,
                                    $eId));

                            return $path;
                        }
                    }
                    $ancestor = $entities[$aid];
                    $urlKeys[] = isset($ancestor[$parentKey]) ? $ancestor[$parentKey] : @$ancestor[0][$parentKey];
                }
            }
            $urlKeys[] = $row[$dataKey];
            if (!empty($urlKeys)) {
                $path = implode("/", $urlKeys);
            }
        } elseif (!(!empty($row['path']) && $row['path'] === '1' || $row['path'] === "1/{$rcID}")) {
            $this->_profile->getLogger()->warning(sprintf('Category: %s is missing url_key: ' . print_r($row, 1) . ', '. $rootPath, $eId ?: 'N/A'));
        }

        return $path;
    }

    /**
     * @return null|string
     */
    protected function _getRootCatId()
    {
        if (null === $this->_rootCatId) {
            $storeId = $this->_profile->getStoreId();
            if ($storeId) {
                $this->_rootCatId = $this->_storeManager->getStore($storeId)->getGroup()->getRootCategoryId();
            } else {
                $this->_rootCatId = $this->_read->fetchOne("SELECT g.root_category_id FROM {$this->_t('store_website')} w INNER JOIN {$this->_t('store_group')} g ON g.group_id=w.default_group_id WHERE w.is_default=1");
            }
        }

        return $this->_rootCatId;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getCategoryUrlEntities()
    {
        if (!$this->_categoryUrlEntities) {
            $eav = $this->_eavModelConfig;
            $categories = [];
            $storeId = $this->_profile->getStoreId();
            $table = $this->_t('catalog_category_entity');
            foreach (array('url_key', 'url_path') as $k) {
                $attribute = $eav->getAttribute('catalog_category', $k);
                $attrId = $attribute->getAttributeId();
                // fetch attribute values for all categories
                $tableName = $attribute->getBackendTable() ?: $table . '_varchar';
                $sql = "SELECT {$this->_entityIdField}, value FROM {$tableName} WHERE attribute_id={$attrId} AND store_id IN (0, {$storeId}) ORDER BY store_id DESC";
                foreach ($this->_read->fetchAll($sql) as $r) {
                    // load values for specific store OR default
                    if (empty($categories[$r[$this->_entityIdField]][$k])) {
                        $categories[$r[$this->_entityIdField]][$k] = $r['value'];
                    }
                }
            }
            $this->_categoryUrlEntities = $categories;
        }

        return $this->_categoryUrlEntities;
        // todo, fetch data with category entity_id, url_key, url_path columns
    }

    /**
     * @param array $attr
     * @param string $baseTableAbstract
     * @return string
     */
    protected function getAttrType($attr, $baseTable = "catalog_product_entity")
    {
        $type = $attr['backend_type'];
        $baseTable = $this->_t($baseTable);
        if (!empty($attr['backend_table'])) {
            $attrTable = $this->_t($attr['backend_table']);
            $diff = str_ireplace($baseTable . "_", "", $attrTable);
            if (!empty($diff)) {
                $type = $diff;
            }
        }
        if (empty($this->_tablesByType[$type])) {
            $this->_tablesByType[$type] = !empty($attrTable) ? $attrTable : "{$baseTable}_{$attr['backend_type']}";
        }
        return $type;
    }

    protected function getAttrTable($attr, $baseTable = "catalog_product_entity")
    {
        $baseTable = $this->_t($baseTable);
        if (!empty($attr['backend_table'])) {
            $attrTable = $this->_t($attr['backend_table']);
        }
        return !empty($attrTable) ? $attrTable : "{$baseTable}_{$attr['backend_type']}";
    }


    protected function _getNextProductSequence($dryRun = false)
    {
        $tableName = $this->_t(static::TABLE_PRODUCT_SEQUENCE);
        return !$dryRun? $this->_getNextSequence($tableName): 1;
    }

    protected function _getNextCategorySequence($dryRun = false)
    {
        $tableName = $this->_t(static::TABLE_CATEGORY_SEQUENCE);
        return !$dryRun ? $this->_getNextSequence($tableName) : 1;
    }

    abstract public function import();

    abstract public function export();

    /**
     * @param string $tableName
     * @return string
     * @throws \Zend_Db_Adapter_Exception
     */
    protected function _getNextSequence($tableName)
    {
        $this->_write->insert($tableName, []);
        return $this->_write->lastInsertId($tableName);
    }

    public function getCorrectFileName($fileName)
    {
        $fileName = preg_replace('/[^a-z0-9_\\-\\.]+/i', '_', $fileName);
        $fileInfo = pathinfo($fileName);

        if (preg_match('/^_+$/', $fileInfo['filename'])) {
            $fileName = 'file';
            if (isset($fileInfo['extension'])) {
                $fileName .= '.'.$fileInfo['extension'];
            }
        }
        return $fileName;
    }
    protected function startStat($timerId)
    {
        $this->_stat->start($timerId, microtime(true), memory_get_usage(true), memory_get_usage());
    }
    protected function stopStat($timerId)
    {
        $this->_stat->stop($timerId, microtime(true), memory_get_usage(true), memory_get_usage());
    }
    protected function logStat($timerId, $stop=false)
    {
        if ($stop) {
            $this->stopStat($timerId);
        }
        $this->_logger->debug(sprintf(
            '%s: %s, %s, %s',
            $timerId,
            $this->_stat->fetch($timerId, 'sum'),
            $this->_stat->fetch($timerId, 'realmem'),
            $this->_stat->fetch($timerId, 'emalloc')
        ));
    }
    protected function logDebug($msg)
    {
        $msg = !is_scalar($msg)?var_export($msg,1):$msg;
        if ($this->benchmark==2) {
            $this->_profile->activity($msg);
        }
        $this->_logger->debug($msg);
    }
    protected function logAllStat()
    {
        //foreach ($this->_stat->getFilteredTimerIds(['sum'=>1]) as $timerId) {
        foreach ($this->_stat->getFilteredTimerIds() as $timerId) {
            $msg = sprintf(
                'PROFILER STAT [%s]: %s, %s, %s',
                $timerId,
                $this->_stat->fetch($timerId, 'sum'),
                $this->_stat->fetch($timerId, 'realmem'),
                $this->_stat->fetch($timerId, 'realmem')
            );
            if ($this->benchmark==2) {
                $this->_profile->activity($msg);
            }
            $this->_logger->debug($msg);
        }
    }
    protected function addProductForImageCacheFlush($sku)
    {
        if(!array_key_exists($sku, $this->_mediaUpdates)){
            $this->_mediaUpdates[$sku] = 1;
        }
    }
    protected function getProductsForImageCacheFlush()
    {
        return array_unique(array_keys($this->_mediaUpdates));
    }
    protected function resetProductsForImageCacheFlush()
    {
        $this->_mediaUpdates = [];
    }
    protected function _enqueueImageCacheFlush()
    {
        $productsForImageCacheFlush = $this->getProductsForImageCacheFlush();
        $this->resetProductsForImageCacheFlush();
        if(count($productsForImageCacheFlush) === 0){
            return;
        }
        $productIds = [];
        foreach ($productsForImageCacheFlush as $sku) {
            if (!isset($this->_skus[$sku])) {
                $this->_profile->getLogger()->warning($this->__('Product id for %1 not found', $sku));
                continue;
            }
            $productIds[$sku] = $this->_skus[$sku];
        }
        foreach ($productIds as $sku => $productId) {
            $this->_imageCacheHelper->addProductIdForFlushCache($productId);
        }
    }
    protected function addProductForCacheFlush($sku)
    {
        if(!array_key_exists($sku, $this->_productUpdates)){
            $this->_productUpdates[$sku] = 1;
        }
    }
    protected function getProductsForCacheFlush()
    {
        return array_unique(array_keys($this->_productUpdates));
    }
    protected function resetProductsForCacheFlush()
    {
        $this->_productUpdates = [];
    }
    protected function _enqueueProductCacheFlush()
    {
        $productsForCacheFlush = $this->getProductsForCacheFlush();
        $this->resetProductsForCacheFlush();
        if(count($productsForCacheFlush) === 0){
            return;
        }
        $productIds = [];
        foreach ($productsForCacheFlush as $sku) {
            if (!isset($this->_skus[$sku])) {
                $this->_profile->getLogger()->warning($this->__('Product id for %1 not found', $sku));
                continue;
            }
            $productIds[$sku] = $this->_skus[$sku];
        }
        foreach ($productIds as $sku => $productId) {
            $this->_productCacheHelper->addProductIdForFlushCache($productId);
        }
    }

    protected function extractStockAttrValue($oldData, $newData, $defaultValue, $pId, $storeId, $key)
    {
        $__ucKey = 'stock.use_config_'.$key;
        $__key = 'stock.'.$key;
        $oldUseConfig = !$pId ? null : $this->extractProductAttrValue(
            $oldData, $pId, $storeId, $__ucKey
        );
        $oldValue = !$pId ? null : $this->extractProductAttrValue(
            $oldData, $pId, $storeId, $__key
        );
        $newUseConfig = array_key_exists($__ucKey, $newData) ? $newData[$__ucKey] : null;
        $newValue = array_key_exists($__key, $newData) ? $newData[$__key] : null;;
        $value = $defaultValue;
        $useConfig = $newUseConfig!==null ? $newUseConfig : ($oldUseConfig!==null ? $oldUseConfig : true);
        if (!$useConfig) {
            $value = $newValue!==null ? $newValue : $oldValue;
        }
        return $value;
    }
    protected function extractProductAttrValue($data, $pId, $storeId, $key)
    {
        return isset($data[$pId][$storeId][$key]) ? $data[$pId][$storeId][$key] : (
            isset($data[$pId][0][$key]) ? $data[$pId][0][$key] : null
        );
    }

    protected function valueToStr($value)
    {
        $strVal = (string)$value;
        if ($value===0) {
            $strVal = '0';
        } elseif ($value===false) {
            $strVal = 'false';
        }
        return $strVal;
    }
    protected function isValueEmpty($value)
    {
        $value = is_array($value) ? implode(',',$value) : $value;
        return $this->valueToStr($value)===(string)$this->_profile->getData('options/import/empty_attribute_const');
    }
    protected function isValueSkip($value)
    {
        $value = is_array($value) ? implode(',',$value) : $value;
        return $this->valueToStr($value)===''
            && '' !== (string)$this->_profile->getData('options/import/empty_attribute_const');
    }
    public function reindexPids($pIds, $indexerIds=null, $loggerCallback=null)
    {
        if (empty($pIds) || !is_array($pIds)) return $this;
        if ($indexerIds===null) {
            $indexerIds = [
                \Magento\CatalogInventory\Model\Indexer\Stock\Processor::INDEXER_ID,
                \Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID,
                \Magento\Catalog\Model\Indexer\Product\Flat\Processor::INDEXER_ID,
                \Magento\Catalog\Model\Indexer\Product\Eav\Processor::INDEXER_ID,
                \Magento\Catalog\Model\Indexer\Product\Category\Processor::INDEXER_ID,
                \Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID
            ];
            if ($this->_rapidFlowHelper->isModuleActive('Unirgy_Dropship')) {
                $indexerIds[] = 'udropship_product_vendor';
                $indexerIds[] = 'udropship_product_vendor_limit';
            }
            if ($this->_rapidFlowHelper->hasMageFeature('msi')) {
                $indexerIds[] = 'inventory';
            }
        }
        /* @var \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry */
        $indexerRegistry = ObjectManager::getInstance()->get('\Magento\Framework\Indexer\IndexerRegistry');
        /* @var \Magento\Indexer\Model\Config $indexerConfig */
        $indexerConfig = ObjectManager::getInstance()->get('\Magento\Indexer\Model\Config');

        foreach ($indexerIds as $indexerId) {
            if (!$indexerConfig->getIndexer($indexerId)) {
                if ($loggerCallback) $loggerCallback($indexerId, 'not loaded');
                continue;
            }
            $indexer = $indexerRegistry->get($indexerId);
            if ($indexer && !$indexer->isScheduled()) {
                if ($loggerCallback) $loggerCallback($indexerId, 'running');
                $indexer->reindexList($this->_mapIndexerProductIds($indexerId, $pIds));
            }  else if (!$indexer) {
                if ($loggerCallback) $loggerCallback($indexerId, 'not found');
            } else if ($indexer->isScheduled()) {
                if ($loggerCallback) $loggerCallback($indexerId, 'is scheduled');
            }
        }
        return $this;
    }
    protected function _mapIndexerProductIds($indexer, $productIds)
    {
        $mapIds = $productIds;
        if ($indexer=='inventory') {
            $conn = $this->_read;
            $skus = $conn->fetchCol($conn->select()
                ->from($this->_res->getTableName('catalog_product_entity'), ['sku'])
                ->where('entity_id in (?)', $productIds)
            );
            $mapIds = $conn->fetchCol($conn->select()
                ->from($this->_res->getTableName('inventory_source_item'), ['source_item_id'])
                ->where('sku in (?)', $skus)
            );
        }
        return $mapIds;
    }
    /**
     * @return FileHelper
     */
    protected function fileHlp()
    {
        return ObjectManager::getInstance()->get(FileHelper::class);
    }
}
