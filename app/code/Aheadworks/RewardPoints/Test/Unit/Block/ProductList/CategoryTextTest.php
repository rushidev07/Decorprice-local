<?php
namespace Aheadworks\RewardPoints\Test\Unit\Block\ProductList;

use Aheadworks\RewardPoints\Block\ProductList\CategoryText;
use Aheadworks\RewardPoints\Model\Config;
use Aheadworks\RewardPoints\Model\EarnRule\Applier;
use Aheadworks\RewardPoints\Model\EarnRule\CategoryPromoTextResolver;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Catalog\Model\Product;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning as EarningCalculator;
use Aheadworks\RewardPoints\Model\Calculator\ResultInterface;

/**
 * Test for \Aheadworks\RewardPoints\Block\ProductList\CategoryText
 */
class CategoryTextTest extends TestCase
{
    /**
     * @var CategoryText
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
     * @var HttpContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpContextMock;

    /**
     * @var CategoryPromoTextResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryPromoTextResolverMock;

    /**
     * @var Applier|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ruleApplierMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productMock;

    /**
     * @var EarningCalculator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $earningCalculatorMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->contextMock = $objectManager->getObject(Context::class, []);

        $this->configMock = $this->createMock(Config::class);
        $this->httpContextMock = $this->createMock(HttpContext::class);
        $this->categoryPromoTextResolverMock = $this->createMock(CategoryPromoTextResolver::class);
        $this->ruleApplierMock = $this->createMock(Applier::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->earningCalculatorMock = $this->createMock(EarningCalculator::class);
        $this->productMock = $this->createMock(Product::class);

        $this->block = $objectManager->getObject(
            CategoryText::class,
            [
                'context' => $this->contextMock,
                'config' => $this->configMock,
                'httpContext' => $this->httpContextMock,
                'categoryPromoTextResolver' => $this->categoryPromoTextResolverMock,
                'ruleApplier' => $this->ruleApplierMock,
                'storeManager' => $this->storeManagerMock,
                'earningCalculator' => $this->earningCalculatorMock,
            ]
        );
    }

    /**
     * Test isDisplayBlock method
     *
     * @param bool $loggedIn
     * @param int $customerGroupId
     * @param int $points
     * @param bool $result
     * @dataProvider isDisplayBlockDataProvider
     */
    public function testIsDisplayBlockText(
        $loggedIn,
        $customerGroupId,
        $points,
        $result
    ) {
        $productId = 125;
        $websiteId = 2;
        $storeId = 3;
        $appliedRuleIds = [11, 12];
        $promoText = 'Sample text';

        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $this->block->setProduct($this->productMock);

        $websiteMock = $this->createMock(WebsiteInterface::class);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);
        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($websiteMock);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        if ($loggedIn) {
            $this->httpContextMock->expects($this->exactly(4))
                ->method('getValue')
                ->withConsecutive(
                    [CustomerContext::CONTEXT_AUTH],
                    [CustomerContext::CONTEXT_GROUP],
                    [CustomerContext::CONTEXT_AUTH],
                    [CustomerContext::CONTEXT_GROUP]
                )->willReturnOnConsecutiveCalls(
                    $loggedIn,
                    $customerGroupId,
                    $loggedIn,
                    $customerGroupId
                );
        } else {
            $this->httpContextMock->expects($this->exactly(2))
                ->method('getValue')
                ->with(CustomerContext::CONTEXT_AUTH)
                ->willReturn($loggedIn);

            $this->configMock->expects($this->any())
                ->method('getDefaultCustomerGroupIdForGuest')
                ->willReturn($customerGroupId);
        }

        $this->ruleApplierMock->expects($this->once())
            ->method('getAppliedRuleIds')
            ->with($productId, $customerGroupId, $websiteId)
            ->willReturn($appliedRuleIds);

        $this->categoryPromoTextResolverMock->expects($this->once())
            ->method('getPromoText')
            ->with($appliedRuleIds, $storeId)
            ->willReturn($promoText);

        $calculationResultMock = $this->getCalculationResultMock($points);

        $this->earningCalculatorMock->expects($this->once())
            ->method('calculationByProduct')
            ->with($this->productMock, true, null, null, $customerGroupId)
            ->willReturn($calculationResultMock);

        $this->assertEquals($result, $this->block->isDisplayBlock());
    }

    /**
     * Test isDisplayBlock method
     *
     * @param bool $loggedIn
     * @param int $customerGroupId
     * @param int $points
     * @param bool $result
     * @dataProvider isDisplayBlockDataProvider
     */
    public function testIsDisplayBlockNoStore(
        $loggedIn,
        $customerGroupId,
        $points,
        $result
    ) {
        $productId = 125;
        $websiteId = 2;
        $storeId = null;
        $appliedRuleIds = [11, 12];
        $promoText = 'Sample text';

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
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willThrowException(new LocalizedException(__('Error!')));

        if ($loggedIn) {
            $this->httpContextMock->expects($this->exactly(4))
                ->method('getValue')
                ->withConsecutive(
                    [CustomerContext::CONTEXT_AUTH],
                    [CustomerContext::CONTEXT_GROUP],
                    [CustomerContext::CONTEXT_AUTH],
                    [CustomerContext::CONTEXT_GROUP]
                )->willReturnOnConsecutiveCalls(
                    $loggedIn,
                    $customerGroupId,
                    $loggedIn,
                    $customerGroupId
                );
        } else {
            $this->httpContextMock->expects($this->exactly(2))
                ->method('getValue')
                ->with(CustomerContext::CONTEXT_AUTH)
                ->willReturn($loggedIn);

            $this->configMock->expects($this->any())
                ->method('getDefaultCustomerGroupIdForGuest')
                ->willReturn($customerGroupId);
        }

        $this->ruleApplierMock->expects($this->once())
            ->method('getAppliedRuleIds')
            ->with($productId, $customerGroupId, $websiteId)
            ->willReturn($appliedRuleIds);

        $this->categoryPromoTextResolverMock->expects($this->once())
            ->method('getPromoText')
            ->with($appliedRuleIds, $storeId)
            ->willReturn($promoText);

        $calculationResultMock = $this->getCalculationResultMock($points);

        $this->earningCalculatorMock->expects($this->once())
            ->method('calculationByProduct')
            ->with($this->productMock, true, null, null, $customerGroupId)
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
                'customerGroupId' => 3,
                'points' => 150,
                'result' => true,
            ],
            [
                'loggedIn' => false,
                'customerGroupId' => null,
                'points' => 150,
                'result' => true,
            ],
        ];
    }

    /**
     * Get calculation result mock
     *
     * @param int $maxPoints
     * @return ResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getCalculationResultMock($maxPoints)
    {
        $calculationResultMock = $this->createMock(ResultInterface::class);
        $calculationResultMock->expects($this->any())
            ->method('getPoints')
            ->willReturn($maxPoints);

        return $calculationResultMock;
    }

    /**
     * Test getPromoText method
     *
     * @param bool $loggedIn
     * @dataProvider getPromoTextDataProvider
     */
    public function testGetPromoText($loggedIn)
    {
        $productId = 125;
        $customerGroupId = 3;
        $websiteId = 2;
        $storeId = 3;
        $appliedRuleIds = [11, 12];
        $promoText = 'Sample text';

        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $this->block->setProduct($this->productMock);

        $websiteMock = $this->createMock(WebsiteInterface::class);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);
        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($websiteMock);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

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

        $this->categoryPromoTextResolverMock->expects($this->once())
            ->method('getPromoText')
            ->with($appliedRuleIds, $storeId)
            ->willReturn($promoText);

        $this->assertEquals($promoText, $this->block->getPromoText());
    }

    /**
     * Test getPromoText method
     *
     * @param bool $loggedIn
     * @dataProvider getPromoTextDataProvider
     */
    public function testGetPromoTextNoStore($loggedIn)
    {
        $productId = 125;
        $customerGroupId = 3;
        $websiteId = 2;
        $storeId = null;
        $appliedRuleIds = [11, 12];
        $promoText = 'Sample text';

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
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willThrowException(new LocalizedException(__("Error!")));

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

        $this->categoryPromoTextResolverMock->expects($this->once())
            ->method('getPromoText')
            ->with($appliedRuleIds, $storeId)
            ->willReturn($promoText);

        $this->assertEquals($promoText, $this->block->getPromoText());
    }

    /**
     * @return array
     */
    public function getPromoTextDataProvider()
    {
        return [
            ['loggedIn' => true],
            ['loggedIn' => false],
        ];
    }

    /**
     * Test getPromoText method if no website
     */
    public function testGetPromoTextNoWebsite()
    {
        $promoText = '';
        $appliedRuleIds = [];
        $storeId = 3;
        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->block->setProduct($this->productMock);

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willThrowException(new LocalizedException(__('Error!')));

        $this->ruleApplierMock->expects($this->never())
            ->method('getAppliedRuleIds');

        $this->categoryPromoTextResolverMock->expects($this->once())
            ->method('getPromoText')
            ->with($appliedRuleIds)
            ->willReturn($promoText);

        $this->assertEquals($promoText, $this->block->getPromoText());
    }

    /**
     * Test getPromoText method if no website
     */
    public function testGetPromoTextNoWebsiteNoStore()
    {
        $promoText = '';
        $appliedRuleIds = [];
        $storeId = null;
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willThrowException(new LocalizedException(__("Error!")));

        $this->block->setProduct($this->productMock);

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willThrowException(new LocalizedException(__('Error!')));

        $this->ruleApplierMock->expects($this->never())
            ->method('getAppliedRuleIds');

        $this->categoryPromoTextResolverMock->expects($this->once())
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

        $this->categoryPromoTextResolverMock->expects($this->once())
            ->method('getPromoText')
            ->with($appliedRuleIds)
            ->willReturn($promoText);

        $this->assertEquals($promoText, $this->block->getPromoText());
    }

    /**
     * Test getPromoText method if no product
     */
    public function testGetPromoTextNoProductNoStore()
    {
        $promoText = '';
        $websiteId = 2;
        $storeId = null;
        $appliedRuleIds = [];

        $websiteMock = $this->createMock(WebsiteInterface::class);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($websiteMock);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willThrowException(new LocalizedException(__("Error!")));

        $this->ruleApplierMock->expects($this->never())
            ->method('getAppliedRuleIds');

        $this->categoryPromoTextResolverMock->expects($this->once())
            ->method('getPromoText')
            ->with($appliedRuleIds)
            ->willReturn($promoText);

        $this->assertEquals($promoText, $this->block->getPromoText());
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
}
