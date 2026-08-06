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
 
namespace Unirgy\DropshipPo\Block\Adminhtml\Po\GridRenderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Unirgy\Dropship\Helper\Data as HelperData;

class VendorName extends AbstractRenderer
{
    /**
     * @var HelperData
     */
    protected $_hlp;

    public function __construct(Context $context, 
        HelperData $helperData, 
        array $data = [])
    {
        $this->_hlp = $helperData;

        parent::__construct($context, $data);
    }


    public function render(DataObject $row)
    {
        $vId = $row->getData($this->getColumn()->getIndex());
        $v = $this->_hlp->getVendor($vId);
        return $v->getId() == $vId ? $v->getVendorName() : $vId;
    }

}