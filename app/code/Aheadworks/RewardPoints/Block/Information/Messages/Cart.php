<?php
namespace Aheadworks\RewardPoints\Block\Information\Messages;

use Aheadworks\RewardPoints\Model\Calculator\ResultInterface;

/**
 * Class Cart
 *
 * @package Aheadworks\RewardPoints\Block\Information\Messages
 */
class Cart extends AbstractMessages
{
    /**
     * {@inheritdoc}
     */
    public function canShow()
    {
        return $this->getEarnPoints() > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        $earnPoints = $this->getEarnPoints();
        $message = __(
            'Checkout now to earn <strong>%1 points%2</strong> for your order.',
            $earnPoints,
            $this->getEarnMoneyByPoints($earnPoints),
            $this->getFrontendExplainerPageLink()
        );

        if (!$this->isCustomerLoggedIn()) {
            $message .= ' ' . __(
                'This amount can vary after logging in. <a href="%1">Learn more</a>.',
                $this->getFrontendExplainerPageLink()
            );
        }

        return $message;
    }

    /**
     * Retrieve how much points will be earned
     *
     * @return int
     */
    public function getEarnPoints()
    {
        /** @var ResultInterface $calculationResult */
        $calculationResult = $this->earningCalculator->calculationByQuote(
            $this->checkoutSession->getQuote(),
            $this->getCustomerId()
        );
        return $calculationResult->getPoints();
    }
}
