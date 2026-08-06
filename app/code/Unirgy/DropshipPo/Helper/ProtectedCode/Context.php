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
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipPo\Helper\ProtectedCode;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Unirgy\DropshipPo\Helper\Data as DropshipPoHelperData;
use Unirgy\DropshipPo\Model\Source;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\Dropship\Helper\ProtectedCode as HelperProtectedCode;
use Unirgy\Dropship\Model\Source as ModelSource;

class Context
{
    /**
     * @var HelperData
     */
    public $_hlp;

    /**
     * @var RequestInterface
     */
    public $_request;

    /**
     * @var HelperProtectedCode
     */
    public $_hlpPr;

    /**
     * @var DropshipPoHelperData
     */
    public $_poHlp;

    /**
     * @var StoreManagerInterface
     */
    public $_storeManager;

    /**
     * @var ScopeConfigInterface
     */
    public $_scopeConfig;

    /**
     * @var ManagerInterface
     */
    public $_eventManager;

    public $sequenceManager;

    public $sequenceBuilder;

    public $sequenceConfig;

    public function __construct(
        \Unirgy\Dropship\Helper\Data $helperData,
        \Magento\Framework\App\RequestInterface $appRequestInterface,
        \Unirgy\Dropship\Helper\ProtectedCode $helperProtectedCode,
        \Unirgy\DropshipPo\Helper\Data $dropshipPoHelperData,
        \Magento\Store\Model\StoreManagerInterface $modelStoreManagerInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $configScopeConfigInterface,
        \Magento\Framework\Event\ManagerInterface $eventManagerInterface,
        \Magento\SalesSequence\Model\Builder $sequenceBuilder,
        \Magento\SalesSequence\Model\Config $sequenceConfig,
        \Magento\SalesSequence\Model\Manager $sequenceManager
    )
    {
        $this->_hlp = $helperData;
        $this->_request = $appRequestInterface;
        $this->_hlpPr = $helperProtectedCode;
        $this->_poHlp = $dropshipPoHelperData;
        $this->_storeManager = $modelStoreManagerInterface;
        $this->_scopeConfig = $configScopeConfigInterface;
        $this->_eventManager = $eventManagerInterface;
        $this->sequenceManager = $sequenceManager;
        $this->sequenceBuilder = $sequenceBuilder;
        $this->sequenceConfig = $sequenceConfig;

    }
}