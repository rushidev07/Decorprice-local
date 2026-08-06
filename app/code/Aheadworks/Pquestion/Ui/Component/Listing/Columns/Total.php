<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Ui\Component\Listing\Columns;

class Total extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        foreach ($dataSource['data']['items'] as & $item) {
            if ($item[$this->getName()] > 0) {
                continue;
            }
            $item[$this->getName()] = '<p style="color: red">' . $item[$this->getName()] . '</p>';
        }

        return $dataSource;
    }
}
