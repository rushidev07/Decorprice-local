<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Action;

use Aheadworks\RewardPoints\Model\Action\AttributeProcessor;
use Aheadworks\RewardPoints\Api\Data\ActionInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Api\AttributeInterface;

/**
 * Test for \Aheadworks\RewardPoints\Model\Action\AttributeProcessor
 */
class AttributeProcessorTest extends TestCase
{
    /**
     * @var AttributeProcessor
     */
    private $attributeProcessor;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->attributeProcessor = $objectManager->getObject(AttributeProcessor::class, []);
    }

    /**
     * Test getAttributeValueByCode method
     *
     * @param AttributeInterface[]||\PHPUnit_Framework_MockObject_MockObject[] $attributes
     * @param string $code
     * @param mixed $result
     * @dataProvider getAttributeValueByCodeDataProvider
     * @throws \Exception
     */
    public function testGetAttributeValueByCode($attributes, $code, $result)
    {
        $this->assertEquals($result, $this->attributeProcessor->getAttributeValueByCode($code, $attributes));
    }

    /**
     * @return array
     */
    public function getAttributeValueByCodeDataProvider()
    {
        $attributes = [
            $this->getAttributeMock('attribute_one', 'value1'),
            $this->getAttributeMock('attribute_two', 123)
        ];
        return [
            [
                'attributes' => $attributes,
                'code' => 'not_exist',
                'result' => null
            ],
            [
                'attributes' => $attributes,
                'code' => 'attribute_one',
                'result' => 'value1'
            ],
            [
                'attributes' => $attributes,
                'code' => 'attribute_two',
                'result' => 123
            ],
        ];
    }

    /**
     * Get attribute mock
     *
     * @param int $code
     * @param mixed $value
     * @return AttributeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAttributeMock($code, $value)
    {
        $attributeMock = $this->createMock(AttributeInterface::class);
        $attributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($code);
        $attributeMock->expects($this->any())
            ->method('getValue')
            ->willReturn($value);

        return $attributeMock;
    }
}
