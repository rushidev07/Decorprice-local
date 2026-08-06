<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Block\Adminhtml\Question;

/**
 * Admin CMS page
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Aheadworks\Pquestion\Helper\Data|null
     */
    protected $_helper = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry           $registry
     * @param \Aheadworks\Pquestion\Helper\Data     $helper
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Aheadworks\Pquestion\Helper\Data $helper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Aheadworks_Pquestion';
        $this->_controller = 'adminhtml_question';
        $this->_formScripts[] = "
        require(['prototype', 'jquery'], function(){
            var initSharing = function() {
                if (!$('_info_sharing_type3')) {
                    return;
                }
                if ($('_info_sharing_type3').checked) {
                    $$('.field-sharing_value_3').first().setStyle({display: 'block'});
                } else {
                    $$('.field-sharing_value_3').first().setStyle({display: 'none'});
                }
                if ($('_info_sharing_type1').checked) {
                    $$('.admin__field.field-product_name').first().show();
                    $$('.field-sharing_value_1').first().setStyle({display: 'block'});
                } else {
                    $$('.admin__field.field-product_name').first().hide();
                    $$('.field-sharing_value_1').first().setStyle({display: 'none'});
                }
            };
            var initObservers = function() {
                $$('.field-sharing_type input').each(function(el){
                    el.observe('click', function(){
                        initSharing();
                    });
                });
            };
            jQuery(document).ready(function() {
                initSharing();
                initObservers();
            });

            window.awpqPublish = function() {
                $('edit_form').setAttribute('action', '" . $this->getPublishUrl() . "');
                $('save').click();
            };
        });
        ";
        parent::_construct();
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $title = __('New Question');
        $questionModel = $this->_coreRegistry->registry('current_question');
        if (null !== $questionModel->getId()) {
            $title = __(
                'Manage Question #%1 from %2 &lt;%3&gt;',
                $questionModel->getId(),
                $questionModel->getAuthorName(),
                $questionModel->getAuthorEmail()
            );
        }
        return $title;
    }

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->removeButton('reset');
        $questionModel = $this->_coreRegistry->registry('current_question');
        if (null !== $questionModel->getId()) {
            if ($questionModel->getStatus() != \Aheadworks\Pquestion\Model\Source\Question\Status::DECLINE_VALUE) {
                $this->addButton(
                    'reject',
                    [
                        'class'   => 'reject',
                        'label'   => __('Reject'),
                        'onclick' => 'setLocation(\'' . $this->getRejectUrl() . '\')',
                    ],
                    10
                );
            }
            if ($questionModel->getStatus() != \Aheadworks\Pquestion\Model\Source\Question\Status::APPROVED_VALUE) {
                $this->addButton(
                    'publish',
                    [
                        'class' => 'publish',
                        'label' => __('Publish'),
                        'onclick' => 'awpqPublish()',
                    ],
                    10
                );
            }
        }
        parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getRejectUrl()
    {
        $questionModel = $this->_coreRegistry->registry('current_question');
        return $this->getUrl('productquestion/question/changeStatus', [
            '_current' => true,
            'id' => $questionModel->getId(),
            'status_id' => \Aheadworks\Pquestion\Model\Source\Question\Status::DECLINE_VALUE
        ]);
    }

    /**
     * @return string
     */
    public function getPublishUrl()
    {
        $questionModel = $this->_coreRegistry->registry('current_question');
        return $this->getUrl('productquestion/question/save', [
            '_current' => true,
            'id' => $questionModel->getId(),
            'status_id' => \Aheadworks\Pquestion\Model\Source\Question\Status::APPROVED_VALUE
        ]);
    }
}
