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
 
namespace Unirgy\DropshipPo\Block\Adminhtml\Po\View;

use Magento\Backend\Block\Text\ListText;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Context;

class Comments extends ListText
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    public function __construct(Context $context, 
        Registry $frameworkRegistry, 
        array $data = [])
    {
        $this->_coreRegistry = $frameworkRegistry;

        parent::__construct($context, $data);
    }

    public function getPo()
    {
        return $this->_coreRegistry->registry('current_udpo');
    }

    public function getOrder()
    {
        return $this->getPo()->getOrder();
    }

    public function getSource()
    {
        return $this->getPo();
    }
}