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

/**
 * Contact form submit ajax controller
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Contact extends \Magento\Framework\App\Action\Action
{

    /**
     * Recipient email config path
     */
    const XML_PATH_EMAIL_RECIPIENT = 'contact/email/recipient_email';

    /**
     * Sender email config path
     */
    const XML_PATH_EMAIL_SENDER = 'contact/email/sender_email_identity';

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
     * @var \Magetrend\Eop\Model\Mail\Template\ContactTransportBuilder
     */
    public $transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    public $inlineTranslation;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $date;

    /**
     * @var \Magetrend\Eop\Model\CampaignFactory
     */
    public $campaignFactory;

    /**
     * Contact constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magetrend\Eop\Helper\Data $helper
     * @param \Magetrend\Eop\Model\CampaignFactory $campaignFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magetrend\Eop\Model\Mail\Template\ContactTransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magetrend\Eop\Helper\Data $helper,
        \Magetrend\Eop\Model\CampaignFactory $campaignFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magetrend\Eop\Model\Mail\Template\ContactTransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->helper = $helper;
        $this->campaignFactory = $campaignFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->coreRegistry = $registry;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->date = $date;
        parent::__construct($context);
    }

    /**
     * Validates the format of the email address
     *
     * @param string $email
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function validateEmailFormat($email)
    {
        if (!\Zend_Validate::is($email, 'EmailAddress')) {
            throw new \Magento\Framework\Exception\LocalizedException(__(
                $this->helper->translate('error_email_not_valid')
            ));
        }
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
        ];

        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('campaign_id')) {
            $subject = '';
            $senderName = '';
            $email = $this->getRequest()->getPost('email');
            $this->validateEmailFormat($email);
            $campaignId = $this->getRequest()->getPost('campaign_id');
            $postObject = $this->getRequest()->getParams();

            if (isset($postObject['name']) && !empty($postObject['name'])) {
                $senderName = $postObject['name'];
            }

            if (isset($postObject['firstname']) && !empty($postObject['firstname'])) {
                $senderName = $postObject['firstname'];
            }

            if (isset($postObject['subject']) && !empty($postObject['subject'])) {
                $subject = $postObject['subject'];
            }

            $postObject['date'] = $this->date->date();
            $postObject['ip'] = $this->helper->getUserIp();

            try {
                $popup = $this->_getPopup($campaignId);
                $postObject['request'] = $this->helper->formatFormRequest($popup, $postObject);
                if (empty($subject)) {
                    $subject = $popup->getSubject();
                }

                $transport = $this->transportBuilder
                    ->setTemplateIdentifier($popup->getEmailTemplate())
                    ->setTemplateOptions(
                        [
                            'area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                        ]
                    )
                    ->setTemplateVars($postObject)
                    ->setFrom([
                        'name' => $senderName,
                        'email' => $email,
                    ])
                    ->addTo($popup->getSendTo(), $popup->getSenderName())
                    ->setReplyTo($email)
                    ->setSubject($subject)
                    ->getTransport();

                $transport->sendMessage();
                $this->inlineTranslation->resume();
                $response['successMsg'] = __($this->helper->translate('contact_form_success_message'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->inlineTranslation->resume();
                $response['errorMsg'] = __('%1', $e->getMessage());
            } catch (\Exception $e) {
                $this->inlineTranslation->resume();
                $response['errorMsg'] = __($this->helper->translate('contact_form_error_general')).$e->getMessage();
            }
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }

    /**
     * It returns popup object
     *
     * @param $campaignId
     * @return mixed
     * @throws \Exception
     */
    protected function _getPopup($campaignId)
    {
        if (!is_numeric($campaignId)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Campaign id is not valid'));
        }
        $campaign = $this->campaignFactory->create()
            ->load($campaignId);
        if (!$campaign->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__(
                'Campaign is no longer available'
            ));
        }

        return $campaign->getPopup();
    }
}
