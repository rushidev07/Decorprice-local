<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Block\Adminhtml\Question\Edit\Tab;

use Aheadworks\Pquestion\Model\Customer\Checker as CustomerChecker;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Store\Model\System\Store;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Data\Form;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Pquestion\Model\Source\Question\Status;
use Aheadworks\Pquestion\Model\Source\Question\Visibility;
use Aheadworks\Pquestion\Block\Adminhtml\Question\Edit\Tab\General\Answers;
use Magento\Framework\Phrase;

/**
 * Class General
 * @package Aheadworks\Pquestion\Block\Adminhtml\Question\Edit\Tab
 */
class General extends Generic implements TabInterface
{
    /**
     * @var Store
     */
    private $systemStore;

    /**
     * @var Status
     */
    private $status;

    /**
     * @var Visibility
     */
    private $visibility;

    /**
     * @var CustomerChecker
     */
    private $customerChecker;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param CustomerChecker $customerChecker
     * @param Status $status
     * @param Visibility $visibility
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        CustomerChecker $customerChecker,
        Status $status,
        Visibility $visibility,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->customerChecker = $customerChecker;
        $this->status = $status;
        $this->visibility = $visibility;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form fields
     *
     * @return $this
     * @throws LocalizedException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function initForm()
    {
        $questionModel = $this->_coreRegistry->registry('current_question');

        /** @var Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('_info_');

        $fieldset = $form->addFieldset('general', ['legend' => __('Question')]);
        $authorLinkTitle = $questionModel->getAuthorName() . ' (' . $questionModel->getAuthorEmail(). ')';
        $questionModel->setAuthorLinkTitle($authorLinkTitle);
        if ($questionModel->getCustomerId()
            && $this->customerChecker->checkCustomerExistByEmail($questionModel->getAuthorEmail())
        ) {
            $fieldset->addField(
                'author_link_title',
                'link',
                [
                    'label' => __('Author'),
                    'title' => $authorLinkTitle,
                    'value' => $authorLinkTitle,
                    'href'  => $this->getUrl(
                        'customer/index/edit',
                        ['id' => $questionModel->getCustomerId()]
                    ),
                    'target' => '_blank'
                ]
            );
        } else {
            $fieldset->addField(
                'author_link_title',
                'label',
                [
                    'label' => __('Author'),
                    'title' => $authorLinkTitle,
                    'value' => $authorLinkTitle,
                ]
            );
        }

        if (!$this->_storeManager->hasSingleStore()) {
            if (null === $questionModel->getId()) {
                $fieldset->addField(
                    'store_id',
                    'select',
                    [
                        'label'    => __('Asked From'),
                        'name'     => 'data[store_id]',
                        'type'     => 'store',
                        'required' => true,
                        'values'   => $this->systemStore->getStoreValuesForForm(false, false),
                    ]
                );
            } else {
                $fieldset->addField(
                    'store_label',
                    'label',
                    [
                        'label' => __('Asked From'),
                        'name'  => 'data[store_label]',
                    ]
                );
            }
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                [
                    'name' => 'data[store_id]'
                ]
            );
        }

        if (null === $questionModel->getId()) {
            $fieldset->addField(
                'status',
                'select',
                [
                    'label' => __('Status'),
                    'name'  => 'data[status]',
                    'values' => $this->status->toOptionArray()
                ]
            );
        } else {
            $fieldset->addField(
                'status',
                'label',
                [
                    'label' => __('Status'),
                    'name'  => 'data[status]',
                ]
            );
        }
        $fieldset->addField(
            'product_name',
            'link',
            [
                'label' => __('Product'),
                'title' => $questionModel->getProductName(),
                'value' => $questionModel->getProductName(),
                'href'  => $this->getUrl(
                    'catalog/product/edit',
                    ['id' => $questionModel->getProductId()]
                ),
                'target' => '_blank'
            ]
        );
        if (null !== $questionModel->getId()) {
            $fieldset->addField(
                'visibility',
                'radios',
                [
                    'label'  => __('Visibility'),
                    'name'   => 'data[visibility]',
                    'values' => $this->visibility->toOptionArray()
                ]
            );
        }
        if (null === $questionModel->getId()) {
            $dateFormatIso = $this->_localeDate->getDateFormat(
                \IntlDateFormatter::MEDIUM
            );
            $timeFormatIso = $this->_localeDate->getTimeFormat(
                \IntlDateFormatter::MEDIUM
            );
            $fieldset->addField(
                'created_at',
                'date',
                [
                    'label' => __('Created At'),
                    'name'  => 'data[created_at]',
                    'title' => __('Created At'),
                    'date_format' => $dateFormatIso,
                    'time_format' => $timeFormatIso,
                ]
            );
        } else {
            $fieldset->addField(
                'created_at',
                'label',
                [
                    'label' => __('Asked at'),
                    'name'  => 'data[created_at]',
                    'title' => __('Asked at'),
                ]
            );
        }
        $fieldset->addField(
            'helpfulness',
            'text',
            [
                'label' => __('Question Rating'),
                'name'  => 'data[helpfulness]',
                'type'  => 'text',
            ]
        );
        $fieldset->addField(
            'content',
            'textarea',
            [
                'label'    => __('Question'),
                'name'     => 'data[content]',
                'type'     => 'textarea',
                'required' => true,
                'after_element_html' => $this->getTextareaAutoResizeJs()
            ]
        );
        $form->setValues($questionModel->getData());
        $createdAt = null;
        if ($questionModel->getCreatedAt()) {
            $createdAt = $questionModel->getCreatedAt();
        }
        $createdAt = $this->_localeDate->formatDate(new \DateTime($createdAt), \IntlDateFormatter::MEDIUM, true);
        $form->getElement('created_at')->setValue($createdAt);
        if (null !== $questionModel->getStatus()) {
            $form->getElement('status')->setValue(
                $this->status->getOptionByValue($questionModel->getStatus())
            );
        } else {
            $form->getElement('status')->setValue(
                Status::APPROVED_VALUE
            );
        }
        if (null === $questionModel->getVisibility()) {
            $form->getElement('visibility')->setValue(
                Visibility::PUBLIC_VALUE
            );
        }
        if (null === $questionModel->getStoreId()) {
            $form->getElement('store_id')->setValue(
                $this->_storeManager->getDefaultStoreView()->getId()
            );
        }
        $this->setForm($form);
        $this->addAnswersFieldset();
        return $this;
    }

    /**
     * Add answer fieldset on form
     *
     * @return void
     * @throws LocalizedException
     */
    private function addAnswersFieldset()
    {
        $block = $this->getLayout()->createBlock(Answers::class);

        $this->getForm()->addFieldset(
            'answers',
            [
                'legend' => __('Answers'),
                'html_content' => $block->toHtml()
            ]
        );
    }

    /**
     * Prepare label for tab
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('General');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get js for autoresize textarea field
     *
     * @return string
     */
    private function getTextareaAutoResizeJs()
    {
        return <<<HTML
<script type="text/javascript">
require(["prototype"], function(){
    var textarea = $('_info_content');
    var fn = function () {
        textarea.setStyle({height: textarea.scrollHeight + 2 + 'px'});
    };
    textarea.observe('keyup', function(){
        fn();
    });
    fn();
    Event.observe(window, 'load', function(){
        fn();
    });
});
</script>
HTML;
    }
}
