<?php
namespace Export\Exportprofiles\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\ObjectManagerInterface;



class Profiles extends Command
{
    const NAME = 'profile';
   protected function configure()
   {
       $options = [
			new InputOption(
				self::NAME,
				null,
				InputOption::VALUE_REQUIRED,
				'Profile Id or Profile Name'
			)
		];

       $this->setName('ahy:rapidflow:run')
            ->setDescription('Process Rapidflow Profile by Id or Name')
            ->setDefinition($options);

       parent::configure();
   }

   protected function execute(InputInterface $input, OutputInterface $output)
   {
       $output->writeln("Process Started");
       if ($profile = $input->getOption(self::NAME)) {
			$output->writeln("Processing Profile with ID/Name: " . $profile);
            $this->preProcess($profile);
		} else {
			$output->writeln("Please enter Valid Rapidflow profile ID or Profile Name");
		}
       $output->writeln("Process Finished");
   }

  protected function preProcess($profile){
      $params = $_SERVER;
      $params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = 'admin'; // change this to appropriate store if needed.
      $params[\Magento\Store\Model\Store::CUSTOM_ENTRY_POINT_PARAM] = true;
      $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params); // bootstrap

      /** @var \Magento\Framework\App\Http $app */
      $app = $bootstrap->createApplication('Magento\Framework\App\Http');

      // configure environment
      $om = $bootstrap->getObjectManager();
      $areaList = $om->get('Magento\Framework\App\AreaList');
      $areaCode = 'adminhtml';
      /** @var \Magento\Framework\App\State $state */
      $state = $om->get('Magento\Framework\App\State');
      $state->setAreaCode($areaCode);
      /** @var \Magento\Framework\ObjectManager\ConfigLoaderInterface $configLoader */
      $configLoader = $om->get('Magento\Framework\ObjectManager\ConfigLoaderInterface');

      $omCfgLoaded = $configLoader->load($areaCode);
      if ($configLoader instanceof \Magento\Framework\App\ObjectManager\ConfigLoader\Compiled) {
          $pfsDiVal = @$omCfgLoaded['arguments']['Magento\Catalog\Model\Indexer\Product\Flat\State'];
          if (is_array($pfsDiVal) && isset($pfsDiVal['isAvailable']) && is_array($pfsDiVal['isAvailable'])) {
              $pfsDiVal['isAvailable']['_v_'] = false;
          } elseif (!is_array($pfsDiVal)) {
              $pfsDiVal = @unserialize($pfsDiVal);
              if (!is_array($pfsDiVal)) {
                  $pfsDiVal = [];
              }
              $pfsDiVal['isAvailable'] = false;
              $pfsDiVal = serialize($pfsDiVal);
          }
          $omCfgLoaded['arguments']['Magento\Catalog\Model\Indexer\Product\Flat\State'] = $pfsDiVal;
      } else {
          $omCfgLoaded['Magento\Catalog\Model\Indexer\Product\Flat\State']['arguments']['isAvailable'] = false;
      }

      $om->configure($omCfgLoaded);
      $this->runRfProfile($om, $profile);
  }

  protected function runRfProfile(ObjectManagerInterface $om, $profile)
  {
      /** @var \Unirgy\RapidFlow\Helper\Data $helper */
      $helper = $om->get('\Unirgy\RapidFlow\Helper\Data');
      $helper->run($profile, true, ['keep_session'=>true]);
  }
}
