<?php

namespace Unirgy\Dropship\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Input\InputArgument;
use Unirgy\Dropship\Helper\Data as DropshipHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LabelBatch extends BaseCommand
{
    protected function configure()
    {
        $this->setName('unirgy:dropship:label-batch')
            ->addArgument('batch_id', InputArgument::REQUIRED, 'Batch Id')
            ->setDescription('Batch generate labels');

        parent::configure();
    }
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Unirgy\Dropship\Model\Label\Batch $labelBatch */
        $labelBatch = $this->_hlp()->createObj('Unirgy\Dropship\Model\Label\Batch');
        try {
            $batchId = $input->getArgument('batch_id');
            if (!$batchId) {
                throw new \Exception(__('Missing batch_id argument'));
            }
            $labelBatch->load($batchId);
            if (!$labelBatch->getId()) {
                throw new \Exception(__('Label batch not found'));
            }
            $labelBatch->processPos();
            $output->writeln(__('Batch generate labels finished'));
        } catch (\Exception $e) {
            if ($labelBatch->getId()) {
                $labelBatch->updateErrorMessage((string)$e->getMessage());
            }
            throw $e;
        }
    }
}
