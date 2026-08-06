<?php
namespace Aheadworks\RewardPoints\Test\Unit\Controller\Adminhtml\Earning\Rules\PostDataProcessor;

use Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\PostDataProcessor\Website;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Website as WebsiteModel;

/**
 * Test for \Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\PostDataProcessor\Website
 */
class WebsiteTest extends TestCase
{
    /**
     * Value of current website id
     */
    const CURRENT_WEBSITE_ID = 123;

    /**
     * @var Website
     */
    private $processor;

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

        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);

        $this->processor = $objectManager->getObject(
            Website::class,
            [
                'storeManager' => $this->storeManagerMock,
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
        $websiteMock = $this->createMock(WebsiteModel::class);
        $websiteMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::CURRENT_WEBSITE_ID);

        $this->storeManagerMock->expects($this->any())
            ->method('getWebsite')
            ->willReturn($websiteMock);

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
                    EarnRuleInterface::WEBSITE_IDS => [self::CURRENT_WEBSITE_ID]
                ]
            ],
            [
                'data' => [
                    EarnRuleInterface::WEBSITE_IDS => []
                ],
                'result' => [
                    EarnRuleInterface::WEBSITE_IDS => [self::CURRENT_WEBSITE_ID]
                ]
            ],
            [
                'data' => [
                    EarnRuleInterface::WEBSITE_IDS => ['10']
                ],
                'result' => [
                    EarnRuleInterface::WEBSITE_IDS => [10]
                ]
            ],
            [
                'data' => [
                    EarnRuleInterface::WEBSITE_IDS => ['1', 2, '3']
                ],
                'result' => [
                    EarnRuleInterface::WEBSITE_IDS => [1, 2, 3]
                ]
            ],
        ];
    }
}
