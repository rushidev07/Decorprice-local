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

namespace  Magetrend\Eop\Controller\Adminhtml;

/**
 * Exit offer popup campaigns abstract controller
 *
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Campaign extends \Magento\Backend\App\Action
{
    /**
     * @var \Magetrend\Eop\Model\CampaignFactory
     */
    public $campaignFactory;

    /**
     * @var \Magetrend\Eop\Model\PopupFactory
     */
    public $popupFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    public $dateTime;

    /**
     * Campaign constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magetrend\Eop\Model\CampaignFactory $campaignFactory
     * @param \Magetrend\Eop\Model\PopupFactory $popupFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magetrend\Eop\Model\CampaignFactory $campaignFactory,
        \Magetrend\Eop\Model\PopupFactory $popupFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\Filter\DateTime $dateTime
    ) {
        $this->campaignFactory = $campaignFactory;
        $this->popupFactory = $popupFactory;
        $this->coreRegistry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->dateTime = $dateTime;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Exit Offer / Manage Campaigns'));
        $this->_view->renderLayout();
    }

    /**
     * Check if user has enough privileges
     *
     * @return bool
     */
    //@codingStandardsIgnoreLine
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magetrend_Eop::campaign');
    }
}
