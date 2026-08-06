<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\StorefrontLabels;

use Aheadworks\RewardPoints\Model\StorefrontLabels\ObjectResolver;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\RewardPoints\Api\Data\StorefrontLabelsInterface;
use Aheadworks\RewardPoints\Api\Data\StorefrontLabelsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class ObjectResolverTest
 *
 * @package Aheadworks\RewardPoints\Test\Unit\Model\StorefrontLabels
 */
class ObjectResolverTest extends TestCase
{
    /**
     * @var ObjectResolver
     */
    private $model;

    /**
     * @var StorefrontLabelsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storefrontLabelsFactoryMock;

    /**
     * @var DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->storefrontLabelsFactoryMock = $this->createMock(
            StorefrontLabelsInterfaceFactory::class
        );
        $this->dataObjectHelperMock = $this->createMock(
            DataObjectHelper::class
        );
        $this->model = $objectManager->getObject(
            ObjectResolver::class,
            [
                'storefrontLabelsFactory' => $this->storefrontLabelsFactoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock
            ]
        );
    }

    /**
     * Test resolve method
     *
     * @param array|StorefrontLabelsInterface $label
     * @param StorefrontLabelsInterface $expected
     * @dataProvider resolveDataProvider
     */
    public function testResolve($label, $expected)
    {
        if (is_array($label)) {
            $this->storefrontLabelsFactoryMock->expects($this->once())
                ->method('create')
                ->willReturn($expected);
            $this->dataObjectHelperMock->expects($this->once())
                ->method('populateWithArray')
                ->with($expected, $label, StorefrontLabelsInterface::class);
        }

        $this->assertEquals($expected, $this->model->resolve($label));
    }

    /**
     * Data provider for resolve
     *
     * @return array
     */
    public function resolveDataProvider()
    {
        $labelMock = $this->getMockForAbstractClass(StorefrontLabelsInterface::class);
        return [
            [
                $labelMock,
                $labelMock
            ],
            [
                [
                    StorefrontLabelsInterface::STORE_ID => 1,
                    StorefrontLabelsInterface::PRODUCT_PROMO_TEXT => 'product promo text',
                    StorefrontLabelsInterface::CATEGORY_PROMO_TEXT => 'category promo text',
                ],
                $labelMock
            ]
        ];
    }
}
