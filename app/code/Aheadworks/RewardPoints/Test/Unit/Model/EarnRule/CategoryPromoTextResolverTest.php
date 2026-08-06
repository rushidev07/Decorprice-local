<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\EarnRule;

use Aheadworks\RewardPoints\Model\Config;
use Aheadworks\RewardPoints\Model\EarnRule\CategoryPromoTextResolver;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Api\EarnRuleRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\RewardPoints\Api\Data\StorefrontLabelsInterface;

/**
 * Test for \Aheadworks\RewardPoints\Model\EarnRule\CategoryPromoTextResolver
 */
class CategoryPromoTextResolverTest extends TestCase
{
    /**
     * @var CategoryPromoTextResolver
     */
    private $categoryPromoTextResolver;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var EarnRuleRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $earnRuleRepositoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->configMock = $this->createMock(Config::class);
        $this->earnRuleRepositoryMock = $this->createMock(EarnRuleRepositoryInterface::class);

        $this->categoryPromoTextResolver = $objectManager->getObject(
            CategoryPromoTextResolver::class,
            [
                'config' => $this->configMock,
                'earnRuleRepository' => $this->earnRuleRepositoryMock,
            ]
        );
    }

    /**
     * Test getPromoText method
     *
     * @param int[] $appliedRuleIds
     * @param int|null $activeRuleId
     * @param int $storeId
     * @param EarnRuleInterface|\PHPUnit_Framework_MockObject_MockObject|null $rule
     * @param string $configCategoryPromoText
     * @param string $resultText
     * @dataProvider getPromoTextDataProvider
     */
    public function testGetPromoText(
        $appliedRuleIds,
        $activeRuleId,
        $storeId,
        $rule,
        $configCategoryPromoText,
        $resultText
    ) {
        if ($rule) {
            $this->earnRuleRepositoryMock->expects($this->any())
                ->method('get')
                ->with($activeRuleId)
                ->willReturn($rule);
        } else {
            $this->earnRuleRepositoryMock->expects($this->any())
                ->method('get')
                ->with($activeRuleId)
                ->willThrowException(new NoSuchEntityException(__('No such entity!')));
        }

        $this->configMock->expects($this->any())
            ->method('getCategoryProductPromoText')
            ->with($storeId)
            ->willReturn($configCategoryPromoText);

        $this->assertEquals(
            $resultText,
            $this->categoryPromoTextResolver->getPromoText($appliedRuleIds, $storeId)
        );
    }

    /**
     * @return array
     */
    public function getPromoTextDataProvider()
    {
        return [
            [
                'appliedRuleIds' => [10, 11],
                'activeRuleId' => 11,
                'storeId' => 1,
                'rule' => $this->getEarnRuleMock('Rule Text'),
                'configCategoryPromoText' => 'Default Promo Text',
                'resultText' => 'Default Promo Text'
            ],
            [
                'appliedRuleIds' => [10, 11, 12],
                'activeRuleId' => 12,
                'storeId' => 1,
                'rule' => $this->getEarnRuleMock('Rule Text'),
                'configCategoryPromoText' => 'Default Promo Text',
                'resultText' => 'Default Promo Text'
            ],
            [
                'appliedRuleIds' => [10],
                'activeRuleId' => 10,
                'storeId' => 1,
                'rule' => $this->getEarnRuleMock('Rule Text'),
                'configCategoryPromoText' => 'Default Promo Text',
                'resultText' => 'Rule Text'
            ],
            [
                'appliedRuleIds' => [10],
                'activeRuleId' => 10,
                'storeId' => 1,
                'rule' => $this->getEarnRuleMock(''),
                'configCategoryPromoText' => 'Default Promo Text',
                'resultText' => ''
            ],
            [
                'appliedRuleIds' => [10],
                'activeRuleId' => 10,
                'storeId' => 1,
                'rule' => null,
                'configCategoryPromoText' => 'Default Promo Text',
                'resultText' => ''
            ],
            [
                'appliedRuleIds' => [],
                'activeRuleId' => null,
                'storeId' => 1,
                'rule' => null,
                'configCategoryPromoText' => 'Default Promo Text',
                'resultText' => ''
            ],
        ];
    }

    /**
     * Get earn rule mock
     *
     * @param string $promoText
     * @return EarnRuleInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEarnRuleMock($promoText)
    {
        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $currentLabelsMock = $this->createMock(StorefrontLabelsInterface::class);
        $currentLabelsMock->expects($this->any())
            ->method('getCategoryPromoText')
            ->willReturn($promoText);
        $ruleMock->expects($this->any())
            ->method('getCurrentLabels')
            ->willReturn($currentLabelsMock);

        return $ruleMock;
    }
}
