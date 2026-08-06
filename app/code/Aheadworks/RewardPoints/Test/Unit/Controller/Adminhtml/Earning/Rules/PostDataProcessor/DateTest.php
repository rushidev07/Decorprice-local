<?php
namespace Aheadworks\RewardPoints\Test\Unit\Controller\Adminhtml\Earning\Rules\PostDataProcessor;

use Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\PostDataProcessor\Date;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Stdlib\DateTime\Filter\Date as DateFilter;

/**
 * Test for \Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\PostDataProcessor\Date
 */
class DateTest extends TestCase
{
    /**
     * @var Date
     */
    private $processor;

    /**
     * @var DateFilter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateFilterMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->dateFilterMock = $this->createMock(DateFilter::class);

        $this->processor = $objectManager->getObject(
            Date::class,
            [
                'dateFilter' => $this->dateFilterMock
            ]
        );
    }

    /**
     * Test process method
     *
     * @param array $data
     * @param array $result
     * @dataProvider processDataProvider
     */
    public function testProcess($data, $result)
    {
        $map = [
            ['10/01/2018', '2018-10-01'],
            ['10/21/2018', '2018-10-21']
        ];

        $this->dateFilterMock->expects($this->any())
            ->method('filter')
            ->will($this->returnValueMap($map));

        $this->assertSame($result, $this->processor->process($data));
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            [
                'data' => [],
                'result' => [
                    EarnRuleInterface::FROM_DATE => null,
                    EarnRuleInterface::TO_DATE => null,
                ]
            ],
            [
                'data' => [
                    EarnRuleInterface::FROM_DATE => '',
                    EarnRuleInterface::TO_DATE => '',
                ],
                'result' => [
                    EarnRuleInterface::FROM_DATE => null,
                    EarnRuleInterface::TO_DATE => null,
                ]
            ],
            [
                'data' => [
                    EarnRuleInterface::FROM_DATE => '10/01/2018',
                    EarnRuleInterface::TO_DATE => '10/21/2018',
                ],
                'result' => [
                    EarnRuleInterface::FROM_DATE => '2018-10-01',
                    EarnRuleInterface::TO_DATE => '2018-10-21',
                ]
            ],
        ];
    }
}
