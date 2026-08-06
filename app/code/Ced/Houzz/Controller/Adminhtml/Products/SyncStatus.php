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
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Houzz\Controller\Adminhtml\Products;

use Magento\Backend\App\Action\Context;

class SyncStatus extends \Magento\Backend\App\Action
{
    const CHUNK_SIZE = 30;
    /**
     * Result Page factory
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * Logger
     * @var $logger \Psr\Log\LoggerInterface
     */
    public $logger;

    /**
     * Data Helper
     * @var $helper
     */
    public $helper;

    /**
     * Product Factory
     * @var $productFactory
     */
    public $productFactory;

    /**
     * DirectoryList
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    public $directoryList;

    /**
     * Session
     * @var \Magento\Backend\Model\Session
     */
    public $session;

    /**
     * Filter
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    public $registry;

    /**
     * Json Factory
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;


    /**
     * SyncStatus constructor.
     * @param Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param  \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Ced\Houzz\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Ced\Houzz\Helper\Data $helper,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->session =  $context->getSession();
        $this->productFactory = $productFactory;
        $this->directoryList = $directoryList;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->filter = $filter;
        $this->registry = $registry;
    }

    /**
     * Execute
     * @return array
     */
    public function execute()
    {
        if(!$this->helper->checkForConfiguration()) {
            $this->messageManager->addNoticeMessage(__('Houzz API not enabled or Invalid. Please check Houzz Configuration.'));
            return $this->_redirect('*/*/index');
        }

        $batchid = $this->getRequest()->getParam('batchid');
        if (isset($batchid)) {
            $resultJson = $this->resultJsonFactory->create();
            $productids = $this->session->getHouzzProducts();
            $response = $this->helper->syncStatus($productids[$batchid]);
            if($response) {
                return $resultJson->setData([
                    'success' => count($productids[$batchid]) . ' Product(s) Synced Successfully',
                ]);
            } else {
                return $resultJson->setData([
                    'error' => 'Product(s) Sync Failed.',
                ]);
            }
        }

        $collection = $this->filter->getCollection($this->_objectManager->create('Magento\Catalog\Model\Product')
            ->getCollection());
        $productids = $collection->getAllIds();

        if (count($productids) == 0) {
            $this->messageManager->addErrorMessage('No Product selected to sync.');
            return $this->_redirect('houzz/products/index');
        }

        if (count($productids) < self::CHUNK_SIZE - 1) {
            $response = $this->helper->syncStatus($productids);
            if($response) {
                $this->messageManager->addSuccessMessage(count($productids) . ' Product(s) Synced Successfully');
            } else {
                $this->messageManager->addErrorMessage('Product(s) Sync Failed.');
            }
            return $this->_redirect('houzz/products/index');
        }

        $productids = array_chunk($productids, self::CHUNK_SIZE);
        $this->registry->register('productids', count($productids));
        $this->session->setHouzzProducts($productids);
        $resultPage = $this->resultPageFactory-> create();
        $resultPage->setActiveMenu('Ced_Houzz::Houzz');
        $resultPage->getConfig()->getTitle()->prepend(__('Sync Products'));
        return $resultPage;

    }

    /**
     * Feeds Sync
     * @return bool
     */
    public function getCsvData()
    {
        $csvData = $this->session->getSyncProductCSV();
        if(!empty($csvData)) {
            return $csvData;
        }
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
            $this->messageManager->addErrorMessage('There has been a Issue of write permission in Directory var.');
            return false;
        }
        $csvObject = $this->_objectManager->create('\Magento\Framework\File\Csv');
        try {
            $data = $csvObject->getData($extractFile);
            unset($data[0]);
            foreach ($data as $key => $value) {
                $csvData[$value[1]] = $value[6];
            }

        } catch (\Exception $e) {
            $this->logger->debug("Houzz Product's Status Sync Failed : syncAllProducts : " . $e->getMessage());
            return false;
        }
        $this->session->setSyncProductCSV($csvData);
        return $csvData;
    }

    /**
     * IsALLowed
     * @return boolean
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_Houzz::Houzz');
    }

}

