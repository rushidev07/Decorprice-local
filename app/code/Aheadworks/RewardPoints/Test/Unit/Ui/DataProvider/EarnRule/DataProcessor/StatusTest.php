<?php
namespace Aheadworks\RewardPoints\Test\Unit\Ui\DataProvider\EarnRule\DataProcessor;

use Aheadworks\RewardPoints\Ui\DataProvider\EarnRule\DataProcessor\Status;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\RewardPoints\Ui\DataProvider\EarnRule\DataProcessor\Status
 */
class StatusTest extends TestCase
{
    /**
     * @var Status
     */
    private $processor;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->processor = $objectManager->getObject(Status::class, []);
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
                'result' => []
            ],
            [
                'data' => [EarnRuleInterface::STATUS => 0],
                'result' => [EarnRuleInterface::STATUS => '0']
            ],
            [
                'data' => [EarnRuleInterface::STATUS => 1],
                'result' => [EarnRuleInterface::STATUS => '1']
            ],
            [
                'data' => [EarnRuleInterface::STATUS => '0'],
                'result' => [EarnRuleInterface::STATUS => '0']
            ],
            [
                'data' => [EarnRuleInterface::STATUS => '1'],
                'result' => [EarnRuleInterface::STATUS => '1']
            ],
        ];
    }
}
