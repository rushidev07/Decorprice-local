<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_NameYourPrice
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\NameYourPrice\Block\Adminhtml\Requests\Edit\Tab;

use IntlDateFormatter;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Mageplaza\NameYourPrice\Helper\Data as HelperData;
use Mageplaza\NameYourPrice\Model\Requests;

/**
 * Class General
 * @package Mageplaza\NameYourPrice\Block\Adminhtml\Requests\Edit\Tab
 */
class General extends Generic implements TabInterface
{
    /**
     * @var Yesno
     */
    protected $_yesno;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var Store
     */
    public $systemStore;

    /**
     * General constructor.
     *
     * @param Yesno $yesno
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param HelperData $_helperData
     * @param array $data
     */
    public function __construct(
        Yesno $yesno,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        HelperData $_helperData,
        array $data = []
    ) {
        $this->_yesno = $yesno;
        $this->systemStore = $systemStore;
        $this->_helperData = $_helperData;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var Requests $request */
        $request = $this->_coreRegistry->registry('current_request');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('request_');
        $form->setFieldNameSuffix('request');

        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('General'),
            'class' => 'fieldset-wide'
        ]);

        if ($request->getId()) {
            $fieldset->addField('request_id', 'hidden', [
                'name' => 'request_id'
            ]);
        }

        $fieldset->addField('name', 'note', [
            'name' => 'name',
            'label' => __('Customer Name'),
            'title' => __('Customer Name'),
            'text' => $this->escapeHtml($request->getCustomerName())
        ]);

        $fieldset->addField('email', 'note', [
            'name' => 'email',
            'label' => __('Customer Email'),
            'title' => __('Customer Email'),
            'text' => $this->escapeHtml($request->getCustomerEmail())
        ]);

        if ($request->getPhone()) {
            $fieldset->addField('customer_phone', 'note', [
                'name' => 'email',
                'label' => __('Customer Phone'),
                'title' => __('Customer Phone'),
                'text' => $this->escapeHtml($request->getPhone())
            ]);
        }

        $fieldset->addField('mpb_product_name', 'note', [
            'name' => 'mpb_product_name',
            'label' => __('Product Name'),
            'title' => __('Product Name'),
            'text' => $this->escapeHtml($request->getProductName())
        ]);

        $fieldset->addField('mpb_product_sku', 'note', [
            'name' => 'mpb_product_sku',
            'label' => __('SKU'),
            'title' => __('SKU'),
            'text' => $this->escapeHtml($request->getSku())
        ]);

        $productId = $request->getProductId();
        $fieldset->addField('mpb_original_price', 'note', [
            'label' => __('Original Price'),
            'text' => $this->_helperData->convertPrice($this->_helperData->getProductPrice($productId))
        ]);

        $fieldset->addField('bargain_qty', 'text', [
            'name' => 'bargain_qty',
            'label' => __('Your Committed Min. Qty'),
            'title' => __('Your Committed Min. Qty'),
            'required' => true,
            'class' => 'validate-digits validate-greater-than-zero'
        ]);

        $fieldset->addField('bargain_price', 'text', [
            'name' => 'bargain_price',
            'label' => __('Bargain Price'),
            'required' => true,
            'class' => 'validate-greater-than-zero validate-number-range'
        ]);

        $data = $this->systemStore->getStoresStructure(false, [$request->getStoreIds()]);
        $content = '';
        foreach ($data as $website) {
            $content .= '<b>' . $website['label'] . '</b><br/>';
            foreach ($website['children'] as $group) {
                $content .= str_repeat(
                    '&nbsp;',
                    3
                ) . '<b>' . $this->_escaper->escapeHtml($group['label']) . '</b><br/>';
                foreach ($group['children'] as $store) {
                    $content .= str_repeat('&nbsp;', 6) . $this->_escaper->escapeHtml($store['label']) . '<br/>';
                }
            }
        }

        $fieldset->addField('mpb_store_ids', 'note', [
            'name' => 'mpb_store_ids',
            'label' => __('Store View'),
            'title' => __('Store View'),
            'text' => $content
        ]);

        $fieldset->addField('mpb_submitted_date', 'note', [
            'name' => 'mpb_submitted_date',
            'label' => __('Submitted Date'),
            'title' => __('Submitted Date'),
            'text' => $this->escapeHtml($this->formatDate($request->getSubmittedDate(), IntlDateFormatter::MEDIUM, true))
        ]);

        $fieldset->addField('mpb_status', 'note', [
            'name' => 'mpb_status',
            'label' => __('Status'),
            'title' => __('Status'),
            'text' => $this->escapeHtml($this->_helperData->getLabelStatus($request->getStatus()))
        ]);

        if ($request->getStatus() === HelperData::STATUS_APPROVED) {
            $orderIncrementIds = $this->_helperData->getIncrementOrderIds($request);

            $fieldset->addField('order_id', 'note', [
                'name' => 'order_id',
                'label' => __('Order ID'),
                'title' => __('Order ID'),
                'text' => $this->escapeHtml($orderIncrementIds ? implode(',', $orderIncrementIds) : __('None'))
            ]);
        }

        $messageFieldset = $form->addFieldset('message_fieldset', [
            'legend' => __('Message'),
            'class' => 'fieldset-wide'
        ]);

        $messageFieldset->addField('mpb_customer_message', 'note', [
            'name' => 'customer_message',
            'label' => __('Customer\'s Message'),
            'title' => __('Customer\'s Message'),
            'text' => $this->escapeHtml($request->getCustomerMessage())
        ]);

        $messageFieldset->addField('admin_message', 'textarea', [
            'name' => 'admin_message',
            'label' => __('Admin\'s Response'),
            'title' => __('Admin\'s Response'),
            'note' => __('The admin\'s response will be sent to customers via email when the admin clicks on "Save & Approve" or "Save & Reject"')
        ]);

        $form->addValues($request->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Name Your Price');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
