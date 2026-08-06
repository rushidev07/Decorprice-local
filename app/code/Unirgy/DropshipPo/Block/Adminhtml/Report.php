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

namespace Unirgy\DropshipPo\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Report extends Container
{
    protected function _construct()
    {
        $this->_blockGroup = 'Unirgy_DropshipPo';
        $this->_controller = 'adminhtml_report';
        $this->_headerText = __('Dropship Advanced PO Details Report');
        parent::_construct();
        $this->setId('udpo_reportgrid_container');
        $this->removeButton('add');
    }
}
