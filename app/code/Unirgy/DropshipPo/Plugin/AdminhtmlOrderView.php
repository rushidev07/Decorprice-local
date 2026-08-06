<?php
namespace Unirgy\DropshipPo\Plugin;

use Magento\Framework\Registry;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\DropshipPo\Helper\Data as PoHelperData;

class AdminhtmlOrderView
{
    /**
     * @var HelperData
     */
    protected $_hlp;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var PoHelperData
     */
    protected $_poHlp;

    public function __construct(
        HelperData $helperData,
        Registry $frameworkRegistry,
        PoHelperData $poHelperData,
        \Magento\Framework\AuthorizationInterface $authorization
    )
    {
        $this->_hlp = $helperData;
        $this->_coreRegistry = $frameworkRegistry;
        $this->_poHlp = $poHelperData;
        $this->_authorization = $authorization;
    }
    public function beforeSetLayout(\Magento\Sales\Block\Adminhtml\Order\View $subject, \Magento\Framework\View\LayoutInterface $layout)
    {
        if ($this->_coreRegistry->registry('sales_order')
            && $this->_hlp->isUdropshipOrder($this->_coreRegistry->registry('sales_order'))
            && $this->_authorization->isAllowed('Unirgy_DropshipPo::action_udpo')
            && $this->_poHlp->canCreatePo($this->_coreRegistry->registry('sales_order')->setSkipLockedCheckFlag(true))
        ) {
            $subject->addButton('create_upo', [
                'label'   => __('Create PO'),
                'class'   => 'create_upo',
                'onclick' => 'setLocation(\'' . $subject->getUrl('udpo/order_po/start') . '\')'
            ]);
            return null;
        }
    }
}
