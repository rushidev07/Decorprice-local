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
 
namespace Unirgy\DropshipPo\Block\Adminhtml\Po\Comments;

use Magento\Backend\Block\Template\Context;
use Magento\Sales\Block\Adminhtml\Order\Comments\View as CommentsView;
use Magento\Sales\Helper\Admin;
use Magento\Sales\Helper\Data as HelperData;
use Magento\Store\Model\ScopeInterface;
use Unirgy\DropshipPo\Helper\Data as DropshipPoHelperData;

class View extends CommentsView
{
    /**
     * @var DropshipPoHelperData
     */
    protected $_poHlp;

    public function __construct(
        DropshipPoHelperData $helperData,
        Context $context,
        HelperData $salesData, 
        Admin $adminHelper, 
        array $data = []
    )
    {
        $this->_poHlp = $helperData;

        parent::__construct($context, $salesData, $adminHelper, $data);
    }

    public function getStatuses()
    {
        $_statuses = $this->_poHlp->src()->setPath('po_statuses')->toOptionHash();
        if (!$this->_scopeConfig->isSetFlag('udropship/vendor/allow_forced_po_status_change', ScopeInterface::SCOPE_STORE)) {
            $_allowedPoStatuses = $this->_poHlp->getAllowedPoStatuses($this->getEntity(), false);
            $__statuses = [];
            foreach ($_statuses as $_status => $_statusLbl) {
                if (in_array($_status, $_allowedPoStatuses)) {
                    $__statuses[$_status] = $_statusLbl;
                }
            }
        } else {
            $__statuses = $_statuses;
        }
        return $__statuses;
    }
    public function canSendCommentEmail()
    {
        return false;
    }
}