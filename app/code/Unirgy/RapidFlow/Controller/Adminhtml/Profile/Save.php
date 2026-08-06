<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;

use Magento\Backend\Model\Session as ModelSession;
use Unirgy\RapidFlow\Helper\Data;
use Unirgy\RapidFlow\Model\Profile;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Helper\Data as HelperData;
use Unirgy\RapidFlow\Model\Profile\HistoryFactory;
use Unirgy\RapidFlow\Model\ResourceModel\Profile as ProfileResource;

class Save extends AbstractProfile
{
    protected $_historyFactory;
    public function __construct(
        Context $context,
        Profile $profile,
        HelperData $catalogHelper,
        ProfileResource $resource,
        HistoryFactory $historyFactory
    )
    {
        $this->_historyFactory = $historyFactory;

        parent::__construct($context, $profile, $catalogHelper, $resource);
    }
    public function execute()
    {
        if ($data = $this->getRequest()->getPost()->toArray()) {
            try {
                $model = $this->_profile;

                if ($id = $this->getRequest()->getParam('id')) {
                    $model->load($id);
                }
                if (!isset($data['columns_post'])) {
                    $data['columns_post'] = [];
                }
                if (isset($data['conditions'])) {
                    $data['conditions_post'] = $data['conditions'];
                    unset($data['conditions']);
                }
                if (isset($data['options']['reindex'])) {
                    $data['options']['reindex'] = array_flip($data['options']['reindex']);
                }
                if (isset($data['options']['refresh'])) {
                    $data['options']['refresh'] = array_flip($data['options']['refresh']);
                }

                foreach ($this->_getHistoryColumns() as $__hc) {
                    unset($data[$__hc]);
                }
                $model->addData($data);
//                $model = $model->factory();

                if ($model->getCreatedTime() === NULL || $model->getUpdateTime() === NULL) {
                    $model->setCreatedTime(Data::now())
                        ->setUpdateTime(Data::now());
                } else {
                    $model->setUpdateTime(Data::now());
                }

                $model->save();
                $this->messageManager->addSuccessMessage(__('Profile was successfully saved'));
                $this->_session->setFormData(false);

                if ($invokeStatus = $this->getRequest()->getParam('start')) {
                    $model->pending($invokeStatus)->save();
                    $this->messageManager->addSuccessMessage(__('Profile started successfully'));
                }

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $model->getId()]);
                    return;
                }

                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_session->setFormData($data);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('Unable to find profile to save'));
        $this->_redirect('*/*/');
    }

    protected $_historyColumns;
    protected function _getHistoryColumns()
    {
        if ($this->_historyColumns==null) {
            $this->_historyColumns = [];
            $hr = $this->_historyFactory->create()->getResource();
            $fields = $hr->getConnection()->describeTable($hr->getMainTable());
            $this->_historyColumns = array_keys($fields);
        }
        return $this->_historyColumns;
    }
}
