<?php

namespace Unirgy\Dropship\Model;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Url as FrameworkUrl;
use Unirgy\Dropship\Helper\Data as DropshipHelper;

class Config
{
    /**
     * @var \Magento\Framework\App\Route\Config\Reader
     */
    protected $_reader;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $_cache;

    /**
     * @var string
     */
    protected $_cacheId;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_data;

    protected $_hlp;

    protected $_frontUrl;
    /**
     * @var ModuleList
     */
    protected $moduleList;

    public function __construct(
        DropshipHelper $udropshipHelper,
        ModuleList $moduleList,
        FrameworkUrl $frontUrl,
        Config\Reader $reader,
        CacheInterface $cache,
        $cacheId = 'UdropshipConfig'
    ) {
        $this->_hlp = $udropshipHelper;
        $this->_frontUrl = $frontUrl;
        $this->_reader = $reader;
        $this->_cache = $cache;
        $this->_cacheId = $cacheId;
        $this->moduleList = $moduleList;
    }

    public function getVersion()
    {
        $cacheId = 'udropship_version';
        $cacheLifetime = 43200;
        $version = $this->_cache->load($cacheId);
        if (!$version) {
            $moduleName = 'Unirgy_Dropship';
            if ($this->moduleList->has($moduleName)) {
                $moduleData = $this->moduleList->getOne($moduleName);
                if ($moduleData) {
                    $version = $moduleData['setup_version'];
                }
            }
            $this->_cache->save($version, $cacheId, [], $cacheLifetime);
        }
        return $version;
    }

    public function modLs()
    {
        $cacheId = 'unirgy_modules';
        $cacheLifetime = 43200;
        $modLs = $this->_cache->load($cacheId);
        if (!$modLs) {
            $names = [];
            $_names = $this->moduleList->getNames();
            foreach ($_names as $name) {
                if (0===strpos($name, 'Unirgy_')) {
                    $names[] = $name;
                }
            }
            $modLs = json_encode($names);
            $this->_cache->save($modLs, $cacheId, [], $cacheLifetime);
        }
        return $modLs;
    }

    protected function _loadData()
    {
        if ($this->_data===null) {
            $cacheId = $this->_cacheId;
            $cachedData = $this->_hlp->unserialize($this->_cache->load($cacheId));
            if (is_array($cachedData) && !empty($cachedData)) {
                $this->_initFromSource($cachedData);
                return $this;
            }
            $source = $this->_reader->read();
            $this->_initFromSource($source);
            $this->_cache->save(serialize($source), $cacheId);
        }
        return $this;
    }

    protected function _initFromSource($source)
    {
        $this->_translate = $source['translate'];
        $output = [];
        foreach ($source['output'] as $key => $value) {
            $this->_setArrayValue($output, $key, $value);
        }
        $this->_data = new \Magento\Framework\DataObject($output);
        return $this;
    }

    protected function _setArrayValue(array &$container, $path, $value)
    {
        $segments = explode('/', $path);
        $currentPointer = & $container;
        foreach ($segments as $segment) {
            if (!isset($currentPointer[$segment])) {
                $currentPointer[$segment] = [];
            }
            $currentPointer = & $currentPointer[$segment];
        }
        $currentPointer = $value;
    }

    public function setConfigData($path, $value)
    {
        $this->_data->setData($path, $value);
        return $this;
    }

    public function getUploadFields()
    {
        $uploadFields = [];
        foreach ($this->getField() as $code => $node) {
            if (in_array(@$node['type'], ['image','file'])) {
                $uploadFields[] = $code;
            }
        }
        return $uploadFields;
    }

    public function getStatementRefundTotalsLabels($calc=false)
    {
        return $this->_getStatementTotals(true,true, $calc);
    }
    public function getStatementRefundTotals($calc=false)
    {
        return $this->_getStatementTotals(true,false, $calc);
    }
    public function getStatementTotalsLabels($calc=false)
    {
        return $this->_getStatementTotals(false,true, $calc);
    }
    public function getStatementTotals($calc=false)
    {
        return $this->_getStatementTotals(false,false, $calc);
    }
    protected $_statementTotals=[];
    protected function _getStatementTotals($refunds=false,$labels=false,$calc=false)
    {
        $refunds = (bool)$refunds;
        $labels = (bool)$labels;
        $calc = (bool)$calc;
        $cacheKey = sprintf('%s-%s-%s', $refunds, $labels, $calc);
        if (!isset($this->_statementTotals[$cacheKey])) {
            $sKey = 'statement_totals';
            if ($calc) {
                if ($refunds) {
                    $sKey = 'statement_refund_calc_totals';
                } else {
                    $sKey = 'statement_calc_totals';
                }
            } elseif ($refunds) {
                $sKey = 'statement_refund_totals';
            }
            $totalsData = $this->_getData($sKey);
            uasort($totalsData, [$this->_hlp, 'usortByPosition']);
            $this->_statementTotals[$cacheKey] = [];
            foreach ($totalsData as $__k=>$__v) {
                if (!$labels) {
                    $val = 0;
                } else {
                    $val = __($__v['label']);
                }
                $this->_statementTotals[$cacheKey][$__k] = $val;
            }
        }
        return $this->_statementTotals[$cacheKey];
    }

    /**
     * @var \Magento\Framework\Data\Collection
     */
    protected $_vendorMenu;
    public function getVendorMenu()
    {
        if ($this->_vendorMenu==null) {
            $this->_vendorMenu = $this->_hlp->createObj('Magento\Framework\Data\Collection');
            $vendorMenu = $this->_getData('vendor_menu');
            if (is_array($vendorMenu)) {
                foreach ($vendorMenu as $id => $_data) {
                    $_data['id'] = $id;
                    $_data['url'] = $this->_frontUrl->getUrl($_data['url']);
                    $_data['title'] = __($_data['title']);
                    $_item = $this->_hlp->createObj('Magento\Framework\DataObject', ['data'=>$_data]);
                    $this->_vendorMenu->addItem($_item);
                }
            }
        }
        return $this->_vendorMenu;
    }
    public function getAdminRoles()
    {
        return $this->_getData('admin_roles');
    }
    public function getFieldset($name = null, $field = null)
    {
        return $this->_getData('vendor/fieldsets', $name, $field);
    }
    public function getField($name = null, $field = null)
    {
        return $this->_getData('vendor/fields', $name, $field);
    }
    public function getTrackApi($name = null, $field = null)
    {
        return $this->_getData('track_api', $name, $field);
    }
    public function getLabel($name = null, $field = null)
    {
        return $this->_getData('labels', $name, $field);
    }
    public function isStockInit($fv = null)
    {
        $c = $this->_cache;
        $fk = 'udflag1';
        return $fv===null?$c->load($fk):$c->save($fv, $fk, [], 85439);
    }
    public function getLabelType($name = null, $field = null)
    {
        return $this->_getData('label_types', $name, $field);
    }
    public function getAvailabilityMethod($name = null, $field = null)
    {
        return $this->_getData('availability_methods', $name, $field);
    }
    public function getStockcheckMethod($name = null, $field = null)
    {
        return $this->_getData('stockcheck_methods', $name, $field);
    }
    public function getBatchAdapter($type, $name = null, $field = null)
    {
        return $this->_getData('batch_adapters', $type.($name ? '/'.$name : ''), $field);
    }
    public function getNotificationMethod($name = null, $field = null)
    {
        return $this->_getData('notification_methods', $name, $field);
    }
    public function getProductState($name = null, $field = null)
    {
        return $this->_getData('product_state', $name, $field);
    }
    public function getPayoutMethod($name = null, $field = null)
    {
        return $this->_getData('payout/method', $name, $field);
    }
    protected function _getData($key, $name = null, $field = null)
    {
        $this->_loadData();
        if ($name===null) {
            $result = (array)$this->_data->getData($key);
        } else {
            $key .= '/'.$name;
            if ($field!==null) {
                $key .= '/'.$field;
            }
            $result = $this->_data->getData($key);
        }
        return $result;
    }
}
