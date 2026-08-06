<?php
use Magento\Framework\App\Bootstrap;

require 'app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();

/** @var \Ahy\Automation\Cron\DailyProductCleanup $cron */
$cron = $objectManager->create(\Ahy\Automation\Cron\DailyProductCleanup::class);
$cron->execute();

echo "Daily cleanup executed!\n";
