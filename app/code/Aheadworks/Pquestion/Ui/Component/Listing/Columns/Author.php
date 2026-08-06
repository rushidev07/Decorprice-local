<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Ui\Component\Listing\Columns;

use Magento\Framework\Escaper;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Author
 * @package Aheadworks\Pquestion\Ui\Component\Listing\Columns
 */
class Author extends Column
{
    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->escaper = $escaper;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        foreach ($dataSource['data']['items'] as & $item) {
            $authorName = $this->escaper->escapeHtml($item['author_name']);
            if ($item['customer_id'] > 0 && $item['customer_isset']) {
                $url = $this->context->getUrl('customer/index/edit', ['id' => $item['customer_id']]);
                $authorName = '<a onclick="setLocation(this.href)" href="' . $url . '">' . $authorName . '</a>';
            }
            $item['author_name'] = $authorName . '<br>' . $item['author_email'];
        }

        return $dataSource;
    }
}
