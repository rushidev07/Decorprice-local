<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Data\Processor\Post\Transaction;

use Aheadworks\RewardPoints\Model\Data\Processor\Post\Transaction\Processor;
use Aheadworks\RewardPoints\Model\Filters\Transaction\ExpirationDate;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Filter\StripTags;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class ProcessorTest
 *
 * @package Aheadworks\RewardPoints\Test\Unit\Model\Data\Processor\Post\Transaction
 */
class ProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Processor
     */
    private $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FilterManager
     */
    private $filterManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StripTags
     */
    private $filterStripTagsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ExpirationDate
     */
    private $filterExpdateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ManagerInterface
     */
    private $messageManagerMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->filterManagerMock = $this->getMockBuilder(FilterManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'custselect'])
            ->getMockForAbstractClass();

        $this->filterStripTagsMock = $this->getMockBuilder(StripTags::class)
            ->disableOriginalConstructor()
            ->setMethods(['filter'])
            ->getMockForAbstractClass();

        $this->filterExpdateMock = $this->getMockBuilder(ExpirationDate::class)
            ->disableOriginalConstructor()
            ->setMethods(['filter'])
            ->getMockForAbstractClass();

        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['addSuccessMessage'])
            ->getMockForAbstractClass();

        $data = [
            'filterManager' => $this->filterManagerMock,
            'messageManager' => $this->messageManagerMock,
        ];

        $this->object = $objectManager->getObject(Processor::class, $data);
    }

    /**
     * Test filter method with empty params
     */
    public function testFilterMethodEmptyParams()
    {
        $this->assertEmpty($this->object->filter([]));
    }

    /**
     * Test filter method with 'comment_to_customer' and 'comment_to_admin' params
     */
    public function testFilterMethodForCommentFields()
    {
        $testData = [
            'comment_to_customer' => 'comment to customer',
            'comment_to_admin' => 'comment to admin',
        ];

        $this->filterManagerMock->expects($this->exactly(2))
            ->method('get')
            ->with('stripTags')
            ->willReturn($this->filterStripTagsMock);

        $this->filterStripTagsMock->expects($this->exactly(2))
            ->method('filter')
            ->withConsecutive(
                ['comment to customer'],
                ['comment to admin']
            )
            ->willReturnOnConsecutiveCalls(
                'comment to customer',
                'comment to admin'
            );

        $this->assertEquals($testData, $this->object->filter($testData));
    }

    /**
     * Test filter method for expiration date
     */
    public function testFilterMethodWithExpirationDate()
    {
        $testData = [
            'expiration_date' => '09/01/2016',
            'balance' => 10
        ];

        $this->filterManagerMock->expects($this->once())
            ->method('get')
            ->with('expdate', ['config' => []])
            ->willReturn($this->filterExpdateMock);

        $this->filterExpdateMock->expects($this->once())
            ->method('filter')
            ->with('09/01/2016')
            ->willReturn('09/01/2016');

        $this->assertEquals($testData, $this->object->filter($testData));
    }

    /**
     * Test filter method for expiration date and expire = 'expire_on_exact_days'
     */
    public function testFilterMethodWithExpireField()
    {
        $testData = [
            'expiration_date' => '09/01/2016',
            'expire' => 'expire_on_exact_days',
            'balance' => 10
        ];

        $expectData = [
            'expiration_date' => '09/01/2016',
            'balance' => 10
        ];

        $this->filterManagerMock->expects($this->once())
            ->method('get')
            ->with('expdate', ['config' => []])
            ->willReturn($this->filterExpdateMock);

        $this->filterExpdateMock->expects($this->once())
            ->method('filter')
            ->with('09/01/2016')
            ->willReturn('09/01/2016');

        $this->assertEquals($expectData, $this->object->filter($testData));
    }

    /**
     * Test filter method for expiration date and expire = 'expire_on_exact_days'
     */
    public function testFilterMethodWithExpireInXDays()
    {
        $testData = [
            'expiration_date' => '',
            'expire' => 'expire_in_x_days',
            'expire_in_days' => 10,
            'balance' => 10
        ];

        $expectData = [
            'expiration_date' => '09/10/2016',
            'balance' => 10
        ];

        $config = [
            'expire' => 'expire_in_x_days',
            'expire_in_days' => 10
        ];

        $this->filterManagerMock->expects($this->once())
            ->method('get')
            ->with('expdate', ['config' => $config])
            ->willReturn($this->filterExpdateMock);

        $this->filterExpdateMock->expects($this->once())
            ->method('filter')
            ->with('')
            ->willReturn('09/10/2016');

        $this->assertEquals($expectData, $this->object->filter($testData));
    }

    /**
     * Test customerSelectionFilter method
     */
    public function testCustomerSelectionFilterMethod()
    {
        $testData = [
            'customer_selection' => [1],
        ];

        $this->filterManagerMock->expects($this->once())
            ->method('custselect')
            ->with($testData)
            ->willReturn($testData);

        $this->assertEquals($testData, $this->object->customerSelectionFilter($testData));
    }
}
