<?php
namespace Aheadworks\RewardPoints\Model\Import\Exception;

use Aheadworks\RewardPoints\Api\Exception\ImportValidatorExceptionInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ImportValidatorException
 *
 * @package Aheadworks\RewardPoints\Model\Import\Exception
 */
class ImportValidatorException extends LocalizedException implements ImportValidatorExceptionInterface
{
}
