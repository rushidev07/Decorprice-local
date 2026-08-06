<?php
namespace Aheadworks\RewardPoints\Test\Unit\Block\Product\View;

use Aheadworks\RewardPoints\Block\Product\View\Earning;
use Aheadworks\RewardPoints\Model\Calculator\Earning as EarningCalculator;
use Aheadworks\RewardPoints\Model\Calculator\ResultInterface;
use Aheadworks\RewardPoints\Model\EarnRule\ProductPromoTextResolver;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Test for \Aheadworks\RewardPoints\Block\Product\View\Earning
 */
class EarningTest extends TestCase
{
    /**
     * @var Earning
     */
    private $block;

    /**
     * @var  Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var EarningCalculator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $earningCalculatorMock;

    /**
     * @var CustomerSession|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepositoryMock;

    /**
     * @var ProductPromoTextResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productPromoTextResolverMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

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

        $this->earningCalculatorMock = $this->createMock(EarningCalculator::class);
        $this->customerSessionMock = $this->createMock(CustomerSession::class);
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->productPromoTextResolverMock = $this->createMock(ProductPromoTextResolver::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);

        $this->block = $objectManager->getObject(
            Earning::class,
            [
                'context' => $this->contextMock,
                'earningCalculator' => $this->earningCalculatorMock,
                'customerSession' => $this->customerSessionMock,
                'productRepository' => $this->productRepositoryMock,
                'productPromoTextResolver' => $this->productPromoTextResolverMock,
                'storeManager' => $this->storeManagerMock,
            ]
        );
    }

    /**
     * Test isAjax method
     *
     * @param bool $isAjax
     * @dataProvider isAjaxDataProvider
     */
    public function testIsAjax($isAjax)
    {
        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn($isAjax);

        $this->assertEquals($isAjax, $this->block->isAjax());
    }

    /**
     * @return array
     */
    public function isAjaxDataProvider()
    {
        return [
            ['isAjax' => true],
            ['isAjax' => false],
        ];
    }

    /**
     * Test isDisplayBlock method
     *
     * @param $isLoggedIn
     * @param $storeId
     * @param $text
     * @param $maxPoints
     * @param $result
     * @dataProvider isDisplayBlockDataProvider
     * @throws \ReflectionException
     */
    public function testIsDisplayBlock($isLoggedIn, $storeId, $text, $maxPoints, $result)
    {
        if (isset($storeId)) {
            $storeMock = $this->createMock(StoreInterface::class);
            $storeMock->expects($this->once())
                ->method('getId')
                ->willReturn($storeId);
            $this->storeManagerMock->expects($this->once())
                ->method('getStore')
                ->willReturn($storeMock);
        } else {
            $this->storeManagerMock->expects($this->once())
                ->method('getStore')
                ->willThrowException(new LocalizedException(__("Error!")));
        }
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn($isLoggedIn);

        $productMock = $this->createMock(Product::class);
        $this->setProperty('product', $productMock);

        $appliedRuleIds = [10, 11];
        $calculationResultMock = $this->createMock(ResultInterface::class);
        $calculationResultMock->expects($this->any())
            ->method('getPoints')
            ->willReturn($maxPoints);
        $calculationResultMock->expects($this->any())
            ->method('getAppliedRuleIds')
            ->willReturn($appliedRuleIds);
        $this->setProperty('calculationResult', $calculationResultMock);

        $this->productPromoTextResolverMock->expects($this->once())
            ->method('getPromoText')
            ->with($appliedRuleIds, $storeId, $isLoggedIn)
            ->willReturn($text);

        $this->assertEquals($result, $this->block->isDisplayBlock());
    }

    /**
     * @return array
     */
    public function isDisplayBlockDataProvider()
    {
        return [
            [
                'isLoggedIn' => false,
                'storeId' => 2,
                'text' => 'Sample text',
                'maxPoints' => 10,
                'result' => true
            ],
            [
                'isLoggedIn' => false,
                'storeId' => 2,
                'text' => null,
                'maxPoints' => 10,
                'result' => false
            ],
            [
                'isLoggedIn' => false,
                'storeId' => 2,
                'text' => 'Sample text',
                'maxPoints' => 0,
                'result' => false
            ],
            [
                'isLoggedIn' => true,
                'storeId' => 2,
                'text' => 'Sample text',
                'maxPoints' => 10,
                'result' => true
            ],
            [
                'isLoggedIn' => true,
                'storeId' => 2,
                'text' => null,
                'maxPoints' => 10,
                'result' => false
            ],
            [
                'isLoggedIn' => true,
                'storeId' => 2,
                'text' => 'Sample text',
                'maxPoints' => 0,
                'result' => false
            ],
            [
                'isLoggedIn' => false,
                'storeId' => null,
                'text' => 'Sample text',
                'maxPoints' => 10,
                'result' => true
            ],
            [
                'isLoggedIn' => false,
                'storeId' => null,
                'text' => null,
                'maxPoints' => 10,
                'result' => false
            ],
            [
                'isLoggedIn' => false,
                'storeId' => null,
                'text' => 'Sample text',
                'maxPoints' => 0,
                'result' => false
            ],
            [
                'isLoggedIn' => true,
                'storeId' => null,
                'text' => 'Sample text',
                'maxPoints' => 10,
                'result' => true
            ],
            [
                'isLoggedIn' => true,
                'storeId' => null,
                'text' => null,
                'maxPoints' => 10,
                'result' => false
            ],
            [
                'isLoggedIn' => true,
                'storeId' => null,
                'text' => 'Sample text',
                'maxPoints' => 0,
                'result' => false
            ],
        ];
    }

    /**
     * Test getPromoText method
     *
     * @param bool $isLoggedIn
     * @param int|null $storeId
     * @param string $text
     * @param int $maxPoints
     * @param string $result
     * @dataProvider getPromoTextDataProvider
     * @throws \ReflectionException
     */
    public function testGetPromoText($isLoggedIn, $storeId, $text, $maxPoints, $result)
    {
        if (isset($storeId)) {
            $storeMock = $this->createMock(StoreInterface::class);
            $storeMock->expects($this->once())
                ->method('getId')
                ->willReturn($storeId);
            $this->storeManagerMock->expects($this->once())
                ->method('getStore')
                ->willReturn($storeMock);
        } else {
            $this->storeManagerMock->expects($this->once())
                ->method('getStore')
                ->willThrowException(new LocalizedException(__("Error!")));
        }

        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn($isLoggedIn);

        $productMock = $this->createMock(Product::class);
        $this->setProperty('product', $productMock);

        $appliedRuleIds = [10, 11];
        $calculationResultMock = $this->createMock(ResultInterface::class);
        $calculationResultMock->expects($this->any())
            ->method('getPoints')
            ->willReturn($maxPoints);
        $calculationResultMock->expects($this->any())
            ->method('getAppliedRuleIds')
            ->willReturn($appliedRuleIds);
        $this->setProperty('calculationResult', $calculationResultMock);

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
                'isLoggedIn' => false,
                'storeId' => 2,
                'text' => 'Sample %X points',
                'maxPoints' => 10,
                'result' => 'Sample 10 points'
            ],
            [
                'isLoggedIn' => true,
                'storeId' => 2,
                'text' => 'Sample %X points',
                'maxPoints' => 10,
                'result' => 'Sample 10 points'
            ],
            [
                'isLoggedIn' => false,
                'storeId' => 2,
                'text' => 'Sample %X points',
                'maxPoints' => 125,
                'result' => 'Sample 125 points'
            ],
            [
                'isLoggedIn' => true,
                'storeId' => 2,
                'text' => 'Sample %X points',
                'maxPoints' => 125,
                'result' => 'Sample 125 points'
            ],
            [
                'isLoggedIn' => false,
                'storeId' => 2,
                'text' => '',
                'maxPoints' => 125,
                'result' => ''
            ],
            [
                'isLoggedIn' => true,
                'storeId' => 2,
                'text' => '',
                'maxPoints' => 125,
                'result' => ''
            ],
            [
                'isLoggedIn' => false,
                'storeId' => 2,
                'text' => 'Sample %X points',
                'maxPoints' => 0,
                'result' => 'Sample 0 points'
            ],
            [
                'isLoggedIn' => true,
                'storeId' => 2,
                'text' => 'Sample %X points',
                'maxPoints' => 0,
                'result' => 'Sample 0 points'
            ],
            [
                'isLoggedIn' => false,
                'storeId' => null,
                'text' => 'Sample %X points',
                'maxPoints' => 10,
                'result' => 'Sample 10 points'
            ],
            [
                'isLoggedIn' => true,
                'storeId' => null,
                'text' => 'Sample %X points',
                'maxPoints' => 10,
                'result' => 'Sample 10 points'
            ],
            [
                'isLoggedIn' => false,
                'storeId' => null,
                'text' => 'Sample %X points',
                'maxPoints' => 125,
                'result' => 'Sample 125 points'
            ],
            [
                'isLoggedIn' => true,
                'storeId' => null,
                'text' => 'Sample %X points',
                'maxPoints' => 125,
                'result' => 'Sample 125 points'
            ],
            [
                'isLoggedIn' => false,
                'storeId' => null,
                'text' => '',
                'maxPoints' => 125,
                'result' => ''
            ],
            [
                'isLoggedIn' => true,
                'storeId' => null,
                'text' => '',
                'maxPoints' => 125,
                'result' => ''
            ],
            [
                'isLoggedIn' => false,
                'storeId' => null,
                'text' => 'Sample %X points',
                'maxPoints' => 0,
                'result' => 'Sample 0 points'
            ],
            [
                'isLoggedIn' => true,
                'storeId' => null,
                'text' => 'Sample %X points',
                'maxPoints' => 0,
                'result' => 'Sample 0 points'
            ],
        ];
    }

    /**
     * Test getMaxPossibleEarningPoints method
     *
     * @param int|null $productId
     * @param int|null $id
     * @param Product|\PHPUnit_Framework_MockObject_MockObject|null $product
     * @param int|null $customerId
     * @param int $points
     * @dataProvider getMaxPossibleEarningPointsDataProvider
     */
    public function testGetMaxPossibleEarningPoints($productId, $id, $product, $customerId, $points)
    {
        $map = [
            ['product_id', null, $productId],
            ['id', null, $id]
        ];
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->will($this->returnValueMap($map));

        if ($productId || $id) {
            $productId = $productId ? $productId : $id;

            if ($product == null) {
                $this->productRepositoryMock->expects($this->once())
                    ->method('getById')
                    ->with($productId)
                    ->willThrowException(new NoSuchEntityException(__('No such entity!')));

$this->expectException(NoSuchEntityException::class);
} else {
                $this->productRepositoryMock->expects($this->once())
                    ->method('getById')
                    ->with($productId)
                    ->willReturn($product);
            }

            $this->customerSessionMock->expects($this->once())
                ->method('getCustomerId')
                ->willReturn($customerId);

            $resultMock = $this->createMock(ResultInterface::class);
            $resultMock->expects($this->once())
                ->method('getPoints')
                ->willReturn($points);

            $this->earningCalculatorMock->expects($this->once())
                ->method('calculationByProduct')
                ->with($product, true, $customerId)
                ->willReturn($resultMock);
        }

        $this->assertEquals($points, $this->block->getMaxPossibleEarningPoints());
    }

    /**
     * @return array
     */
    public function getMaxPossibleEarningPointsDataProvider()
    {
        return [
            [
                'productId' => 125,
                'id' => null,
                'product' => $this->createMock(Product::class),
                'customerId' => 10,
                'points' => 20
            ],
            [
                'productId' => 125,
                'id' => null,
                'product' => $this->createMock(Product::class),
                'customerId' => null,
                'points' => 30
            ],
            [
                'productId' => null,
                'id' => 125,
                'product' => $this->createMock(Product::class),
                'customerId' => 10,
                'points' => 20
            ],
            [
                'productId' => null,
                'id' => null,
                'product' => null,
                'customerId' => 10,
                'points' => 0
            ],
        ];
    }

    /**
     * Test getAppliedRuleIds method
     *
     * @param int|null $productId
     * @param int|null $id
     * @param Product|\PHPUnit_Framework_MockObject_MockObject|null $product
     * @param int|null $customerId
     * @param int[] $appliedRuleIds
     * @dataProvider getAppliedRuleIdsDataProvider
     */
    public function testGetAppliedRuleIds($productId, $id, $product, $customerId, $appliedRuleIds)
    {
        $map = [
            ['product_id', null, $productId],
            ['id', null, $id]
        ];
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->will($this->returnValueMap($map));

        if ($productId || $id) {
            $productId = $productId ? $productId : $id;

            if ($product == null) {
                $this->productRepositoryMock->expects($this->once())
                    ->method('getById')
                    ->with($productId)
                    ->willThrowException(new NoSuchEntityException(__('No such entity!')));
            } else {
                $this->productRepositoryMock->expects($this->once())
                    ->method('getById')
                    ->with($productId)
                    ->willReturn($product);
            }

            $this->customerSessionMock->expects($this->once())
                ->method('getCustomerId')
                ->willReturn($customerId);

            $resultMock = $this->createMock(ResultInterface::class);
            $resultMock->expects($this->once())
                ->method('getAppliedRuleIds')
                ->willReturn($appliedRuleIds);

            $this->earningCalculatorMock->expects($this->once())
                ->method('calculationByProduct')
                ->with($product, true, $customerId)
                ->willReturn($resultMock);
        }

        $this->assertEquals($appliedRuleIds, $this->block->getAppliedRuleIds());
    }

    /**
     * @return array
     */
    public function getAppliedRuleIdsDataProvider()
    {
        return [
            [
                'productId' => 125,
                'id' => null,
                'product' => $this->createMock(Product::class),
                'customerId' => 10,
                'appliedRuleIds' => [11, 12, 13]
            ],
            [
                'productId' => 125,
                'id' => null,
                'product' => $this->createMock(Product::class),
                'customerId' => null,
                'appliedRuleIds' => [11, 12]
            ],
            [
                'productId' => null,
                'id' => 125,
                'product' => $this->createMock(Product::class),
                'customerId' => 10,
                'appliedRuleIds' => [11, 12]
            ],
            [
                'productId' => null,
                'id' => null,
                'product' => null,
                'customerId' => 10,
                'appliedRuleIds' => []
            ],
        ];
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
}
