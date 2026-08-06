<?php
namespace Aheadworks\RewardPoints\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection as FrameworkAbstractCollection;

/**
 * Class AbstractCollection
 * @package Aheadworks\RewardPoints\Model\ResourceModel
 * @codeCoverageIgnore
 */
class AbstractCollection extends FrameworkAbstractCollection
{
    /**
     * @var string[]
     */
    private $linkageTableNames = [];

    /**
     * Attach relation table data to collection items
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $linkageColumnName
     * @param string|array $columnNameRelationTable
     * @param string $fieldName
     * @param array $conditions
     * @param array $order
     * @param bool $setDataAsArray
     * @param array $default
     * @return void
     */
    public function attachRelationTable(
        $tableName,
        $columnName,
        $linkageColumnName,
        $columnNameRelationTable,
        $fieldName,
        $conditions = [],
        $order = [],
        $setDataAsArray = false,
        $default = []
    ) {
        $ids = $this->getColumnValues($columnName);
        if (count($ids)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from([$tableName . '_table' => $this->getTable($tableName)])
                ->where($tableName . '_table.' . $linkageColumnName . ' IN (?)', $ids);

            foreach ($conditions as $condition) {
                $select->where(
                    $tableName . '_table.' . $condition['field'] . ' ' . $condition['condition'] . ' (?)',
                    $condition['value']
                );
            }
            if (!empty($order)) {
                $select->order($tableName . '_table.' . $order['field'] . ' ' . $order['direction']);
            }

            $relationTableData = $connection->fetchAll($select);

            /** @var \Magento\Framework\DataObject $item */
            foreach ($this as $item) {
                $result = [];
                $id = $item->getData($columnName);
                foreach ($relationTableData as $dataRow) {
                    $result = $this->processDataRow(
                        $dataRow,
                        $linkageColumnName,
                        $id,
                        $columnNameRelationTable,
                        $result
                    );
                }
                if (!empty($result)) {
                    $fieldData = $setDataAsArray ? $result : array_shift($result);
                    $item->setData($fieldName, $fieldData);
                } elseif (!empty($default) && $default[$linkageColumnName] == $id) {
                    $item->setData($fieldName, $default[$columnNameRelationTable]);
                }
            }
        }
    }

    /**
     * Join to linkage table if filter is applied
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $linkageColumnName
     * @param string $columnFilter
     * @param string $fieldName
     * @return $this
     */
    public function joinLinkageTable(
        $tableName,
        $columnName,
        $linkageColumnName,
        $columnFilter,
        $fieldName
    ) {
        if ($this->getFilter($columnFilter)) {
            $linkageTableName = $columnFilter . '_table';
            if (in_array($linkageTableName, $this->linkageTableNames)) {
                $this->addFilterToMap($columnFilter, $columnFilter . '_table.' . $fieldName);
                return $this;
            }

            $this->linkageTableNames[] = $linkageTableName;
            $select = $this->getSelect();
            $select->joinLeft(
                [$linkageTableName => $this->getTable($tableName)],
                'main_table.' . $columnName . ' = ' . $linkageTableName . '.' . $linkageColumnName,
                []
            );

            $this->addFilterToMap($columnFilter, $columnFilter . '_table.' . $fieldName);
        }

        return $this;
    }

    /**
     * Process data row to attach
     *
     * @param array $dataRow
     * @param string $linkageColumnName
     * @param int $id
     * @param string|array $columnNameRelationTable
     * @param array $result
     * @return array
     */
    private function processDataRow(
        $dataRow,
        $linkageColumnName,
        $id,
        $columnNameRelationTable,
        $result
    ) {
        if ($dataRow[$linkageColumnName] == $id) {
            if (is_array($columnNameRelationTable)) {
                $fieldValue = [];
                foreach ($columnNameRelationTable as $columnNameRelation) {
                    $fieldValue[$columnNameRelation] = $dataRow[$columnNameRelation];
                }
                $result[] = $fieldValue;
            } else {
                $result[] = $dataRow[$columnNameRelationTable];
            }
        }
        return $result;
    }
}
