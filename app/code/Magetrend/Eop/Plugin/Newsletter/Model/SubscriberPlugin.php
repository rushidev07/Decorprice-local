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

namespace Magetrend\Eop\Plugin\Newsletter\Model;

/**
 * Newsletter subscriber plugin
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class SubscriberPlugin
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    public $subscriberModel;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    public $productMetadata;

    /**
     * SubscriberPlugin constructor.
     * @param \Magetrend\Eop\Model\Newsletter\Subscriber $subscriber
     */
    public function __construct(
        \Magetrend\Eop\Model\Newsletter\Subscriber $subscriber,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->subscriberModel = $subscriber;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Assign additional fields data and coupon code for subscriber object
     * @param $subscriber
     * @param $email
     * @return array
     */
    public function beforeSubscribe($subscriber, $email)
    {
        if (version_compare($this->productMetadata->getVersion(), '2.4.0', '>=')) {
            return [$email];
        }

        $this->subscriberModel->beforeSubscribe($subscriber);
        return [$email];
    }

    /**
     * Assign additional fields data and coupon code for subscriber object then subscriber is customer
     * @param $subscriber
     * @param $customerId
     * @return array
     */
    public function beforeSubscribeCustomerById($subscriber, $customerId)
    {
        if (version_compare($this->productMetadata->getVersion(), '2.4.0', '>=')) {
            return [$customerId];
        }

        $this->subscriberModel->beforeSubscribeCustomerById($subscriber);
        return [$customerId];
    }

    /**
     * @param $subscriber
     */
    public function beforeBeforeSave($subscriber)
    {
        if (!$this->subscriberModel->subscriberDataRegistry) {
            return;
        }
        $additionalData = $this->subscriberModel->subscriberDataRegistry->getData();
        if (!empty($additionalData)) {
            foreach ($additionalData as $key => $value) {
                if (empty($value)) {
                    continue;
                }

                $subscriber->setData($key, $value);
            }
        }
    }
}
