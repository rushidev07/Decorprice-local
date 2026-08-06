<?php
namespace Aheadworks\RewardPoints\Test\Unit\Ui\DataProvider\EarnRule\DataProcessor;

use Aheadworks\RewardPoints\Ui\DataProvider\EarnRule\DataProcessor\Website;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\RewardPoints\Ui\DataProvider\EarnRule\DataProcessor\Website
 */
class WebsiteTest extends TestCase
{
    /**
     * @var Website
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

        $this->processor = $objectManager->getObject(Website::class, []);
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
                'data' => [
                    EarnRuleInterface::WEBSITE_IDS => []
                ],
                'result' => [
                    EarnRuleInterface::WEBSITE_IDS => []
                ]
            ],
            [
                'data' => [
                    EarnRuleInterface::WEBSITE_IDS => [10]
                ],
                'result' => [
                    EarnRuleInterface::WEBSITE_IDS => ['10']
                ]
            ],
            [
                'data' => [
                    EarnRuleInterface::WEBSITE_IDS => [1, '2', 3]
                ],
                'result' => [
                    EarnRuleInterface::WEBSITE_IDS => ['1', '2', '3']
                ]
            ],
        ];
    }
}
