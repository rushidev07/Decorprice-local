<?php

declare(strict_types=1);
/**
 * Attribute
 *
 * @copyright Copyright © 2020 Firebear Studio. All rights reserved.
 * @author    fbeardev@gmail.com
 */

namespace Firebear\CustomImportExportAttributeImport\Model\Import;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\ImportExport\Model\Import;
use Zend_Validate_Exception;

class Attribute extends \Firebear\ImportExport\Model\Import\Attribute
{
    /**
     * Import Data Rows
     *
     * @return boolean
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    protected function _importData()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNumber => $rowData) {
                /* validate data */
                if (!$rowData || !$this->validateRow($rowData, $rowNumber)) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNumber);
                    continue;
                }
                /* behavior selector */
                switch ($this->getBehavior()) {
                    case Import::BEHAVIOR_DELETE:
                        $this->_deleteAttribute($rowData);
                        break;
                    case Import::BEHAVIOR_REPLACE:
                        $this->_saveAttribute(
                            $this->_prepareDataForReplace($rowData)
                        );
                        break;
                    case Import::getDefaultBehavior():
                    case Import::BEHAVIOR_ADD_UPDATE:
                        $this->_saveAttribute(
                            $this->_prepareDataForUpdate($rowData)
                        );
                        break;
                }
            }
        }
        return true;
    }
}
