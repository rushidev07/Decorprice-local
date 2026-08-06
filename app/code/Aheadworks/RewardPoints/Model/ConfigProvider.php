<?php
namespace Aheadworks\RewardPoints\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;

/**
 * Class ConfigProvider
 *
 * @package Aheadworks\RewardPoints\Model
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'payment' => [
                'awRewardPoints' => [
                    'removeUrl' => $this->getRemoveUrl(),
                ],
            ]
        ];
    }

    /**
     * Retrieve URL to remove store credit balance
     *
     * @return string
     */
    protected function getRemoveUrl()
    {
        return $this->urlBuilder->getUrl('aw_rewardpoints/cart/remove');
    }
}
