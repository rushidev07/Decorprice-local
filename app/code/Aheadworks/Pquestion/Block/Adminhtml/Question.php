<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Block\Adminhtml;

class Question extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_controller = 'adminhtml_question';
        $this->_blockGroup = 'Aheadworks_Pquestion';
        $this->_headerText = __('Manage Questions');
        $this->_addButtonLabel = __('Add Question');
        parent::_construct();
    }
}
