<?php
namespace Ahy\Automation\Cron;

use Ahy\Automation\Service\VisibilityManager;
use Ahy\Automation\Logger\Logger;

class VisibilityScheduler
{
    protected $visibilityManager;
    protected $logger;

    public function __construct(
        VisibilityManager $visibilityManager,
        Logger $logger
    ) {
        $this->visibilityManager = $visibilityManager;
        $this->logger = $logger;
    }

    public function execute()
    {
        try {

            // Disabled on 2nd March 2026 as per client request:
            // $updatedCount = $this->visibilityManager->process();

            $this->logger->info("Ahy_Automation Cron executed. Products updated: {$updatedCount}");
        } catch (\Exception $e) {
            $this->logger->error("Ahy_Automation Cron error: " . $e->getMessage());
        }
    }
}
