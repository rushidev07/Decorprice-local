<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Ui\Component\Listing\Columns\Product;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class ProductActions
 */
class Actions extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->productFactory = $productFactory;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $product = $this->productFactory->create();
                $product->load($item['entity_id']);
                $item[$this->getData('name')] = [
                    'view_frontend' => [
                        'href' => $product->getProductUrl(),
                        'label' => __('View Product'),
                        'hidden' => false,
                    ],
                    'view_adminhtml' => [
                        'href' => $this->context->getUrl(
                            'catalog/product/edit',
                            ['id' => $item['entity_id']]
                        ),
                        'label' => __('Edit Product'),
                        'hidden' => false,
                    ],
                ];
            }
        }

        return $dataSource;
    }
}
