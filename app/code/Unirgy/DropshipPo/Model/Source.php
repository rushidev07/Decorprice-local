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
 * @package    Unirgy_DropshipPo
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipPo\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Unirgy\DropshipPo\Helper\Data as HelperData;
use Unirgy\DropshipPo\Model\Source as ModelSource;
use Unirgy\Dropship\Helper\Data as DropshipHelperData;
use Unirgy\Dropship\Model\Source\AbstractSource;

class Source extends AbstractSource
{
    /**
     * @var HelperData
     */
    protected $_poHlp;

    /**
     * @var DropshipHelperData
     */
    protected $_hlp;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    public function __construct(
        HelperData $helperData,
        DropshipHelperData $dropshipHelperData,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    )
    {
        $this->_poHlp = $helperData;
        $this->_hlp = $dropshipHelperData;
        $this->_scopeConfig = $scopeConfig;

        parent::__construct($data);
    }

    const UDPO_STATUS_PENDING    = 0;
    const UDPO_STATUS_EXPORTED   = 10;
    const UDPO_STATUS_RETURNED   = 11;
    const UDPO_STATUS_ACK        = 9;
    const UDPO_STATUS_BACKORDER  = 5;
    const UDPO_STATUS_ONHOLD     = 4;
    const UDPO_STATUS_READY      = 3;
    const UDPO_STATUS_PARTIAL    = 2;
    const UDPO_STATUS_SHIPPED    = 1;
    const UDPO_STATUS_CANCELED   = 6;
    const UDPO_STATUS_DELIVERED  = 7;

    const UDPO_STATUS_STOCKPO_READY   = 11;
    const UDPO_STATUS_STOCKPO_EXPORTED = 12;
    const UDPO_STATUS_STOCKPO_RECEIVED = 13;

    const UDPO_INCREMENT_NATIVE      = 'native';
    const UDPO_INCREMENT_ORDER_BASED = 'order_based';
    const UDPO_INCREMENT_VENDOR_BASED = 'vendor_based';

    const SHIPMENT_INCREMENT_NATIVE      = 'native';
    const SHIPMENT_INCREMENT_ORDER_BASED = 'order_based';
    const SHIPMENT_INCREMENT_PO_BASED    = 'po_based';

    const AUTOINVOICE_SHIPMENT_NO = 0;
    const AUTOINVOICE_SHIPMENT_YES = 1;
    const AUTOINVOICE_SHIPMENT_ORDER = 2;

    public function getAllowedPoStatusesForPartialShipped()
    {
        return [
            self::UDPO_STATUS_BACKORDER,
            self::UDPO_STATUS_ONHOLD,
            self::UDPO_STATUS_PARTIAL,
            self::UDPO_STATUS_RETURNED
        ];
    }

    public function getAllowedPoStatusesForShipped($auto=false)
    {
        $statuses = [
            self::UDPO_STATUS_SHIPPED,
        ];
        if (!$auto) {
            $statuses[] = self::UDPO_STATUS_DELIVERED;
        }
        return $statuses;
    }

    public function getAllowedPoStatusesForDelivered()
    {
        return [
            self::UDPO_STATUS_DELIVERED,
        ];
    }

    public function getAllowedPoStatusesForCanceled()
    {
        return [
            self::UDPO_STATUS_CANCELED,
        ];
    }

    public function getNonSecurePoStatuses()
    {
        return [
            self::UDPO_STATUS_PENDING,
            self::UDPO_STATUS_EXPORTED,
            self::UDPO_STATUS_ACK,
            self::UDPO_STATUS_BACKORDER,
            self::UDPO_STATUS_ONHOLD,
            self::UDPO_STATUS_READY,
            self::UDPO_STATUS_PARTIAL,
            self::UDPO_STATUS_STOCKPO_READY,
            self::UDPO_STATUS_STOCKPO_EXPORTED,
            self::UDPO_STATUS_STOCKPO_RECEIVED,
            self::UDPO_STATUS_RETURNED
        ];
    }

    public function getAllPoStatuses()
    {
        $options = [
            self::UDPO_STATUS_PENDING    => __('Pending'),
            self::UDPO_STATUS_EXPORTED   => __('Exported'),
            self::UDPO_STATUS_ACK        => __('Acknowledged'),
            self::UDPO_STATUS_BACKORDER  => __('Backorder'),
            self::UDPO_STATUS_ONHOLD     => __('On Hold'),
            self::UDPO_STATUS_READY      => __('Ready to Ship'),
            self::UDPO_STATUS_PARTIAL    => __('Partially Shipped'),
            self::UDPO_STATUS_SHIPPED    => __('Shipped'),
            self::UDPO_STATUS_DELIVERED  => __('Delivered'),
            self::UDPO_STATUS_CANCELED   => __('Canceled'),
            self::UDPO_STATUS_RETURNED   => __('Returned'),
        ];
        if ($this->_hlp->isModuleActive('Unirgy_DropshipStockPo')) {
            $options[self::UDPO_STATUS_STOCKPO_READY]   = __('Ready for stock PO');
            $options[self::UDPO_STATUS_STOCKPO_EXPORTED]   = __('Exported stock PO');
            $options[self::UDPO_STATUS_STOCKPO_RECEIVED]   = __('Received stock PO');
        }
        return $options;
    }

    public function toOptionHash($selector=false)
    {
        $hlp = $this->_poHlp;

        $options = [];

        switch ($this->getPath()) {

        case 'udropship/stockpo/generate_on_po_status':
    	case 'udropship/batch/export_on_po_status':
        case 'udropship/purchase_order/default_po_status':
        case 'udropship/purchase_order/default_virtual_po_status':
        case 'udropship/vendor/restrict_udpo_status':
        case 'udropship/pocombine/notify_on_status':
        case 'udropship/pocombine/after_notify_status':
        case 'udropship/statement/statement_po_status':
        case 'batch_export_orders_export_on_po_status':
        case 'statement_po_status':
        case 'po_statuses':
        case 'notify_by_udpo_status':
        case 'initial_po_status':
        case 'initial_virtual_po_status':
        case 'vendor_po_grid_status_filter':
            $options = $this->getAllPoStatuses();
            if (in_array($this->getPath(), ['initial_po_status','statement_po_status','initial_virtual_po_status','batch_export_orders_export_on_po_status'])) {
                $options = ['999' => __('* Default (global setting)')] + $options;
            }
            break;

        case 'udropship/purchase_order/po_increment_type':
        case 'po_increment_types':
            $options = [
                self::UDPO_INCREMENT_NATIVE      => __('Magento Native'),
                self::UDPO_INCREMENT_ORDER_BASED => __('Order Based'),
                self::UDPO_INCREMENT_VENDOR_BASED => __('Vendor Based'),
            ];
            break;

        case 'udropship/purchase_order/autoinvoice_shipment':
            $options = [
                ModelSource::AUTOINVOICE_SHIPMENT_NO => __('No'),
                ModelSource::AUTOINVOICE_SHIPMENT_YES => __('Yes'),
                ModelSource::AUTOINVOICE_SHIPMENT_ORDER => __('Trigger whole order invoice'),
            ];
            break;

        case 'udropship/purchase_order/shipment_increment_type':
        case 'shipment_increment_types':
            $options = [
                self::SHIPMENT_INCREMENT_NATIVE      => __('Magento Native'),
                self::SHIPMENT_INCREMENT_ORDER_BASED => __('Order Based'),
                self::SHIPMENT_INCREMENT_PO_BASED    => __('PO Based'),
            ];
            break;

        case 'vendor_po_grid_sortby':
            $options = [
                'order_increment_id' => __('Order ID'),
                'increment_id' => __('PO ID'),
                'order_date' => __('Order Date'),
                'po_date' => __('PO Date'),
                'shipping_method' => __('Delivery Method'),
                'udropship_status' => __('PO Status'),
            ];
            break;

        case 'new_order_notifications':
            $options = [
                '' => __('* No notification'),
                '1' => __('* Email notification'),
                '-1' => __('* Email notification By Status'),
            ];
            $config = $this->_hlp->config()->getNotificationMethod();
            foreach ($config as $code=>$node) {
                if (!$node['label']) {
                    continue;
                }
                $options[$code] = __((string)$node['label']);
            }
            asort($options);
            break;

        default:
            throw new \Exception(__('Invalid request for source options: '.$this->getPath()));
        }

        if ($selector) {
            $options = [''=>__('* Please select')] + $options;
        }

        return $options;
    }

}
