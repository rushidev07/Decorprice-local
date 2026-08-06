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

use Magento\Customer\Model\Session;

/**
 * Newsletter subscription ajax controller
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Subscribe extends \Magento\Newsletter\Controller\Subscriber
{
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
     * @var \Magetrend\Eop\Model\Validator\Popup\Subscribe
     */
    public $validator;

    /**
     * Subscribe constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magetrend\Eop\Helper\Data $helper
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magetrend\Eop\Model\Validator\Popup\Subscribe $validator
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magetrend\Eop\Helper\Data $helper,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magetrend\Eop\Model\Validator\Popup\Subscribe $validator
    ) {
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->coreRegistry = $registry;
        $this->validator = $validator;
        parent::__construct(
            $context,
            $subscriberFactory,
            $customerSession,
            $storeManager,
            $customerUrl
        );
    }

    /**
     * New subscription action
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function execute()
    {
        $response = [
            'errorMsg' => '',
            'successMsg' => '',
            'couponCode' => ''
        ];

        if ($this->getRequest()->isPost()
            && $this->getRequest()->getPost('email')
            && $this->getRequest()->getPost('campaign_id')
        ) {
            $email = (string)$this->getRequest()->getPost('email');
            $campaignId = $this->getRequest()->getPost('campaign_id');

            try {
                $this->validator->validateEmailFormat($email);
                $this->validator->validateGuestSubscription();
                $this->validator->validateEmailAvailable($email);
                $popup = $this->helper->getPopup($campaignId);
                $status = $this->_subscriberFactory->create()->subscribe($email);

                if ($popup->getShowInPopup()) {
                    $code = $this->coreRegistry->registry('eop_coupon_code');
                    if (!empty($code)) {
                        $response['couponCode'] = $code;
                    }
                }

                if ($status == \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE) {
                    $response['successMsg'] = __($this->helper->translate('success_message_need_to_confirm'));
                } else {
                    $response['successMsg'] = __($this->helper->translate('success_message'));
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $response['errorMsg'] = __('%1', $e->getMessage());
            } catch (\Exception $e) {
                $response['errorMsg'] = __($this->helper->translate('error_with_subscription'));
            }
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
