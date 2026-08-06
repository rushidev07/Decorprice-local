<?php
declare(strict_types=1);

namespace Ahy\Automation\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Handler extends Base
{
    /**
     * Log file name (relative to var/log)
     */
    protected $fileName = '/var/log/product-cleanup.log';

    /**
     * Logging level
     */
    protected $loggerType = Logger::DEBUG;
}
