<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;

class HistoryGrid extends AbstractProfile
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()
            ->getBlock('urapidflow.profile.historygrid')
            ->setProfileId($this->getRequest()->getParam('id'));
        $this->_view->renderLayout();
    }
}
