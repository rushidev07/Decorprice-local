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

namespace Magetrend\Eop\Controller\Popup;

use Magento\Customer\Model\Url as CustomerUrl;

/**
 * Cupon code generator ajax controller
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Coupon extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    public $customerAccountManagement;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;

    /**
     * @var \Magetrend\Eop\Helper\Data
     */
    public $helper;

    /**
     * @var \Magetrend\Eop\Model\CampaignFactory
     */
    public $campaignFactory;

    /**
     * Coupon constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magetrend\Eop\Helper\Data $helper
     * @param \Magetrend\Eop\Model\CampaignFactory $campaignFactory
     * @param \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magetrend\Eop\Helper\Data $helper,
        \Magetrend\Eop\Model\CampaignFactory $campaignFactory,
        \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->helper = $helper;
        $this->campaignFactory = $campaignFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->coreRegistry = $registry;
        $this->customerAccountManagement = $customerAccountManagement;
        parent::__construct($context);
    }

    /**
     * New subscription action
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return String
     */
    public function execute()
    {
        $response = [
            'errorMsg' => '',
            'successMsg' => '',
            'couponCode' => ''
        ];

        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('id')) {
            $campaignId = $this->getRequest()->getPost('id');
            try {
                $campaign = $this->helper->getCampaign($campaignId);
                $popup = $campaign->getPopup();
                $code = $popup->getUniqueDiscountCode();
                $campaign->setYesNoCookie();

                if (!empty($code)) {
                    $response['couponCode'] = $code;
                    $response['successMsg'] = 1;
                }
            } catch (\Exception $e) {
                $response['errorMsg'] = $e->getMessage();
            }
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
