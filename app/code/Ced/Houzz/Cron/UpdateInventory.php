<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Houzz
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Houzz\Cron;

class UpdateInventory
{
    /**
     * Logger
     * @var \Psr\Log\LoggerInterface
     */
    public $logger;

    /**
     * OM
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Config Manager
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfigManager;

    /**
     * Config Manager
     * @var \Ced\Houzz\Helper\Data
     */
    public $helper;

    /**
     * DirectoryList
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    public $directoryList;

    /**
     * @var
     */
    public $helperData;

    public $productchange;

    /**
     * UploadProducts constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Ced\Houzz\Helper\HouzzLogger $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Ced\Houzz\Helper\Data $helperData,
        \Ced\Houzz\Model\Productchange $productchange
    ) {
        $this->scopeConfigManager = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->objectManager = $objectManager;
        $this->helper = $this->objectManager->get('Ced\Houzz\Helper\Data');
        $this->logger = $logger;
        $this->directoryList = $directoryList;
        $this->helperData = $helperData;
        $this->productchange = $productchange;
    }


    /**
     * Execute
     * @return bool
     */
    public function execute()
    {
        if($this->helperData->checkForConfiguration()) {
            $scopeConfigManager = $this->objectManager
                ->create('Magento\Framework\App\Config\ScopeConfigInterface');
            $autoSync = $scopeConfigManager->getValue('houzzconfiguration/houzz_cron_settings/inventory_cron');
            if ($autoSync) {
                $collection = $this->productchange->getCollection();
                $type = \Ced\Houzz\Model\Productchange::CRON_TYPE_INVENTORY;
                $collection->addFieldToFilter('cron_type', $type);
                $ids = [];
                foreach ($collection as $pchange){
                    $ids[]= $pchange->getProductId();
                }
                $inventory = $this->objectManager
                    ->get('Ced\Houzz\Helper\Data')
                    ->updateInventoryOnHouzz($ids);

                if($inventory){
                    $this->logger->logger("Houzz Cron" , "Houzz Inventory Cron" , 'Success',' Inventory Cron Success');
                    return true;
                }
                $this->logger->logger("Houzz Cron" , "Houzz Inventory Cron" , 'Failure - '.var_export($inventory,true),' Inventory Cron Failure');
                return false;

            } else {
                $this->logger->logger("Houzz Cron" , "Houzz Inventory Cron" , 'Disabled',' Inventory Cron Failure');
                return false;
                }
            }
            else{
                $this->logger->logger("Houzz Cron" , "Houzz Inventory Cron" , 'Not Processed','Check API details in Houzz Configuration');
                return false;
            }
    }

    /**
     * CSV Data
     * @return array
     */
    public function getCsvData()
    {
        $csvData = array();
        $walmpro = $this->helper->getRequest('v2/getReport?type=item');
        $start = stripos($walmpro, 'ItemReport');
        $end = strpos($walmpro, 'Content-Type: text/html;charset=utf-8');

        $filename = trim(substr($walmpro, $start,$end - $start));

        $filepath = $this->directoryList->getPath('var').'/houzz/ItemReport.zip';
        $extractTo = $this->directoryList->getPath('var').'/houzz/';
        $this->helper->createDir();
        $file = explode('.csv',$filename);
        $filename = $file[0];
        $extractFile = $extractTo.$filename.'.csv';

        $handle = fopen($filepath,'w');
        fwrite($handle, $walmpro);
        fclose($handle);

        $zip = new \ZipArchive();
        if ($zip->open($filepath) === TRUE) {
            $zip->extractTo($extractTo);
            $zip->close();
        } else {
            $this->logger->debug('Zip Extraction Failed');
           return false;
        }

        $csvObject = $this->objectManager->create('\Magento\Framework\File\Csv');
        try {
            $data = $csvObject->getData($extractFile);
            unset($data[0]);
            foreach ($data as $key => $value) {
                $product = $this->objectManager->create('\Magento\Catalog\Model\Product');
                $entityID = $product->getIdBySku($value[1]);
              if($entityID) {
                    $csvData[] = ['id' => $entityID];
                }
            }
        } catch (\Exception $e) {
            $this->logger->debug("Houzz Product's Inventory Cron Failed : getCsvData : " . $e->getMessage());
            return false;
        }
        return $csvData;
    }
}
