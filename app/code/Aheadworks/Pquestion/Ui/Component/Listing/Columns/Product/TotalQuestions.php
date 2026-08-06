<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Ui\Component\Listing\Columns\Product;

class TotalQuestions extends \Aheadworks\Pquestion\Ui\Component\Listing\Columns\Total
{
    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        foreach ($dataSource['data']['items'] as & $item) {
            $item[$this->getName()] = $item['shared_questions'] + $item['product_only_questions'];
        }
        return parent::prepareDataSource($dataSource);
    }
}
