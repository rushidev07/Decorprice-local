<?php
namespace Aheadworks\RewardPoints\Model\Calculator\Earning;

use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Class EarnItem
 * @package Aheadworks\RewardPoints\Model\Calculator\Earning
 * @codeCoverageIgnore
 */
class EarnItem extends AbstractSimpleObject implements EarnItemInterface
{
    /**#@+
     * Constants for keys.
     */
    const PRODUCT_ID    = 'product_id';
    const BASE_AMOUNT   = 'base_amount';
    const QTY           = 'qty';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function getProductId()
    {
        return $this->_get(self::PRODUCT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseAmount()
    {
        return $this->_get(self::BASE_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseAmount($baseAmount)
    {
        return $this->setData(self::BASE_AMOUNT, $baseAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getQty()
    {
        return $this->_get(self::QTY);
    }

    /**
     * {@inheritdoc}
     */
    public function setQty($qty)
    {
        return $this->setData(self::QTY, $qty);
    }
}
