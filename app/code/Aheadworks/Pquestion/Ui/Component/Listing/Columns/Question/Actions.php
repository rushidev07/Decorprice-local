<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Ui\Component\Listing\Columns\Question;

/**
 * Class ProductActions
 */
class Actions extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $approvedValue = \Aheadworks\Pquestion\Model\Source\Question\Status::APPROVED_VALUE;
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item['status'] !=  $approvedValue) {
                    $item[$this->getData('name')] = [
                        'publish' => [
                            'href' => $this->context->getUrl(
                                'productquestion/question/changeStatus',
                                [
                                    'id'        => $item['entity_id'],
                                    'status_id' => $approvedValue
                                ]
                            ),
                            'label' => __('Publish'),
                            'hidden' => false,
                        ],
                    ];
                }
            }
        }

        return $dataSource;
    }
}
