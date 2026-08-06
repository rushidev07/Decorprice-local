<?php

namespace Ced\Houzz\Controller\Adminhtml\Products;
use Magento\Backend\App\Action\Context;

class SyncStatusBySku extends \Magento\Backend\App\Action {

    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ced\Houzz\Helper\Data $data
    )
    {
        $this->dataHelper = $data;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $prodId = $this->getRequest()->getParam('id');
        if(!is_array($prodId)) {
            $prodId = [$prodId];
        }
        $response = $this->dataHelper->syncStatus($prodId);
        if($response){
            $this->messageManager->addSuccessMessage("Product sync sucessfully");
        }else{
            $this->messageManager->addErrorMessage("Product sync Failed");
        }
       return $this->getResponse()->setBody( $response );
    }
}
