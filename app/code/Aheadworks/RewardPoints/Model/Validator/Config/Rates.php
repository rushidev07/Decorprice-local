<?php
namespace Aheadworks\RewardPoints\Model\Validator\Config;

use Aheadworks\RewardPoints\Api\Data\EarnRateInterface;

/**
 * Class Rates
 *
 * @package Aheadworks\RewardPoints\Model\Validator\Config
 */
class Rates
{
    /**
     * Checks whether a config has a duplicate rates
     *
     * @param array $rates
     * @return bool
     */
    public function hasDuplicateValue($rates)
    {
        if (!is_array($rates)) {
            return false;
        }

        $clearRates = array_filter($rates, 'is_array');
        foreach ($clearRates as $key => $rate) {
            foreach ($clearRates as $comparedKey => $comparedRate) {
                if ($key === $comparedKey) {
                    continue;
                }
                if ($this->isDuplicateValue($rate, $comparedRate)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks whether rates are a duplicate
     *
     * @param array $rate
     * @param array $comparedRate
     * @return bool
     */
    private function isDuplicateValue($rate, $comparedRate)
    {
        if ($rate[EarnRateInterface::WEBSITE_ID] != $comparedRate[EarnRateInterface::WEBSITE_ID]) {
            return false;
        }

        if ($rate[EarnRateInterface::CUSTOMER_GROUP_ID] != $comparedRate[EarnRateInterface::CUSTOMER_GROUP_ID]) {
            return false;
        }

        if ($rate[EarnRateInterface::LIFETIME_SALES_AMOUNT]
            != $comparedRate[EarnRateInterface::LIFETIME_SALES_AMOUNT]
        ) {
            return false;
        }

        return true;
    }
}
