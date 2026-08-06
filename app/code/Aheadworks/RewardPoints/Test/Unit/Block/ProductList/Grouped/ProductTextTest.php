<?php
namespace Aheadworks\RewardPoints\Test\Unit\Block\ProductList\Grouped;

use Aheadworks\RewardPoints\Block\ProductList\Grouped\ProductText;
use Aheadworks\RewardPoints\Model\Calculator\ResultInterface;
use Aheadworks\RewardPoints\Model\Config;
use Aheadworks\RewardPoints\Model\EarnRule\Applier;
use Aheadworks\RewardPoints\Model\EarnRule\ProductPromoTextResolver;
use Aheadworks\RewardPoints\Model\Calculator\Earning as EarningCalculator;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Model\Product;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\App\Request\Http;

/**
 * Test for \Aheadworks\RewardPoints\Block\ProductList\Grouped\ProductText
 */
class ProductTextTest extends TestCase
{
    /**
     * @var ProductText
     */
    private $block;

    /**
     * @var  Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var EarningCalculator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $earningCalculatorMock;

    /**
     * @var ProductPromoTextResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productPromoTextResolverMock;

    /**
     * @var Applier|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ruleApplierMock;

    /**
     * @var CustomerSession|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var HttpContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpContextMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productMock;

    /**
     * @var Http|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->createMock(Http::class);
        $this->contextMock = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock
            ]
        );
        $this->configMock = $this->createMock(Config::class);
        $this->earningCalculatorMock = $this->createMock(EarningCalculator::class);
        $this->productPromoTextResolverMock = $this->createMock(ProductPromoTextResolver::class);
        $this->ruleApplierMock = $this->createMock(Applier::class);
        $this->customerSessionMock = $this->createMock(CustomerSession::class);
        $this->httpContextMock = $this->createMock(HttpContext::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->productMock = $this->createMock(Product::class);

        $this->block = $objectManager->getObject(
            ProductText::class,
            [
                'context' => $this->contextMock,
                'config' => $this->configMock,
                'earningCalculator' => $this->earningCalculatorMock,
                'productPromoTextResolver' => $this->productPromoTextResolverMock,
                'ruleApplier' => $this->ruleApplierMock,
                'customerSession' => $this->customerSessionMock,
                'httpContext' => $this->httpContextMock,
                'storeManager' => $this->storeManagerMock,
            ]
        );
    }

    /**
     * Test isDisplayBlock method
     *
     * @param bool $isLoggedIn
     * @param string $text
     * @param int $maxPoints
     * @param int|null $customerId
     * @param bool $isAjax
     * @param bool $result
     * @dataProvider isDisplayBlockDataProvider
     */
    public function testIsDisplayBlock(
        $isLoggedIn,
        $text,
        $maxPoints,
        $customerId,
        $isAjax,
        $result
    ) {

        $productId = 125;
        $customerGroupId = 3;
        $websiteId = 2;
        $storeId = 3;
        $appliedRuleIds = [11, 12];

        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $this->block->setProduct($this->productMock);
        $this->requestMock->expects($this->any())
            ->method('isAjax')
            ->willReturn($isAjax);

        $websiteMock = $this->createMock(WebsiteInterface::class);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($websiteMock);

        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        if ($isLoggedIn) {
            $this->httpContextMock->expects($this->any())
                ->method('getValue')
                ->willReturnMap(
                    [
                        [
                            CustomerContext::CONTEXT_AUTH,
                            $isLoggedIn
                        ],
                        [
                            CustomerContext::CONTEXT_GROUP,
                            $customerGroupId
                        ]
                    ]
                );
        } else {
            $this->httpContextMock->expects($this->any())
                ->method('getValue')
                ->with(CustomerContext::CONTEXT_AUTH)
                ->willReturn($isLoggedIn);

            $this->configMock->expects($this->atLeastOnce())
                ->method('getDefaultCustomerGroupIdForGuest')
                ->willReturn($customerGroupId);
        }

        $calculationResultMock = $this->getCalculationResultMock($maxPoints, $appliedRuleIds);

        $this->ruleApplierMock->expects($this->once())
            ->method('getAppliedRuleIds')
            ->with($productId, $customerGroupId, $websiteId)
            ->willReturn($appliedRuleIds);

        $this->productPromoTextResolverMock->expects($this->once())
            ->method('getPromoText')
            ->with($appliedRuleIds, $storeId, $isLoggedIn)
            ->willReturn($text);

        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->earningCalculatorMock->expects($this->once())
            ->method('calculationByProduct')
            ->with($this->productMock, true, $customerId)
            ->willReturn($calculationResultMock);

        $this->assertEquals($result, $this->block->isDisplayBlock());
    }

    /**
     * @return array
     */
    public function isDisplayBlockDataProvider()
    {
        return [
            [
                'loggedIn' => true,
                'text' => 'Sample %X points',
                'maxPoints' => 10,
                'customerId' => 10,
                'isAjax' => true,
                'result' => true
            ],
            [
                'loggedIn' => false,
                'text' => 'Sample %X points',
                'maxPoints' => 10,
                'customerId' => 10,
                'isAjax' => true,
                'result' => true
            ],
            [
                'loggedIn' => true,
                'text' => 'Sample %X points',
                'maxPoints' => 125,
                'customerId' => 10,
                'isAjax' => true,
                'result' => true
            ],
            [
                'loggedIn' => false,
                'text' => 'Sample %X points',
                'maxPoints' => 125,
                'customerId' => 10,
                'isAjax' => true,
                'result' => true
            ],
            [
                'loggedIn' => true,
                'text' => '',
                'maxPoints' => 125,
                'customerId' => 10,
                'isAjax' => true,
                'result' => false
            ],
            [
                'loggedIn' => false,
                'text' => '',
                'maxPoints' => 125,
                'customerId' => null,
                'isAjax' => true,
                'result' => false
            ],
            [
                'loggedIn' => true,
                'text' => 'Sample %X points',
                'maxPoints' => 10,
                'customerId' => null,
                'isAjax' => true,
                'result' => true
            ],
            [
                'loggedIn' => false,
                'text' => 'Sample %X points',
                'maxPoints' => 10,
                'customerId' => null,
                'isAjax' => true,
                'result' => true
            ],
            [
                'loggedIn' => true,
                'text' => 'Sample %X points',
                'maxPoints' => 125,
                'customerId' => null,
                'isAjax' => true,
                'result' => true
            ],
            [
                'loggedIn' => false,
                'text' => 'Sample %X points',
                'maxPoints' => 125,
                'customerId' => null,
                'isAjax' => true,
                'result' => true
            ],
            [
                'loggedIn' => true,
                'text' => '',
                'maxPoints' => 125,
                'customerId' => null,
                'isAjax' => true,
                'result' => false
            ],
            [
                'loggedIn' => true,
                'text' => 'Sample %X points',
                'maxPoints' => 0,
                'customerId' => 10,
                'isAjax' => true,
                'result' => false
            ],
            [
                'loggedIn' => false,
                'text' => 'Sample %X points',
                'maxPoints' => 0,
                'customerId' => 10,
                'isAjax' => true,
                'result' => false
            ],
            [
                'loggedIn' => true,
                'text' => 'Sample %X points',
                'maxPoints' => 0,
                'customerId' => 10,
                'isAjax' => true,
                'result' => false
            ],
            [
                'loggedIn' => false,
                'text' => 'Sample %X points',
                'maxPoints' => 0,
                'customerId' => 10,
                'isAjax' => true,
                'result' => false
            ],
            [
                'loggedIn' => true,
                'text' => '',
                'maxPoints' => 0,
                'customerId' => 10,
                'isAjax' => true,
                'result' => false
            ],
            [
                'loggedIn' => false,
                'text' => '',
                'maxPoints' => 0,
                'customerId' => null,
                'isAjax' => true,
                'result' => false
            ],
            [
                'loggedIn' => true,
                'text' => 'Sample %X points',
                'maxPoints' => 10,
                'customerId' => 10,
                'isAjax' => false,
                'result' => false
            ],
        ];
    }

    /**
     * Test getPromoText method
     *
     * @param bool $isLoggedIn
     * @param string $text
     * @param int $maxPoints
     * @param string $result
     * @dataProvider getPromoTextDataProvider
     */
    public function testGetPromoText($isLoggedIn, $text, $maxPoints, $result)
    {
        $productId = 125;
        $customerGroupId = 3;
        $websiteId = 2;
        $storeId = 3;
        $appliedRuleIds = [11, 12];

        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $this->block->setProduct($this->productMock);

        $websiteMock = $this->createMock(WebsiteInterface::class);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($websiteMock);

        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        if ($isLoggedIn) {
            $this->httpContextMock->expects($this->any())
                ->method('getValue')
                ->willReturnMap(
                    [
                        [
                            CustomerContext::CONTEXT_AUTH,
                            $isLoggedIn
                        ],
                        [
                            CustomerContext::CONTEXT_GROUP,
                            $customerGroupId
                        ]
                    ]
                );
        } else {
            $this->httpContextMock->expects($this->any())
                ->method('getValue')
                ->with(CustomerContext::CONTEXT_AUTH)
                ->willReturn($isLoggedIn);

            $this->configMock->expects($this->once())
                ->method('getDefaultCustomerGroupIdForGuest')
                ->willReturn($customerGroupId);
        }

        $calculationResultMock = $this->getCalculationResultMock($maxPoints, $appliedRuleIds);
        $this->setProperty('calculationResult', $calculationResultMock);

        $this->ruleApplierMock->expects($this->once())
            ->method('getAppliedRuleIds')
            ->with($productId, $customerGroupId, $websiteId)
            ->willReturn($appliedRuleIds);

        $this->productPromoTextResolverMock->expects($this->once())
            ->method('getPromoText')
            ->with($appliedRuleIds, $storeId, $isLoggedIn)
            ->willReturn($text);

        $this->assertEquals($result, $this->block->getPromoText());
    }

    /**
     * @return array
     */
    public function getPromoTextDataProvider()
    {
        return [
            [
                'loggedIn' => true,
                'text' => 'Sample %X points',
                'maxPoints' => 10,
                'result' => 'Sample 10 points'
            ],
            [
                'loggedIn' => false,
                'text' => 'Sample %X points',
                'maxPoints' => 10,
                'result' => 'Sample 10 points'
            ],
            [
                'loggedIn' => true,
                'text' => 'Sample %X points',
                'maxPoints' => 125,
                'result' => 'Sample 125 points'
            ],
            [
                'loggedIn' => false,
                'text' => 'Sample %X points',
                'maxPoints' => 125,
                'result' => 'Sample 125 points'
            ],
            [
                'loggedIn' => true,
                'text' => '',
                'maxPoints' => 125,
                'result' => ''
            ],
            [
                'loggedIn' => false,
                'text' => '',
                'maxPoints' => 125,
                'result' => ''
            ],
        ];
    }

    /**
     * Test getPromoText method if no website
     */
    public function testGetPromoTextNoWebsite()
    {
        $promoText = '';
        $appliedRuleIds = [];
        $maxPoints = 100;

        $this->block->setProduct($this->productMock);

        $calculationResultMock = $this->getCalculationResultMock($maxPoints, $appliedRuleIds);
        $this->setProperty('calculationResult', $calculationResultMock);

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willThrowException(new LocalizedException(__('Error!')));

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willThrowException(new LocalizedException(__('Error!')));

        $this->ruleApplierMock->expects($this->never())
            ->method('getAppliedRuleIds');

        $this->productPromoTextResolverMock->expects($this->once())
            ->method('getPromoText')
            ->with($appliedRuleIds)
            ->willReturn($promoText);

        $this->assertEquals($promoText, $this->block->getPromoText());
    }

    /**
     * Test getPromoText method if no product
     */
    public function testGetPromoTextNoProduct()
    {
        $promoText = '';
        $websiteId = 2;
        $storeId = 3;
        $appliedRuleIds = [];

        $websiteMock = $this->createMock(WebsiteInterface::class);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($websiteMock);

        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->ruleApplierMock->expects($this->never())
            ->method('getAppliedRuleIds');

        $this->productPromoTextResolverMock->expects($this->once())
            ->method('getPromoText')
            ->with($appliedRuleIds)
            ->willReturn($promoText);

        $this->assertEquals($promoText, $this->block->getPromoText());
    }

    /**
     * Test getMaxPossibleEarningPoints method
     *
     * @param int|null $customerId
     * @param int $points
     * @dataProvider getMaxPossibleEarningPointsDataProvider
     */
    public function testGetMaxPossibleEarningPoints($customerId, $points)
    {
        $this->block->setProduct($this->productMock);

        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $resultMock = $this->createMock(ResultInterface::class);
        $resultMock->expects($this->once())
            ->method('getPoints')
            ->willReturn($points);

        $this->earningCalculatorMock->expects($this->once())
            ->method('calculationByProduct')
            ->with($this->productMock, true, $customerId)
            ->willReturn($resultMock);

        $this->assertEquals($points, $this->block->getMaxPossibleEarningPoints());
    }

    /**
     * @return array
     */
    public function getMaxPossibleEarningPointsDataProvider()
    {
        return [
            [
                'customerId' => 10,
                'points' => 125
            ],
            [
                'customerId' => 10,
                'points' => 0
            ],
            [
                'customerId' => null,
                'points' => 123
            ],
            [
                'customerId' => null,
                'points' => 0
            ]
        ];
    }

    /**
     * Test getMaxPossibleEarningPoints method if no product specified
     */
    public function testGetMaxPossibleEarningPointsNoProduct()
    {
        $this->earningCalculatorMock->expects($this->never())
            ->method('calculationByProduct');

        $this->assertEquals(0, $this->block->getMaxPossibleEarningPoints());
    }

    /**
     * Test getAppliedRuleIds method
     *
     * @param bool $loggedIn
     * @dataProvider getAppliedRuleIdsDataProvider
     */
    public function testGetAppliedRuleIds($loggedIn)
    {
        $productId = 125;
        $customerGroupId = 3;
        $websiteId = 2;
        $appliedRuleIds = [11, 12];

        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $this->block->setProduct($this->productMock);

        $websiteMock = $this->createMock(WebsiteInterface::class);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($websiteMock);

        if ($loggedIn) {
            $this->httpContextMock->expects($this->exactly(2))
                ->method('getValue')
                ->withConsecutive([CustomerContext::CONTEXT_AUTH], [CustomerContext::CONTEXT_GROUP])
                ->willReturnOnConsecutiveCalls($loggedIn, $customerGroupId);
        } else {
            $this->httpContextMock->expects($this->once())
                ->method('getValue')
                ->with(CustomerContext::CONTEXT_AUTH)
                ->willReturn($loggedIn);

            $this->configMock->expects($this->once())
                ->method('getDefaultCustomerGroupIdForGuest')
                ->willReturn($customerGroupId);
        }

        $this->ruleApplierMock->expects($this->once())
            ->method('getAppliedRuleIds')
            ->with($productId, $customerGroupId, $websiteId)
            ->willReturn($appliedRuleIds);

        $this->assertEquals($appliedRuleIds, $this->block->getAppliedRuleIds());
    }

    /**
     * @return array
     */
    public function getAppliedRuleIdsDataProvider()
    {
        return [
            ['loggedIn' => true],
            ['loggedIn' => false],
        ];
    }

    /**
     * Test getAppliedRuleIds method if no website
     */
    public function testGetAppliedRuleIdsNoWebsite()
    {
        $appliedRuleIds = [];

        $this->block->setProduct($this->productMock);

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willThrowException(new LocalizedException(__('Error!')));

        $this->ruleApplierMock->expects($this->never())
            ->method('getAppliedRuleIds');

        $this->assertEquals($appliedRuleIds, $this->block->getAppliedRuleIds());
    }

    /**
     * Test getAppliedRuleIds method if no product
     */
    public function testGetAppliedRuleIdsNoProduct()
    {
        $websiteId = 2;
        $appliedRuleIds = [];

        $websiteMock = $this->createMock(WebsiteInterface::class);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($websiteMock);

        $this->ruleApplierMock->expects($this->never())
            ->method('getAppliedRuleIds');

        $this->assertEquals($appliedRuleIds, $this->block->getAppliedRuleIds());
    }

    /**
     * Set property
     *
     * @param string $propertyName
     * @param mixed $value
     * @return mixed
     * @throws \ReflectionException
     */
    private function setProperty($propertyName, $value)
    {
        $class = new \ReflectionClass($this->block);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($this->block, $value);

        return $this;
    }

    /**
     * Get calculation result mock
     *
     * @param int $maxPoints
     * @param int[] $appliedRuleIds
     * @return ResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getCalculationResultMock($maxPoints, $appliedRuleIds)
    {
        $calculationResultMock = $this->createMock(ResultInterface::class);
        $calculationResultMock->expects($this->any())
            ->method('getPoints')
            ->willReturn($maxPoints);
        $calculationResultMock->expects($this->any())
            ->method('getAppliedRuleIds')
            ->willReturn($appliedRuleIds);

        return $calculationResultMock;
    }
}
