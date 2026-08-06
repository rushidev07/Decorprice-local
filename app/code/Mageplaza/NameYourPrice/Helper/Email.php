<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  Mageplaza
 * @package   Mageplaza_NameYourPrice
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\NameYourPrice\Helper;

use Exception;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\NameYourPrice\Model\ResourceModel\RequestsFactory;

/**
 * Class Email
 * @package Mageplaza\NameYourPrice\Helper
 */
class Email extends Data
{
    const CONFIG_MODULE_PATH = 'mppricebargain';
    const EMAIL_CONFIGURATION = '/email';

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * Email constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param ProductFactory $productFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param RequestsFactory $requestsFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param TransportBuilder $transportBuilder
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        ProductFactory $productFactory,
        OrderRepositoryInterface $orderRepository,
        RequestsFactory $requestsFactory,
        PriceCurrencyInterface $priceCurrency,
        TransportBuilder $transportBuilder
    ) {
        $this->transportBuilder = $transportBuilder;

        parent::__construct(
            $context,
            $objectManager,
            $storeManager,
            $productFactory,
            $orderRepository,
            $requestsFactory,
            $priceCurrency
        );
    }

    /**
     * @param string $template
     * @param string $toEmail
     * @param array $templateParams
     * @param null $storeId
     *
     * @return $this
     * @throws NoSuchEntityException
     */
    public function sendEmail($template, $toEmail, $templateParams = [], $storeId = null)
    {
        if (!$this->isEnabledEmail($storeId)) {
            return $this;
        }

        $storeId = $storeId ?: $this->storeManager->getStore()->getId();

        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($template)
                ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $storeId])
                ->setTemplateVars($templateParams)
                ->setFrom($this->getSender($storeId))
                ->addTo($toEmail)
                ->getTransport();

            $transport->sendMessage();
        } catch (Exception $e) {
            $this->_logger->critical($e->getMessage());
        }

        return $this;
    }

    /**
     * ======================================= Email Configuration ==================================================
     *
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getConfigEmail($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . self::EMAIL_CONFIGURATION . $code, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function isEnabledEmail($storeId = null)
    {
        if ($this->isEnabled()) {
            return (bool)$this->getConfigEmail('enabled', $storeId);
        }

        return false;
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getSender($storeId = null)
    {
        return $this->getConfigEmail('sender', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getConfirmTemplate($storeId = null)
    {
        return $this->getConfigEmail('template_confirm', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getApproveTemplate($storeId = null)
    {
        return $this->getConfigEmail('template_approve', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getRejectTemplate($storeId = null)
    {
        return $this->getConfigEmail('template_reject', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array|bool
     */
    public function getToAdminEmail($storeId = null)
    {
        $config = $this->getConfigEmail('admin_email', $storeId);

        return $config ? explode(',', $config) : false;
    }
}
