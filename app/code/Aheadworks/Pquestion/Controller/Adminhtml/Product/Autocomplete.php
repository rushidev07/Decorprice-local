<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Controller\Adminhtml\Product;

use Magento\Catalog\Model\Product\Visibility;

/**
 * Class Autocomplete
 *
 * @package Aheadworks\Pquestion\Controller\Adminhtml\Product
 */
class Autocomplete extends \Aheadworks\Pquestion\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private $productCollection;

    /**
     * Autocomplete constructor.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     * @param \Magento\Backend\App\Action\Context                     $context
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection,
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
        $this->productCollection = $productCollection;
    }

    /**
     * Gets the items found on request
     *
     * @return $this
     */
    public function execute()
    {
        $query = $this->_request->getParam('query', '');
        $result = [
            'query'  => $query,
            'suggestions' => [],
        ];
        $this->productCollection
            ->addAttributeToFilter([
                ['attribute' => 'name', 'like' => '%' . $query . '%'],
                ['attribute' => 'sku', 'like' => '%' . $query . '%']
            ])
            ->addAttributeToFilter([
                ['attribute' => 'visibility', 'eq' => Visibility::VISIBILITY_IN_CATALOG],
                ['attribute' => 'visibility', 'eq' => Visibility::VISIBILITY_IN_SEARCH],
                ['attribute' => 'visibility', 'eq' => Visibility::VISIBILITY_BOTH]
            ])
            ->setPageSize(10)
        ;
        foreach ($this->productCollection->load() as $item) {
            $result['suggestions'][] = [
                'value' => $item->getName(),
                'data' => $item->getId()
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        return $resultJson->setData($result);
    }
}
