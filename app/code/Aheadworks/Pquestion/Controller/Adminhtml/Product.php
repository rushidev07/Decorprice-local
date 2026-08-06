<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class Product extends \Magento\Backend\App\Action
{
    /**
     * Check if admin has permissions to visit related pages
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Aheadworks_Pquestion::questions');
    }
}
