<?php
namespace Aheadworks\RewardPoints\Block\Adminhtml\Sales\Order\Create;

use Magento\Framework\View\Element\Template;
use Aheadworks\RewardPoints\Api\CustomerRewardPointsManagementInterface;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Backend\Model\Session\Quote;

/**
 * Class Payment
 *
 * @package Aheadworks\RewardPoints\Block\Adminhtml\Sales\Order\Create
 */
class Payment extends Template
{
    /**
     * @var Create
     */
    private $orderCreate;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var CustomerRewardPointsManagementInterface
     */
    private $customerRewardPointsService;

    /**
     * @var Quote
     */
    private $sessionQuote;

    /**
     * @param Context $context
     * @param Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param CustomerRewardPointsManagementInterface $customerRewardPointsService
     * @param Session\Quote $sessionQuote
     * @param array $data
     */
    public function __construct(
        Context $context,
        Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        CustomerRewardPointsManagementInterface $customerRewardPointsService,
        Quote $sessionQuote,
        array $data = []
    ) {
        $this->orderCreate = $orderCreate;
        $this->priceCurrency = $priceCurrency;
        $this->customerRewardPointsService = $customerRewardPointsService;
        $this->sessionQuote = $sessionQuote;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->orderCreate->getQuote();
    }

    /**
     * Show reward points or not
     *
     * @return bool
     */
    public function canShow()
    {
        return $this->getBalance() > 0;
    }

    /**
     * Retrieve customer balance
     *
     * @return float
     */
    public function getBalance()
    {
        if (!$this->getQuote() || !$this->getQuote()->getCustomerId()) {
            return 0.0;
        }
        return $this->customerRewardPointsService
            ->getCustomerRewardPointsBalanceBaseCurrency($this->getQuote()->getCustomerId());
    }

    /**
     * Format value as price
     *
     * @param float $value
     * @return string
     */
    public function formatPrice($value)
    {
        return $this->priceCurrency->convertAndFormat(
            $value,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->sessionQuote->getStore()
        );
    }

    /**
     * Check whether quote uses customer balance
     *
     * @return bool
     */
    public function isUseAwRewardPoints()
    {
        return $this->getQuote()->getAwUseRewardPoints();
    }
}
