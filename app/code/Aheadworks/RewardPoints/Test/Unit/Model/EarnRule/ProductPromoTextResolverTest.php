<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\EarnRule;

use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Api\EarnRuleRepositoryInterface;
use Aheadworks\RewardPoints\Model\Config;
use Aheadworks\RewardPoints\Model\EarnRule\ProductPromoTextResolver;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\RewardPoints\Api\Data\StorefrontLabelsInterface;

/**
 * Test for \Aheadworks\RewardPoints\Model\EarnRule\ProductPromoTextResolver
 */
class ProductPromoTextResolverTest extends TestCase
{
    /**
     * @var ProductPromoTextResolver
     */
    private $productPromoTextResolver;

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

        $this->productPromoTextResolver = $objectManager->getObject(
            ProductPromoTextResolver::class,
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
     * @param int $storeId
     * @param bool $loggedIn
     * @param int|null $activeRuleId
     * @param EarnRuleInterface|\PHPUnit_Framework_MockObject_MockObject|null $rule
     * @param string $customerPromoText
     * @param string $guestPromoText
     * @param string $resultText
     * @dataProvider getPromoTextDataProvider
     */
    public function testGetPromoText(
        $appliedRuleIds,
        $storeId,
        $loggedIn,
        $activeRuleId,
        $rule,
        $customerPromoText,
        $guestPromoText,
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
            ->method('getProductPromoTextForRegisteredCustomers')
            ->with($storeId)
            ->willReturn($customerPromoText);
        $this->configMock->expects($this->any())
            ->method('getProductPromoTextForNotLoggedInVisitors')
            ->with($storeId)
            ->willReturn($guestPromoText);

        $this->assertEquals(
            $resultText,
            $this->productPromoTextResolver->getPromoText($appliedRuleIds, $storeId, $loggedIn)
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
                'storeId' => 2,
                'loggedIn' => true,
                'activeRuleId' => 11,
                'rule' => $this->getEarnRuleMock('Rule Text'),
                'customerPromoText' => 'Customer Text',
                'guestPromoText' => 'Guest Text',
                'resultText' => 'Customer Text'
            ],
            [
                'appliedRuleIds' => [10, 11],
                'storeId' => 2,
                'loggedIn' => false,
                'activeRuleId' => 11,
                'rule' => $this->getEarnRuleMock('Rule Text'),
                'customerPromoText' => 'Customer Text',
                'guestPromoText' => 'Guest Text',
                'resultText' => 'Guest Text'
            ],
            [
                'appliedRuleIds' => [10],
                'storeId' => 2,
                'loggedIn' => true,
                'activeRuleId' => 10,
                'rule' => $this->getEarnRuleMock('Rule Text'),
                'customerPromoText' => 'Customer Text',
                'guestPromoText' => 'Guest Text',
                'resultText' => 'Rule Text'
            ],
            [
                'appliedRuleIds' => [10],
                'storeId' => 2,
                'loggedIn' => false,
                'activeRuleId' => 10,
                'rule' => $this->getEarnRuleMock('Rule Text'),
                'customerPromoText' => 'Customer Text',
                'guestPromoText' => 'Guest Text',
                'resultText' => 'Rule Text'
            ],
            [
                'appliedRuleIds' => [10],
                'storeId' => 2,
                'loggedIn' => true,
                'activeRuleId' => 10,
                'rule' => $this->getEarnRuleMock(''),
                'customerPromoText' => 'Customer Text',
                'guestPromoText' => 'Guest Text',
                'resultText' => 'Customer Text'
            ],
            [
                'appliedRuleIds' => [10],
                'storeId' => 2,
                'loggedIn' => false,
                'activeRuleId' => 10,
                'rule' => $this->getEarnRuleMock(''),
                'customerPromoText' => 'Customer Text',
                'guestPromoText' => 'Guest Text',
                'resultText' => 'Guest Text'
            ],
            [
                'appliedRuleIds' => [10],
                'storeId' => 2,
                'loggedIn' => true,
                'activeRuleId' => 10,
                'rule' => null,
                'customerPromoText' => 'Customer Text',
                'guestPromoText' => 'Guest Text',
                'resultText' => 'Customer Text'
            ],
            [
                'appliedRuleIds' => [10],
                'storeId' => 2,
                'loggedIn' => false,
                'activeRuleId' => 10,
                'rule' => null,
                'customerPromoText' => 'Customer Text',
                'guestPromoText' => 'Guest Text',
                'resultText' => 'Guest Text'
            ],
            [
                'appliedRuleIds' => [],
                'storeId' => 2,
                'loggedIn' => true,
                'activeRuleId' => null,
                'rule' => null,
                'customerPromoText' => 'Customer Text',
                'guestPromoText' => 'Guest Text',
                'resultText' => 'Customer Text'
            ],
            [
                'appliedRuleIds' => [],
                'storeId' => 2,
                'loggedIn' => false,
                'activeRuleId' => null,
                'rule' => null,
                'customerPromoText' => 'Customer Text',
                'guestPromoText' => 'Guest Text',
                'resultText' => 'Guest Text'
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
            ->method('getProductPromoText')
            ->willReturn($promoText);
        $ruleMock->expects($this->any())
            ->method('getCurrentLabels')
            ->willReturn($currentLabelsMock);

        return $ruleMock;
    }
}
