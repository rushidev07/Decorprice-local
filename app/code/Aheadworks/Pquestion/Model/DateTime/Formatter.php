<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Model\DateTime;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Formatter
 *
 * @package Aheadworks\Pquestion\Model\DateTime
 */
class Formatter
{
    /**
     * @var TimezoneInterface
     */
    private $dateTime;

    /**
     * @param TimezoneInterface $dateTime
     */
    public function __construct(
        TimezoneInterface $dateTime
    ) {
        $this->dateTime = $dateTime;
    }

    /**
     * Get date and time in db format
     *
     * @param string $date
     * @param string $locale
     * @return string
     * @throws LocalizedException
     * @throws \Zend_Date_Exception
     * @throws \Zend_Locale_Exception
     */
    public function getDateTimeInDbFormat($date, $locale)
    {
        $locale = new \Zend_Locale($locale);
        $dateObject = new \Zend_Date(null, null, $locale);
        $format = $locale->getTranslation(null, 'datetime', $locale);
        $dateObject->setDate($date, $format);
        $dateObject->setTime($date, $format);

        return $this->dateTime->convertConfigTimeToUtc($dateObject->toString('YYYY-MM-dd H:m:s'));
    }
}
