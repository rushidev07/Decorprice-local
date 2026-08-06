<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Calculator;

use Aheadworks\RewardPoints\Model\Calculator\Earning as EarningCalculator;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemsResolver;
use Aheadworks\RewardPoints\Model\Calculator\Earning\Calculator;
use Aheadworks\RewardPoints\Model\Calculator\Earning\Predictor;
use Aheadworks\RewardPoints\Model\Calculator\ResultInterface;
use Aheadworks\RewardPoints\Model\Config;
use Aheadworks\RewardPoints\Model\Source\Calculation\PointsEarning;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface as Logger;

/**
 * Test for \Aheadworks\RewardPoints\Model\Calculator\Earning
 */
class EarningTest extends TestCase
{
    /**
     * @var EarningCalculator
     */
    private $earningCalculator;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var EarnItemsResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $earnItemsResolverMock;

    /**
     * @var Calculator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $calculatorMock;

    /**
     * @var Predictor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $predictorMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->configMock = $this->createMock(Config::class);
        $this->earnItemsResolverMock = $this->createMock(EarnItemsResolver::class);
        $this->calculatorMock = $this->createMock(Calculator::class);
        $this->predictorMock = $this->createMock(Predictor::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->loggerMock = $this->createMock(Logger::class);

        $this->earningCalculator = $objectManager->getObject(
            EarningCalculator::class,
            [
                'config' => $this->configMock,
                'earnItemsResolver' => $this->earnItemsResolverMock,
                'calculator' => $this->calculatorMock,
                'predictor' => $this->predictorMock,
                'storeManager' => $this->storeManagerMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    /**
     * Test calculationByQuote method
     *
     * @param bool $websiteSpecified
     * @param bool $beforeTax
     * @dataProvider calculationByDataProvider
     */
    public function testCalculationByQuote($websiteSpecified, $beforeTax)
    {
        $quoteMock = $this->createMock(Quote::class);
        $customerId = 10;
        $websiteId = 3;
        $pointsEarningCalculation = $beforeTax ? PointsEarning::BEFORE_TAX : PointsEarning::AFTER_TAX;
        $earnItems = [$this->createMock(EarnItemInterface::class)];
        $resultMock = $this->createMock(ResultInterface::class);

        if (!$websiteSpecified) {
            $this->setupStoreManager($websiteId);
        }

        $this->configMock->expects($this->once())
            ->method('getPointsEarningCalculation')
            ->with($websiteId)
            ->willReturn($pointsEarningCalculation);

        $this->earnItemsResolverMock->expects($this->once())
            ->method('getItemsByQuote')
            ->with($quoteMock, $beforeTax)
            ->willReturn($earnItems);

        $this->calculatorMock->expects($this->once())
            ->method('calculate')
            ->with($earnItems, $customerId, $websiteId)
            ->willReturn($resultMock);

        if (!$websiteSpecified) {
            $this->assertEquals(
                $resultMock,
                $this->earningCalculator->calculationByQuote($quoteMock, $customerId)
            );
        } else {
            $this->assertEquals(
                $resultMock,
                $this->earningCalculator->calculationByQuote($quoteMock, $customerId, $websiteId)
            );
        }
    }

    /**
     * @return array
     */
    public function calculationByDataProvider()
    {
        return [
            [
                'websiteSpecified' => true,
                'beforeTax' => true,
            ],
            [
                'websiteSpecified' => true,
                'beforeTax' => false,
            ],
            [
                'websiteSpecified' => false,
                'beforeTax' => true,
            ],
            [
                'websiteSpecified' => false,
                'beforeTax' => false,
            ],
        ];
    }

    /**
     * Test calculationByQuote method for quest
     *
     * @param bool $websiteSpecified
     * @param bool $beforeTax
     * @dataProvider calculationByDataProvider
     */
    public function testCalculationByQuoteNoCustomerId($websiteSpecified, $beforeTax)
    {
        $quoteMock = $this->createMock(Quote::class);
        $defaultCustomerGroupId = 5;
        $websiteId = 3;
        $pointsEarningCalculation = $beforeTax ? PointsEarning::BEFORE_TAX : PointsEarning::AFTER_TAX;
        $earnItems = [$this->createMock(EarnItemInterface::class)];
        $resultMock = $this->createMock(ResultInterface::class);

        if (!$websiteSpecified) {
            $this->setupStoreManager($websiteId);
        }

        $this->configMock->expects($this->once())
            ->method('getPointsEarningCalculation')
            ->with($websiteId)
            ->willReturn($pointsEarningCalculation);
        $this->configMock->expects($this->once())
            ->method('getDefaultCustomerGroupIdForGuest')
            ->willReturn($defaultCustomerGroupId);

        $this->earnItemsResolverMock->expects($this->once())
            ->method('getItemsByQuote')
            ->with($quoteMock, $beforeTax)
            ->willReturn($earnItems);

        $this->calculatorMock->expects($this->once())
            ->method('calculateByCustomerGroup')
            ->with($earnItems, $defaultCustomerGroupId, $websiteId)
            ->willReturn($resultMock);

        if (!$websiteSpecified) {
            $this->assertEquals(
                $resultMock,
                $this->earningCalculator->calculationByQuote($quoteMock, null)
            );
        } else {
            $this->assertEquals(
                $resultMock,
                $this->earningCalculator->calculationByQuote($quoteMock, null, $websiteId)
            );
        }
    }

    /**
     * Test calculationByQuote method if no website
     */
    public function testCalculationByQuoteNoWebsiteSpecified()
    {
        $quoteMock = $this->createMock(Quote::class);
        $customerId = 10;
        $resultMock = $this->createMock(ResultInterface::class);

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willThrowException(new LocalizedException(__('No such entity!')));

        $this->earnItemsResolverMock->expects($this->never())
            ->method('getItemsByQuote');

        $this->calculatorMock->expects($this->never())
            ->method('calculate');
        $this->calculatorMock->expects($this->once())
            ->method('getEmptyResult')
            ->willReturn($resultMock);

        $this->assertEquals($resultMock, $this->earningCalculator->calculationByQuote($quoteMock, $customerId));
    }

    /**
     * Test calculationByQuote method if an exception occurs
     */
    public function testCalculationByQuoteException()
    {
        $quoteMock = $this->createMock(Quote::class);
        $customerId = 10;
        $websiteId = 3;
        $beforeTax = true;
        $pointsEarningCalculation = $beforeTax ? PointsEarning::BEFORE_TAX : PointsEarning::AFTER_TAX;
        $exceptionMessage = 'Error!';
        $resultMock = $this->createMock(ResultInterface::class);

        $this->configMock->expects($this->once())
            ->method('getPointsEarningCalculation')
            ->with($websiteId)
            ->willReturn($pointsEarningCalculation);

        $this->earnItemsResolverMock->expects($this->once())
            ->method('getItemsByQuote')
            ->with($quoteMock, $beforeTax)
            ->willThrowException(new \Exception($exceptionMessage));

        $this->calculatorMock->expects($this->never())
            ->method('calculate');
        $this->calculatorMock->expects($this->once())
            ->method('getEmptyResult')
            ->willReturn($resultMock);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exceptionMessage)
            ->willReturnSelf();

        $this->assertEquals(
            $resultMock,
            $this->earningCalculator->calculationByQuote($quoteMock, $customerId, $websiteId)
        );
    }

    /**
     * Test calculationByInvoice method
     *
     * @param bool $websiteSpecified
     * @param bool $beforeTax
     * @dataProvider calculationByDataProvider
     */
    public function testCalculationByInvoice($websiteSpecified, $beforeTax)
    {
        $invoiceMock = $this->createMock(InvoiceInterface::class);
        $customerId = 10;
        $websiteId = 3;
        $pointsEarningCalculation = $beforeTax ? PointsEarning::BEFORE_TAX : PointsEarning::AFTER_TAX;
        $earnItems = [$this->createMock(EarnItemInterface::class)];
        $resultMock = $this->createMock(ResultInterface::class);

        if (!$websiteSpecified) {
            $this->setupStoreManager($websiteId);
        }

        $this->configMock->expects($this->once())
            ->method('getPointsEarningCalculation')
            ->with($websiteId)
            ->willReturn($pointsEarningCalculation);

        $this->earnItemsResolverMock->expects($this->once())
            ->method('getItemsByInvoice')
            ->with($invoiceMock, $beforeTax)
            ->willReturn($earnItems);

        $this->calculatorMock->expects($this->once())
            ->method('calculate')
            ->with($earnItems, $customerId, $websiteId)
            ->willReturn($resultMock);

        if (!$websiteSpecified) {
            $this->assertEquals(
                $resultMock,
                $this->earningCalculator->calculationByInvoice($invoiceMock, $customerId)
            );
        } else {
            $this->assertEquals(
                $resultMock,
                $this->earningCalculator->calculationByInvoice($invoiceMock, $customerId, $websiteId)
            );
        }
    }

    /**
     * Test calculationByInvoice method if no website
     */
    public function testCalculationByInvoiceNoWebsiteSpecified()
    {
        $invoiceMock = $this->createMock(InvoiceInterface::class);
        $customerId = 10;
        $resultMock = $this->createMock(ResultInterface::class);

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willThrowException(new LocalizedException(__('No such entity!')));

        $this->earnItemsResolverMock->expects($this->never())
            ->method('getItemsByInvoice');

        $this->calculatorMock->expects($this->never())
            ->method('calculate');
        $this->calculatorMock->expects($this->once())
            ->method('getEmptyResult')
            ->willReturn($resultMock);

        $this->assertEquals($resultMock, $this->earningCalculator->calculationByInvoice($invoiceMock, $customerId));
    }

    /**
     * Test calculationByInvoice method if an exception occurs
     */
    public function testCalculationByInvoiceException()
    {
        $invoiceMock = $this->createMock(InvoiceInterface::class);
        $customerId = 10;
        $websiteId = 3;
        $beforeTax = true;
        $pointsEarningCalculation = $beforeTax ? PointsEarning::BEFORE_TAX : PointsEarning::AFTER_TAX;
        $exceptionMessage = 'Error!';
        $resultMock = $this->createMock(ResultInterface::class);

        $this->configMock->expects($this->once())
            ->method('getPointsEarningCalculation')
            ->with($websiteId)
            ->willReturn($pointsEarningCalculation);

        $this->earnItemsResolverMock->expects($this->once())
            ->method('getItemsByInvoice')
            ->with($invoiceMock, $beforeTax)
            ->willThrowException(new \Exception($exceptionMessage));

        $this->calculatorMock->expects($this->never())
            ->method('calculate');
        $this->calculatorMock->expects($this->once())
            ->method('getEmptyResult')
            ->willReturn($resultMock);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exceptionMessage)
            ->willReturnSelf();

        $this->assertEquals(
            $resultMock,
            $this->earningCalculator->calculationByInvoice($invoiceMock, $customerId, $websiteId)
        );
    }

    /**
     * Test calculationByCreditmemo method
     *
     * @param bool $websiteSpecified
     * @param bool $beforeTax
     * @dataProvider calculationByDataProvider
     */
    public function testCalculationByCreditmemo($websiteSpecified, $beforeTax)
    {
        $creditmemoMock = $this->createMock(CreditmemoInterface::class);
        $customerId = 10;
        $websiteId = 3;
        $pointsEarningCalculation = $beforeTax ? PointsEarning::BEFORE_TAX : PointsEarning::AFTER_TAX;
        $earnItems = [$this->createMock(EarnItemInterface::class)];
        $resultMock = $this->createMock(ResultInterface::class);

        if (!$websiteSpecified) {
            $this->setupStoreManager($websiteId);
        }

        $this->configMock->expects($this->once())
            ->method('getPointsEarningCalculation')
            ->with($websiteId)
            ->willReturn($pointsEarningCalculation);

        $this->earnItemsResolverMock->expects($this->once())
            ->method('getItemsByCreditmemo')
            ->with($creditmemoMock, $beforeTax)
            ->willReturn($earnItems);

        $this->calculatorMock->expects($this->once())
            ->method('calculate')
            ->with($earnItems, $customerId, $websiteId)
            ->willReturn($resultMock);

        if (!$websiteSpecified) {
            $this->assertEquals(
                $resultMock,
                $this->earningCalculator->calculationByCreditmemo($creditmemoMock, $customerId)
            );
        } else {
            $this->assertEquals(
                $resultMock,
                $this->earningCalculator->calculationByCreditmemo($creditmemoMock, $customerId, $websiteId)
            );
        }
    }

    /**
     * Test calculationByCreditmemo method if no website
     */
    public function testCalculationByCreditmemoNoWebsiteSpecified()
    {
        $creditmemoMock = $this->createMock(CreditmemoInterface::class);
        $customerId = 10;
        $resultMock = $this->createMock(ResultInterface::class);

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willThrowException(new LocalizedException(__('No such entity!')));

        $this->earnItemsResolverMock->expects($this->never())
            ->method('getItemsByCreditmemo');

        $this->calculatorMock->expects($this->never())
            ->method('calculate');
        $this->calculatorMock->expects($this->once())
            ->method('getEmptyResult')
            ->willReturn($resultMock);

        $this->assertEquals(
            $resultMock,
            $this->earningCalculator->calculationByCreditmemo($creditmemoMock, $customerId)
        );
    }

    /**
     * Test calculationByCreditmemo method if an exception occurs
     */
    public function testCalculationByCreditmemoException()
    {
        $creditmemoMock = $this->createMock(CreditmemoInterface::class);
        $customerId = 10;
        $websiteId = 3;
        $beforeTax = true;
        $pointsEarningCalculation = $beforeTax ? PointsEarning::BEFORE_TAX : PointsEarning::AFTER_TAX;
        $exceptionMessage = 'Error!';
        $resultMock = $this->createMock(ResultInterface::class);

        $this->configMock->expects($this->once())
            ->method('getPointsEarningCalculation')
            ->with($websiteId)
            ->willReturn($pointsEarningCalculation);

        $this->earnItemsResolverMock->expects($this->once())
            ->method('getItemsByCreditmemo')
            ->with($creditmemoMock, $beforeTax)
            ->willThrowException(new \Exception($exceptionMessage));

        $this->calculatorMock->expects($this->never())
            ->method('calculate');
        $this->calculatorMock->expects($this->once())
            ->method('getEmptyResult')
            ->willReturn($resultMock);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exceptionMessage)
            ->willReturnSelf();

        $this->calculatorMock->expects($this->once())
            ->method('getEmptyResult')
            ->willReturn($resultMock);

        $this->assertEquals(
            $resultMock,
            $this->earningCalculator->calculationByCreditmemo($creditmemoMock, $customerId, $websiteId)
        );
    }

    /**
     * Test calculationByProduct method
     *
     * @param bool $mergeRuleIds
     * @param bool $websiteSpecified
     * @param bool $beforeTax
     * @dataProvider calculationByProductDataProvider
     */
    public function testCalculationByProduct($mergeRuleIds, $websiteSpecified, $beforeTax)
    {
        $productMock = $this->createMock(Product::class);
        $customerId = 10;
        $websiteId = 3;
        $pointsEarningCalculation = $beforeTax ? PointsEarning::BEFORE_TAX : PointsEarning::AFTER_TAX;
        $earnItems = [$this->createMock(EarnItemInterface::class)];
        $resultMock = $this->createMock(ResultInterface::class);

        if (!$websiteSpecified) {
            $this->setupStoreManager($websiteId);
        }

        $this->configMock->expects($this->once())
            ->method('getPointsEarningCalculation')
            ->with($websiteId)
            ->willReturn($pointsEarningCalculation);

        $this->earnItemsResolverMock->expects($this->once())
            ->method('getItemsByProduct')
            ->with($productMock, $beforeTax)
            ->willReturn($earnItems);

        $this->predictorMock->expects($this->once())
            ->method('calculateMaxPointsForCustomer')
            ->with($earnItems, $customerId, $websiteId, $mergeRuleIds)
            ->willReturn($resultMock);

        if (!$websiteSpecified) {
            $this->assertEquals(
                $resultMock,
                $this->earningCalculator->calculationByProduct($productMock, $mergeRuleIds, $customerId)
            );
        } else {
            $this->assertEquals(
                $resultMock,
                $this->earningCalculator->calculationByProduct($productMock, $mergeRuleIds, $customerId, $websiteId)
            );
        }
    }

    /**
     * @return array
     */
    public function calculationByProductDataProvider()
    {
        return [
            [
                'mergeRuleIds' => false,
                'websiteSpecified' => true,
                'beforeTax' => true,
            ],
            [
                'mergeRuleIds' => false,
                'websiteSpecified' => true,
                'beforeTax' => false,
            ],
            [
                'mergeRuleIds' => false,
                'websiteSpecified' => false,
                'beforeTax' => true,
            ],
            [
                'mergeRuleIds' => false,
                'websiteSpecified' => false,
                'beforeTax' => false,
            ],
            [
                'mergeRuleIds' => true,
                'websiteSpecified' => true,
                'beforeTax' => true,
            ],
            [
                'mergeRuleIds' => true,
                'websiteSpecified' => true,
                'beforeTax' => false,
            ],
            [
                'mergeRuleIds' => true,
                'websiteSpecified' => false,
                'beforeTax' => true,
            ],
            [
                'mergeRuleIds' => true,
                'websiteSpecified' => false,
                'beforeTax' => false,
            ],
        ];
    }

    /**
     * Test calculationByProduct method if no customer specified (guest)
     *
     * @param bool $mergeRuleIds
     * @param bool $websiteSpecified
     * @param bool $beforeTax
     * @dataProvider calculationByProductDataProvider
     */
    public function testCalculationByProductNoCustomer($mergeRuleIds, $websiteSpecified, $beforeTax)
    {
        $productMock = $this->createMock(Product::class);
        $customerId = null;
        $websiteId = 3;
        $pointsEarningCalculation = $beforeTax ? PointsEarning::BEFORE_TAX : PointsEarning::AFTER_TAX;
        $earnItems = [$this->createMock(EarnItemInterface::class)];
        $resultMock = $this->createMock(ResultInterface::class);

        if (!$websiteSpecified) {
            $this->setupStoreManager($websiteId);
        }

        $this->configMock->expects($this->once())
            ->method('getPointsEarningCalculation')
            ->with($websiteId)
            ->willReturn($pointsEarningCalculation);

        $this->earnItemsResolverMock->expects($this->once())
            ->method('getItemsByProduct')
            ->with($productMock, $beforeTax)
            ->willReturn($earnItems);

        $this->predictorMock->expects($this->once())
            ->method('calculateMaxPointsForGuest')
            ->with($earnItems, $websiteId, $mergeRuleIds)
            ->willReturn($resultMock);

        if (!$websiteSpecified) {
            $this->assertEquals(
                $resultMock,
                $this->earningCalculator->calculationByProduct($productMock, $mergeRuleIds, $customerId)
            );
        } else {
            $this->assertEquals(
                $resultMock,
                $this->earningCalculator->calculationByProduct($productMock, $mergeRuleIds, $customerId, $websiteId)
            );
        }
    }

    /**
     * Test calculationByProduct method if no website
     */
    public function testCalculationByProductNoWebsiteSpecified()
    {
        $productMock = $this->createMock(Product::class);
        $customerId = 10;
        $resultMock = $this->createMock(ResultInterface::class);
        $mergeRuleIds = true;

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willThrowException(new LocalizedException(__('No such entity!')));

        $this->earnItemsResolverMock->expects($this->never())
            ->method('getItemsByProduct');

        $this->predictorMock->expects($this->never())
            ->method('calculateMaxPointsForCustomer');
        $this->predictorMock->expects($this->never())
            ->method('calculateMaxPointsForGuest');
        $this->calculatorMock->expects($this->once())
            ->method('getEmptyResult')
            ->willReturn($resultMock);

        $this->assertEquals(
            $resultMock,
            $this->earningCalculator->calculationByProduct($productMock, $mergeRuleIds, $customerId)
        );
    }

    /**
     * Test calculationByProduct method if an exception occurs
     */
    public function testCalculationByProductException()
    {
        $productMock = $this->createMock(Product::class);
        $mergeRuleIds = true;
        $customerId = 10;
        $websiteId = 3;
        $beforeTax = true;
        $pointsEarningCalculation = $beforeTax ? PointsEarning::BEFORE_TAX : PointsEarning::AFTER_TAX;
        $exceptionMessage = 'Error!';
        $resultMock = $this->createMock(ResultInterface::class);

        $this->configMock->expects($this->once())
            ->method('getPointsEarningCalculation')
            ->with($websiteId)
            ->willReturn($pointsEarningCalculation);

        $this->earnItemsResolverMock->expects($this->once())
            ->method('getItemsByProduct')
            ->with($productMock, $beforeTax)
            ->willThrowException(new \Exception($exceptionMessage));

        $this->predictorMock->expects($this->never())
            ->method('calculateMaxPointsForCustomer');
        $this->predictorMock->expects($this->never())
            ->method('calculateMaxPointsForGuest');
        $this->calculatorMock->expects($this->once())
            ->method('getEmptyResult')
            ->willReturn($resultMock);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exceptionMessage)
            ->willReturnSelf();

        $this->calculatorMock->expects($this->once())
            ->method('getEmptyResult')
            ->willReturn($resultMock);

        $this->assertEquals(
            $resultMock,
            $this->earningCalculator->calculationByProduct($productMock, $mergeRuleIds, $customerId, $websiteId)
        );
    }

    /**
     * Setup store manager
     *
     * @param int $websiteId
     * @return void
     */
    private function setupStoreManager($websiteId)
    {
        $websiteMock = $this->createMock(Website::class);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($websiteMock);
    }

    /**
     * Test calculationByProduct method for customer group
     *
     * @param bool $mergeRuleIds
     * @param int|null $customerId
     * @param bool $websiteSpecified
     * @param int|null $customerGroupId
     * @param bool $beforeTax
     * @dataProvider calculationByProductForCustomerGroupDataProvider
     */
    public function testCalculationByProductForCustomerGroup(
        $mergeRuleIds,
        $customerId,
        $websiteSpecified,
        $customerGroupId,
        $beforeTax
    ) {
        $productMock = $this->createMock(Product::class);
        //$customerId = 10;
        $websiteId = 3;
        $pointsEarningCalculation = $beforeTax ? PointsEarning::BEFORE_TAX : PointsEarning::AFTER_TAX;
        $earnItems = [$this->createMock(EarnItemInterface::class)];
        $resultMock = $this->createMock(ResultInterface::class);

        if (!$websiteSpecified) {
            $this->setupStoreManager($websiteId);
        }

        $this->configMock->expects($this->once())
            ->method('getPointsEarningCalculation')
            ->with($websiteId)
            ->willReturn($pointsEarningCalculation);

        $this->earnItemsResolverMock->expects($this->once())
            ->method('getItemsByProduct')
            ->with($productMock, $beforeTax)
            ->willReturn($earnItems);

        $this->predictorMock->expects($this->once())
            ->method('calculateMaxPointsForCustomerGroup')
            ->with($earnItems, $websiteId, $customerGroupId, $mergeRuleIds)
            ->willReturn($resultMock);

        $this->predictorMock->expects($this->never())
            ->method('calculateMaxPointsForCustomer');
        $this->predictorMock->expects($this->never())
            ->method('calculateMaxPointsForGuest');

        if (!$websiteSpecified) {
            $this->assertEquals(
                $resultMock,
                $this->earningCalculator->calculationByProduct(
                    $productMock,
                    $mergeRuleIds,
                    $customerId,
                    null,
                    $customerGroupId
                )
            );
        } else {
            $this->assertEquals(
                $resultMock,
                $this->earningCalculator->calculationByProduct(
                    $productMock,
                    $mergeRuleIds,
                    $customerId,
                    $websiteId,
                    $customerGroupId
                )
            );
        }
    }

    /**
     * @return array
     */
    public function calculationByProductForCustomerGroupDataProvider()
    {
        return [
            [
                'mergeRuleIds' => false,
                'customerId' => 1,
                'websiteSpecified' => true,
                'customerGroupId' => 1,
                'beforeTax' => true,
            ],
            [
                'mergeRuleIds' => false,
                'customerId' => 1,
                'websiteSpecified' => true,
                'customerGroupId' => 1,
                'beforeTax' => false,
            ],
            [
                'mergeRuleIds' => false,
                'customerId' => 1,
                'websiteSpecified' => false,
                'customerGroupId' => 1,
                'beforeTax' => true,
            ],
            [
                'mergeRuleIds' => false,
                'customerId' => 1,
                'websiteSpecified' => false,
                'customerGroupId' => 1,
                'beforeTax' => false,
            ],
            [
                'mergeRuleIds' => true,
                'customerId' => 1,
                'websiteSpecified' => true,
                'customerGroupId' => 1,
                'beforeTax' => true,
            ],
            [
                'mergeRuleIds' => true,
                'customerId' => 1,
                'websiteSpecified' => true,
                'customerGroupId' => 1,
                'beforeTax' => false,
            ],
            [
                'mergeRuleIds' => true,
                'customerId' => 1,
                'websiteSpecified' => false,
                'customerGroupId' => 1,
                'beforeTax' => true,
            ],
            [
                'mergeRuleIds' => true,
                'customerId' => 1,
                'websiteSpecified' => false,
                'customerGroupId' => 1,
                'beforeTax' => false,
            ],
            [
                'mergeRuleIds' => false,
                'customerId' => null,
                'websiteSpecified' => true,
                'customerGroupId' => 1,
                'beforeTax' => true,
            ],
            [
                'mergeRuleIds' => false,
                'customerId' => null,
                'websiteSpecified' => true,
                'customerGroupId' => 1,
                'beforeTax' => false,
            ],
            [
                'mergeRuleIds' => false,
                'customerId' => null,
                'websiteSpecified' => false,
                'customerGroupId' => 1,
                'beforeTax' => true,
            ],
            [
                'mergeRuleIds' => false,
                'customerId' => null,
                'websiteSpecified' => false,
                'customerGroupId' => 1,
                'beforeTax' => false,
            ],
            [
                'mergeRuleIds' => true,
                'customerId' => null,
                'websiteSpecified' => true,
                'customerGroupId' => 1,
                'beforeTax' => true,
            ],
            [
                'mergeRuleIds' => true,
                'customerId' => null,
                'websiteSpecified' => true,
                'customerGroupId' => 1,
                'beforeTax' => false,
            ],
            [
                'mergeRuleIds' => true,
                'customerId' => null,
                'websiteSpecified' => false,
                'customerGroupId' => 1,
                'beforeTax' => true,
            ],
            [
                'mergeRuleIds' => true,
                'customerId' => null,
                'websiteSpecified' => false,
                'customerGroupId' => 1,
                'beforeTax' => false,
            ],
        ];
    }
}
