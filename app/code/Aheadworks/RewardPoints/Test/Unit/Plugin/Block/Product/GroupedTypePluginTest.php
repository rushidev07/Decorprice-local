<?php
namespace Aheadworks\RewardPoints\Test\Unit\Plugin\Block\Product;

use Aheadworks\RewardPoints\Plugin\Block\Product\GroupedTypePlugin;
use Aheadworks\RewardPoints\Block\ProductList\Grouped\ProductText;
use Aheadworks\RewardPoints\Block\ProductList\Grouped\ProductTextFactory;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Model\Product;
use Magento\GroupedProduct\Block\Product\View\Type\Grouped as GroupedTypeBlock;

/**
 * Test for \Aheadworks\RewardPoints\Plugin\Block\Product\GroupedTypePlugin
 */
class GroupedTypePluginTest extends TestCase
{
    /**
     * @var GroupedTypePlugin
     */
    private $plugin;

    /**
     * @var ProductTextFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productTextFactoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->productTextFactoryMock = $this->createMock(ProductTextFactory::class);

        $this->plugin = $objectManager->getObject(
            GroupedTypePlugin::class,
            [
                'productTextFactory' => $this->productTextFactoryMock,
            ]
        );
    }

    /**
     * Test aroundGetProductPrice method
     */
    public function testAroundGetProductPrice()
    {
        $blockMock = $this->createMock(GroupedTypeBlock::class);
        $productMock = $this->createMock(Product::class);
        $nativeBlockHtml = '<div>HTML Content</div>';
        $productTextHtml = '<div>Product Text HTML Content</div>';

        $proceed = function ($query) use ($productMock, $nativeBlockHtml) {
            $this->assertEquals($productMock, $query);
            return $nativeBlockHtml;
        };

        $productTextMock = $this->createMock(ProductText::class);
        $this->productTextFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => ['product' => $productMock]])
            ->willReturn($productTextMock);

        $productTextMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($productTextHtml);

        $this->assertEquals(
            $nativeBlockHtml . $productTextHtml,
            $this->plugin->aroundGetProductPrice($blockMock, $proceed, $productMock)
        );
    }
}
