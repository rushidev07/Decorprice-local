<?php
namespace Ahy\Automation\Console\Command;

use Ahy\Automation\Service\VisibilityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Output\OutputInterface;

class VisibilityCommand extends Command
{
    protected $visibilityManager;

    public function __construct(VisibilityManager $visibilityManager)
    {
        $this->visibilityManager = $visibilityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('ahy:automation:visibility')
            ->setDescription('Run Visibility Automation with optional simulation')
            ->addOption('simulate', null, InputOption::VALUE_OPTIONAL, 'Simulate datetime in EST (Y-m-d H:i)')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run mode, do not update');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $simulate = $input->getOption('simulate');
        $dryRun = $input->getOption('dry-run');

        // Get desired visibility first
        $desiredVisibility = $this->visibilityManager->getDesiredVisibility($simulate);

        $count = $this->visibilityManager->process($simulate, $dryRun);

        $visibilityLabel = $desiredVisibility === 1 ? 'Not Visible Individually' : 'Catalog, Search';

        $output->writeln("Ahy Automation: Products affected = $count");
        $output->writeln("Visibility set to: {$visibilityLabel}");
        
        return Cli::RETURN_SUCCESS;
    }
}
