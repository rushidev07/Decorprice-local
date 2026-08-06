<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Block\Adminhtml\Question\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        $id = $this->getRequest()->getParam('id');
        $customerId = $this->getRequest()->getParam('customer_id');
        $productId = $this->getRequest()->getParam('product_id');

        $action = $this->getUrl(
            '*/*/save',
            [
                'id' => $id,
                'customer_id' => $customerId,
                'product_id'  => $productId
            ]
        );
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $action, 'method' => 'post']]
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
