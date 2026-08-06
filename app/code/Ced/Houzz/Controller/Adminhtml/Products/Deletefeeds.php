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
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;

class Deletefeeds extends \Magento\Backend\App\Action
{
    /**
     * PageFactory
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @throws NotFoundException
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $filter = $this->_objectManager->get('\Magento\Ui\Component\MassAction\Filter');
        $feedIds = $filter->getCollection($this->_objectManager->create('\Ced\Houzz\Model\Feeds')
            ->getCollection())->getAllIds();
        foreach ($feedIds as $feedId) {
            $this->_objectManager->get('Ced\Houzz\Model\Feeds')->load($feedId)->delete()->save();
        }
        $this->messageManager->addSuccessMessage(__(count($feedIds).' Feed Records Deleted Successfully.'));
        return $this->_redirect('*/*/feeds');
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