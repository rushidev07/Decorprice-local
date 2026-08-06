<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Question extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $_filterManager;

    /**
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Filter\FilterManager $filterManager,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_filterManager = $filterManager;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        foreach ($dataSource['data']['items'] as & $item) {
            $item['content'] = $this->_getLink($item['entity_id'], $item['content']);
        }

        return $dataSource;
    }

    /**
     * @param mixed $entityId
     * @param mixed $content
     * @return string
     */
    protected function _getLink($entityId, $content)
    {
        $content = $this->_filterManager->truncate(
            htmlspecialchars($content, ENT_COMPAT, 'UTF-8', false),
            ['length' => 80, 'etc' => '...', 'remainder' => '', 'breakWords' => true]
        );
        $url = $this->context->getUrl('productquestion/question/edit', ['id' => $entityId]);
        return '<a href="' . $url . '" onclick="setLocation(this.href)">' . $content . '</a>';
    }
}
