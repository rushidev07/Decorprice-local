<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

use Magento\Framework\ObjectManagerInterface;

try {
//    if you move this file, adjust bootstrap.php path
    require __DIR__ . '/../../../../../app/bootstrap.php';
} catch (\Exception $e) {
    echo <<<HTML
{$e->getMessage()}
</div>
HTML;
    exit(1);
}

error_reporting((E_ALL | E_STRICT) ^ E_DEPRECATED);
ini_set('display_errors', 1);

$params = $_SERVER;
$params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = 'admin'; // change this to appropriate store if needed.
$params[\Magento\Store\Model\Store::CUSTOM_ENTRY_POINT_PARAM] = true;
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params); // bootstrap

/** @var \Magento\Framework\App\Http $app */
$app = $bootstrap->createApplication('Magento\Framework\App\Http');

// configure environment
$om = $bootstrap->getObjectManager();
$areaList = $om->get('Magento\Framework\App\AreaList');
//$areaCode = $areaList->getCodeByFrontName('admin');
$areaCode = 'adminhtml';

/** @var \Magento\Framework\App\State $state */
$state = $om->get('Magento\Framework\App\State');
$state->setAreaCode($areaCode);

/** @var \Magento\Framework\ObjectManager\ConfigLoaderInterface $configLoader */
$configLoader = $om->get('Magento\Framework\ObjectManager\ConfigLoaderInterface');

$omCfgLoaded = $configLoader->load($areaCode);
if ($configLoader instanceof \Magento\Framework\App\ObjectManager\ConfigLoader\Compiled) {
    $pfsDiVal = @$omCfgLoaded['arguments']['Magento\Catalog\Model\Indexer\Product\Flat\State'];
    $pfsDiVal = @unserialize($pfsDiVal);
    if (!is_array($pfsDiVal)) {
        $pfsDiVal = [];
    }
    $pfsDiVal['isAvailable'] = false;
    $omCfgLoaded['arguments']['Magento\Catalog\Model\Indexer\Product\Flat\State'] = serialize($pfsDiVal);
} else {
    $omCfgLoaded['Magento\Catalog\Model\Indexer\Product\Flat\State']['arguments']['isAvailable'] = false;
}

$om->configure($omCfgLoaded);

function testRfEavExport(ObjectManagerInterface $om)
{
    runRfProfile($om, 'eav-export');
}

function testRfCatExport(ObjectManagerInterface $om)
{
    runRfProfile($om, 'categories-export');
}

function testRfExtraExport(ObjectManagerInterface $om)
{
    runRfProfile($om, 'extras-export');
}

/**
 * Function to
 * @param ObjectManagerInterface $om
 * @param string|int $profile
 * @throws Exception
 */
function runRfProfile(ObjectManagerInterface $om, $profile)
{
    /** @var \Unirgy\RapidFlow\Helper\Data $helper */
    $helper = $om->get('\Unirgy\RapidFlow\Helper\Data');
    $helper->run($profile);
}

//runRfProfile($om, 5);
//testRfEavExport($om);
//
//testRfCatExport($om);
//
//testRfExtraExport($om);

// _ -u _www /usr/bin/php -d memory_limit=512M "<Magento root path>/app/code/Unirgy/RapidFlow/misc/rf.php"
