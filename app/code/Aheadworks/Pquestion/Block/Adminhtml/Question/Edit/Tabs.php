<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Block\Adminhtml\Question\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $jsonEncoder, $authSession, $data);
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('aw_pq_info_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Question'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'general_tab',
            [
                'label'   => __('General'),
                'content' => $this->getLayout()
                    ->createBlock(\Aheadworks\Pquestion\Block\Adminhtml\Question\Edit\Tab\General::class)
                    ->initForm()
                    ->toHtml(),
                'active'  => true
            ]
        );

        $state = false;
        $activeTab = $this->getRequest()->getParam('tab', null);
        if ($activeTab == 'aw_pq_info_tabs_sharing_tab') {
            $state = true;
        }
        $this->addTab(
            'sharing_tab',
            [
                'label'   => __('Sharing Options'),
                'content' => $this->getLayout()
                    ->createBlock(\Aheadworks\Pquestion\Block\Adminhtml\Question\Edit\Tab\Sharing::class)
                    ->initForm()
                    ->toHtml(),
                'active'  => $state
            ]
        );
        return parent::_beforeToHtml();
    }
}
