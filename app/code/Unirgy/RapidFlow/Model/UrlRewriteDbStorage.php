<?php

namespace Unirgy\RapidFlow\Model;

$hlp = \Magento\Framework\App\ObjectManager::getInstance()->get('\Unirgy\RapidFlow\Helper\Data');

if ($hlp->compareMageVer('2.3.4',null,'<')) {
    class UrlRewriteDbStorage extends UrlRewriteDbStorageBase
    {
        public function deleteByData(array $data)
        {
            $table = $this->resource->getTableName(self::TABLE_NAME);
            $where = [];
            foreach ($data as $column => $value) {
                $columnSql = $this->connection->quoteIdentifier($column);
                $valueSql = $this->connection->quote($value);
                $where[] = sprintf('%s IN (%s)', $columnSql, $valueSql);
            }
            $query = sprintf("DELETE FROM %s WHERE %s",
                $table, implode(' and ', $where)
            );
            $this->connection->query($query);
            /*
            $this->connection->query(
                $this->prepareSelect($data)->deleteFromSelect($this->resource->getTableName(self::TABLE_NAME))
            );
            */
        }

        protected function insertMultiple($data)
        {
            $newData = [];
            foreach ($data as $d) {
                if (!empty($d['request_path']) || !in_array($d['entity_type'], ['category', 'product'])) {
                    $newData[] = $d;
                }
            }
            return parent::insertMultiple($newData);
        }
        protected function doReplace(array $urls)
        {
            try {
                return parent::doReplace($urls);
            } catch (\Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException $e) {
                $this->uProcessDuplicates($urls, $e->getUrls());
                return parent::doReplace($urls);
            }
        }
    }
} else {
    class UrlRewriteDbStorage extends UrlRewriteDbStorageBase
    {
        public function deleteByData(array $data)
        {
            $table = $this->resource->getTableName(self::TABLE_NAME);
            $where = [];
            foreach ($data as $column => $value) {
                $columnSql = $this->connection->quoteIdentifier($column);
                $valueSql = $this->connection->quote($value);
                $where[] = sprintf('%s IN (%s)', $columnSql, $valueSql);
            }
            $query = sprintf("DELETE FROM %s WHERE %s",
                $table, implode(' and ', $where)
            );
            $this->connection->query($query);
            /*
            $this->connection->query(
                $this->prepareSelect($data)->deleteFromSelect($this->resource->getTableName(self::TABLE_NAME))
            );
            */
        }
        protected function insertMultiple($data): void
        {
            $newData = [];
            foreach ($data as $d) {
                if (!empty($d['request_path']) || !in_array($d['entity_type'], ['category','product'])) {
                    $newData[] = $d;
                }
            }
            parent::insertMultiple($newData);
        }
        protected function doReplace(array $urls): array
        {
            try {
                return parent::doReplace($urls);
            } catch (\Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException $e) {
                $this->uProcessDuplicates($urls, $e->getUrls());
                return parent::doReplace($urls);
            }
        }
    }
}
