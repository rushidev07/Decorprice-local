<?php
namespace Aheadworks\RewardPoints\Model\EarnRule;

use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Api\EarnRuleRepositoryInterface;
use Aheadworks\RewardPoints\Model\Config;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class ProductPromoTextResolver
 * @package Aheadworks\RewardPoints\Model\EarnRule
 */
class ProductPromoTextResolver
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var EarnRuleRepositoryInterface
     */
    private $earnRuleRepository;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Config $config
     * @param EarnRuleRepositoryInterface $earnRuleRepository
     * @param Logger $logger
     */
    public function __construct(
        Config $config,
        EarnRuleRepositoryInterface $earnRuleRepository,
        Logger $logger
    ) {
        $this->config = $config;
        $this->earnRuleRepository = $earnRuleRepository;
        $this->logger = $logger;
    }

    /**
     * Get product promo text
     *
     * @param int[] $appliedRuleIds
     * @param int|null $storeId
     * @param bool $loggedIn
     * @return string
     */
    public function getPromoText($appliedRuleIds, $storeId = null, $loggedIn = true)
    {
        $promoText = '';
        $appliedRulesCount = count($appliedRuleIds);
        if ($appliedRulesCount == 1) {
            $ruleId = reset($appliedRuleIds);
            try {
                /** @var EarnRuleInterface $rule */
                $rule = $this->earnRuleRepository->get($ruleId, $storeId);
                $promoText = $rule->getCurrentLabels()->getProductPromoText();
            } catch (NoSuchEntityException $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }

        if (empty($promoText)) {
            $promoText = $loggedIn
                ? $this->config->getProductPromoTextForRegisteredCustomers($storeId)
                : $this->config->getProductPromoTextForNotLoggedInVisitors($storeId);
        }

        return $promoText;
    }
}
