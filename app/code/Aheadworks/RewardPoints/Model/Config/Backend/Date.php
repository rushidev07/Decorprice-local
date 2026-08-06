<?php
namespace Aheadworks\RewardPoints\Model\Config\Backend;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class Date
 * @package Aheadworks\RewardPoints\Model\Config\Backend
 */
class Date extends Value
{
    /**
     * @inheritDoc
     */
    public function beforeSave()
    {
        $value = $this->getValue();

        if (!empty($value) && !$this->isDate($value)) {
            throw new LocalizedException(__('Lifetime Sales Start Date is invalid. Enter valid date.'));
        }
        return parent::beforeSave();
    }

    /**
     * Check is date
     *
     * @param string $date
     * @return bool
     */
    private function isDate($date)
    {
        $dateNew = date(DateTime::DATE_PHP_FORMAT, strtotime($date));

        return $date === $dateNew;
    }
}
