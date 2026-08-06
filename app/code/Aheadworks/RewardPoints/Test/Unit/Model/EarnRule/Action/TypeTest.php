<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\EarnRule\Action;

use Aheadworks\RewardPoints\Model\EarnRule\Action\ProcessorInterface;
use Aheadworks\RewardPoints\Model\EarnRule\Action\Type;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\RewardPoints\Model\EarnRule\Action\Type
 */
class TypeTest extends TestCase
{
    /**
     * @var Type
     */
    private $type;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->type = $objectManager->getObject(Type::class, []);
    }

    /**
     * Test getTitle method
     */
    public function testGetTitle()
    {
        $title = 'Test Title';
        $this->setProperty('title', $title);

        $this->assertSame($title, $this->type->getTitle());
    }

    /**
     * Test getProcessor method
     */
    public function testGetProcessor()
    {
        $processorMock = $this->createMock(ProcessorInterface::class);
        $this->setProperty('processor', $processorMock);

        $this->assertSame($processorMock, $this->type->getProcessor());
    }

    /**
     * Test getAttributeCodes method
     *
     * @param $attributeCodes
     * @dataProvider getAttributeCodesDataProvider
     * @throws \ReflectionException
     */
    public function testGetAttributeCodes($attributeCodes)
    {
        $this->setProperty('attributeCodes', $attributeCodes);

        $this->assertEquals($attributeCodes, $this->type->getAttributeCodes());
    }

    /**
     * @return array
     */
    public function getAttributeCodesDataProvider()
    {
        return [
            ['attributeCodes' => []],
            ['attributeCodes' => ['sample_code_one']],
            ['attributeCodes' => ['sample_code_one', 'sample_code_two']]
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
        $class = new \ReflectionClass($this->type);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($this->type, $value);

        return $this;
    }
}
