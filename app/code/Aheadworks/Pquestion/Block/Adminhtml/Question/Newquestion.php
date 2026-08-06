<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Block\Adminhtml\Question;

class Newquestion extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_controller = 'adminhtml_question';
        $this->_blockGroup = 'Aheadworks_Pquestion';
        parent::_construct();
        $this->removeButton('add');
        $this->_addBackButton();
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/grid');
    }
}
