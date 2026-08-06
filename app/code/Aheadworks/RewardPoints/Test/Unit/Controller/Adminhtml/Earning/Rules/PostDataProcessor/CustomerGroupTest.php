<?php
namespace Aheadworks\RewardPoints\Test\Unit\Controller\Adminhtml\Earning\Rules\PostDataProcessor;

use Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\PostDataProcessor\CustomerGroup;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\PostDataProcessor\CustomerGroup
 */
class CustomerGroupTest extends TestCase
{
    /**
     * @var CustomerGroup
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

        $this->processor = $objectManager->getObject(CustomerGroup::class, []);
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
                'result' => [
                    EarnRuleInterface::CUSTOMER_GROUP_IDS => []
                ]
            ],
            [
                'data' => [
                    EarnRuleInterface::CUSTOMER_GROUP_IDS => []
                ],
                'result' => [
                    EarnRuleInterface::CUSTOMER_GROUP_IDS => []
                ]
            ],
            [
                'data' => [
                    EarnRuleInterface::CUSTOMER_GROUP_IDS => ['10']
                ],
                'result' => [
                    EarnRuleInterface::CUSTOMER_GROUP_IDS => [10]
                ]
            ],
            [
                'data' => [
                    EarnRuleInterface::CUSTOMER_GROUP_IDS => ['1', 2, '3']
                ],
                'result' => [
                    EarnRuleInterface::CUSTOMER_GROUP_IDS => [1, 2, 3]
                ]
            ],
        ];
    }
}
