<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Block\Adminhtml\Customer\Edit\Tab;

class Questions extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'customer/tab/question.phtml';

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Product Questions');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Product Questions');
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        $customerId =  $this->_coreRegistry
            ->registry(\Magento\Customer\Controller\RegistryConstants::CURRENT_CUSTOMER_ID)
        ;
        if (!$customerId) {
            return false;
        }
        $customer =  \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Magento\Customer\Model\Customer::class)
            ->load($customerId)
        ;
        return $customer->getWebsiteId() != 0;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getAfter()
    {
        return 'reviews';
    }

    /**
     * @return $this
     */
    public function initForm()
    {
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('_aw_pq');
        $customerId =  $this->_coreRegistry
            ->registry(\Magento\Customer\Controller\RegistryConstants::CURRENT_CUSTOMER_ID)
        ;
        $customer =  \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Magento\Customer\Model\Customer::class)
            ->load($customerId)
        ;

        if ($customer->getWebsiteId() == 0) {
            $this->setForm($form);
            return $this;
        }

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Manage Notification List')]
        );

        $helper =  \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Aheadworks\Pquestion\Helper\Notification::class)
        ;
        $fieldset->addField(
            'subscribe_to',
            'multiselect',
            [
                'label'  => __('Subscribe to'),
                'title'  => __('Subscribe to'),
                'name'   => 'subscribe_to[]',
                'values' => $helper->getNotificationListForCustomer($customer)
            ]
        );

        if ($customer->isReadonly()) {
            $form->getElement('subscribe_to')->setReadonly(true, true);
        }

        $this->setForm($form);

        //init values
        $data = $helper->getNotificationListForCustomer(
            $customer,
            $customer->getWebsiteId()
        );
        $subscribeTo = [];
        foreach ($data as $item) {
            if ($item['checked']) {
                $subscribeTo[] = $item['value'];
            }
        }
        $this->getForm()->setValues(
            [
                'subscribe_to' => $subscribeTo
            ]
        );
        return $this;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->canShowTab()) {
            $this->initForm();
            return parent::_toHtml();
        } else {
            return '';
        }
    }
}
