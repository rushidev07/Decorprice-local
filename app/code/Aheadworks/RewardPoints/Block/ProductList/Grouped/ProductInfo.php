<?php
namespace Aheadworks\RewardPoints\Block\ProductList\Grouped;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Pricing\Render;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\GroupedProduct\Block\Product\View\Type\Grouped;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class ProductInfo
 *
 * @package Aheadworks\RewardPoints\Block\ProductList\Grouped
 */
class ProductInfo extends Template
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_RewardPoints::product/grouped/earning.phtml';

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param LayoutInterface $layout
     * @param Logger $logger
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        LayoutInterface $layout,
        Logger $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->layout = $layout;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    /**
     * Get blocks of grouped child products
     *
     * @return string
     */
    public function getGroupedInfoBlocks()
    {
        if (!$this->_request->isAjax()) {
            return $this->getChildHtml('product.info.grouped');
        }

        if (!$product = $this->getProduct()) {
            return '';
        }

        $blockInstance = $this->layout->createBlock(
            Grouped::class,
            '',
            [
                'data' => ['product' => $product]
            ]
        );
        if (!is_object($blockInstance)) {
            return '';
        }
        $this->initPriceRender();
        $blockInstance->setNameInLayout('product.info.grouped');
        $blockInstance->setTemplate('Magento_GroupedProduct::product/view/type/grouped.phtml');
        return $blockInstance->toHtml();
    }

    /**
     * Retrieve product
     *
     * @return ProductInterface
     */
    private function getProduct()
    {
        $product = null;
        $productId = $this->_request->getParam('product_id', null)
            ? $this->_request->getParam('product_id')
            : $this->_request->getParam('id');

        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $exception) {
            $this->logger->critical($exception->getMessage());
        }

        return $product;
    }

    /**
     * Create product price block if not exist
     *
     * @return void
     */
    private function initPriceRender()
    {
        $priceRender = $this->layout->getBlock('product.price.render.default');
        if (!$priceRender) {
            $this->layout->createBlock(
                Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }
    }
}
