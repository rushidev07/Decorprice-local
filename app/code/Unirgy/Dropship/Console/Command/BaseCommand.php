<?php

namespace Unirgy\Dropship\Console\Command;

use Magento\Framework\App\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Unirgy\Dropship\Helper\Data as DropshipHelper;

class BaseCommand extends Command
{
    /**
     * @return DropshipHelper
     */
    protected function _hlp()
    {
        return ObjectManager::getInstance()->get(DropshipHelper::class);
    }

}
