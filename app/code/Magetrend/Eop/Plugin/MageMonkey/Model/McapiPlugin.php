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

namespace Magetrend\Eop\Plugin\MageMonkey\Model;

use Magento\Newsletter\Model\Subscriber;

/**
 * Magemonkey api plugin
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class McapiPlugin
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * @var \Magetrend\Eop\Helper\Data
     */
    public $helper;

    public $unserialize;

    /**
     * McapiPlugin constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface
     * @param \Magetrend\Eop\Helper\Data $helper
     * @param \Magento\Framework\Unserialize\Unserialize $unserialize
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Magetrend\Eop\Helper\Data $helper,
        \Magento\Framework\Unserialize\Unserialize $unserialize
    ) {
        $this->registry = $registry;
        $this->scopeConfig = $scopeConfigInterface;
        $this->helper = $helper;
        $this->unserialize = $unserialize;
    }

    /**
     * @param $api
     * @param $listId
     * @param $memberData
     * @return array|void
     * @codingStandardsIgnoreStart
     */
    public function beforeListCreateMember($api, $listId, $memberData)
    {
        if (!$this->helper->isActive()) {
            return;
        }

        $additionalData = $this->registry->registry('eop_additional_data');
        $couponCode = $this->registry->registry('eop_coupon_code');
        if (!empty($couponCode)) {
            $fieldName = \Magetrend\Eop\Model\Popup::DISCOUNT_CODE_FIELD;
            $additionalData[$fieldName] = $couponCode;
        }

        //@codingStandardsIgnoreStart
        $mergeVars = $this->unserialize->unserialize($this->scopeConfig->getValue(
            \Ebizmarts\MageMonkey\Helper\Data::XML_PATH_MAPPING,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            null
        ));
        //@codingStandardsIgnoreEnd

        $memberData = json_decode($memberData, true);
        if (!empty($mergeVars)) {
            foreach ($mergeVars as $mergeVar) {
                if (isset($additionalData[$mergeVar['magento']])
                    && !empty($additionalData[$mergeVar['magento']])
                ) {
                    $memberData['merge_fields'][$mergeVar['mailchimp']] = $additionalData[$mergeVar['magento']];
                }
            }
        }
        return [$listId, json_encode($memberData)];
    }
}
