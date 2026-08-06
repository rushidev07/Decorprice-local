<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.3 or later
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */

namespace Magetrend\Eop\Model;

/**
 * Field model
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Field extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    public $resourceConnection;

    /**
     * Db column preffix
     * @var string
     */
    private $columnPrefix = 'subscriber_';

    /**
     * @var string
     */
    private $dbTable = 'newsletter_subscriber';

    /**
     * Field constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $abstractResource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Model\ResourceModel\AbstractResource $abstractResource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->resourceConnection = $resource;
        parent::__construct(
            $context,
            $registry,
            $abstractResource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Define resource model
     *
     * @return void
     */
    //@codingStandardsIgnoreLine
    protected function _construct()
    {
        $this->_init('Magetrend\Eop\Model\ResourceModel\Field');
    }

    /**
     * Save model
     *
     * @return $this
     */
    public function save()
    {
        $this->isValid();
        $connection = $this->getConnection();
        $tableName = $this->_getResource()->getTable($this->dbTable);
        $tableColumns = $connection->describeTable($tableName);
        if (!isset($tableColumns[$this->getColumnName()])) {
            $this->createColumn();
        }
        return parent::save();
    }

    /**
     * Validator
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function isValid()
    {
        $fieldName = $this->getName();
        if (empty($fieldName) || !ctype_alpha($fieldName)) {
            throw new \Magento\Framework\Exception\LocalizedException(__(
                'Bad field name. It must be not empty and only alpha'
            ));
        }

        $fieldType = $this->getType();
        if (empty($fieldType)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Bad field type'));
        }
    }

    /**
     * Returns column name
     *
     * @return string
     */
    public function getColumnName()
    {
        return $this->columnPrefix.$this->getName();
    }

    /**
     * Creates database column for store aditional fields data
     *
     * @return bool
     */
    public function createColumn()
    {
        $db = $this->getConnection();
        $columnName = $this->getColumnName();
        $tableName = $this->_getResource()->getTable($this->dbTable);

        if ($this->getType() == 'checkbox') {
            $db->addColumn($tableName, $columnName, [
                'TYPE'      => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'LENGTH'    => 1,
                'COMMENT'   => 'Additional field'
            ]);
        } else {
            $db->addColumn($tableName, $columnName, [
                'TYPE'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'COMMENT'   => 'Additional field'
            ]);
        }
        return true;
    }

    /**
     * Get connection
     *
     * @return \Magento\Framework\App\ResourceConnection
     * @codeCoverageIgnore
     */
    public function getConnection()
    {
        return $this->resourceConnection->getConnection();
    }
}
