<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\EarnRule\ProductMatcher\ProductResolver;

use Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\ProductResolver\Pool;
use Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\ProductResolverInterface;
use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Exception\ConfigurationMismatchException;

/**
 * Test for \Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\ProductResolver\Pool
 */
class PoolTest extends TestCase
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->pool = $objectManager->getObject(Pool::class, []);
    }

    /**
     * Test getResolvers method
     *
     * @param ItemProcessorInterface[]|\PHPUnit_Framework_MockObject_MockObject[] $resolvers
     * @dataProvider getResolversDataProvider
     * @throws \ReflectionException
     */
    public function testGetResolvers($resolvers)
    {
        $this->setProperty('resolvers', $resolvers);

        $this->assertEquals($resolvers, $this->pool->getResolvers());
    }

    /**
     * @return array
     */
    public function getResolversDataProvider()
    {
        return [
            [
                'resolvers' => []
            ],
            [
                'resolvers' => [$this->createMock(ProductResolverInterface::class)]
            ],
            [
                'resolvers' => [
                    $this->createMock(ProductResolverInterface::class),
                    $this->createMock(ProductResolverInterface::class)
                ]
            ]
        ];
    }

    /**
     * Test getResolverByCode method
     *
     * @param ProductResolverInterface[] $resolvers
     * @param string $code
     * @param ProductResolverInterface|\Exception $result
     * @throws \ReflectionException
     * @dataProvider getResolverByCodeDataProvider
     */
    public function testGetProcessorByCode($resolvers, $code, $result)
    {
        $this->setProperty('resolvers', $resolvers);

        if ($result instanceof \Exception) {
            try {
                $this->pool->getResolverByCode($code);
            } catch (\Exception $e) {
                $this->assertEquals($result, $e);
            }
        } else {
            $this->assertSame($result, $this->pool->getResolverByCode($code));
        }
    }

    /**
     * @return array
     */
    public function getResolverByCodeDataProvider()
    {
        $resolverDefaultMock = $this->createMock(ProductResolverInterface::class);
        $resolverOneMock = $this->createMock(ProductResolverInterface::class);
        $badResolver = $this->createMock(DataObject::class);
        $resolvers = [
            'default' => $resolverDefaultMock,
            'resolver_one' => $resolverOneMock,
            'resolver_bad' => $badResolver
        ];
        return [
            [
                'resolvers' => $resolvers,
                'code' => 'resolver_one',
                'result' => $resolverOneMock
            ],
            [
                'resolvers' => $resolvers,
                'code' => 'unknown_code',
                'result' => $resolverDefaultMock
            ],
            [
                'resolvers' => $resolvers,
                'code' => 'resolver_bad',
                'result' => new ConfigurationMismatchException(
                    __('Product resolver must implements %1', ProductResolverInterface::class)
                )
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
        $class = new \ReflectionClass($this->pool);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($this->pool, $value);

        return $this;
    }
}
