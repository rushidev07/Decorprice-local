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

namespace Mageplaza\NameYourPrice\Block\Adminhtml\Requests;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Mageplaza\NameYourPrice\Helper\Data;
use Mageplaza\NameYourPrice\Model\Requests;

/**
 * Class Edit
 * @package Mageplaza\NameYourPrice\Block\Adminhtml\Requests
 */
class Edit extends Container
{
    /**
     * @var Registry
     */
    public $coreRegistry;

    /**
     * Edit constructor.
     *
     * @param Registry $coreRegistry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Registry $coreRegistry,
        Context $context,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context, $data);
    }

    /**
     * Initialize Rule edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Mageplaza_NameYourPrice';
        $this->_controller = 'adminhtml_requests';

        parent::_construct();

        $this->buttonList->add(
            'save-and-approve',
            [
                'label' => __('Save & Approve'),
                'class' => 'approve',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'save',
                            'target' => '#edit_form',
                            'eventData' => ['action' => ['args' => ['type' => 'save_approve']]],
                        ]
                    ]
                ]
            ],
            -100
        );

        $this->buttonList->add(
            'save-and-reject',
            [
                'label' => __('Save & Reject'),
                'class' => 'reject',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'save',
                            'target' => '#edit_form',
                            'eventData' => ['action' => ['args' => ['type' => 'save_reject']]],
                        ]
                    ]
                ]
            ],
            -100
        );

        $this->buttonList->remove('reset');
    }

    /**
     * @param LayoutInterface $layout
     *
     * @return Container
     */
    public function setLayout(LayoutInterface $layout)
    {
        /** @var Requests $request */
        $request = $this->coreRegistry->registry('current_request');

        if ($request->getStatus() === Data::STATUS_APPROVED) {
            $this->buttonList->remove('save-and-approve');
        }

        if ($request->getStatus() === Data::STATUS_REJECT) {
            $this->buttonList->remove('save-and-reject');
        }

        if (in_array($request->getStatus(), [Data::STATUS_CLOSED, Data::STATUS_REJECT_BY_CUSTOMER], true)) {
            $this->buttonList->remove('save-and-reject');
            $this->buttonList->remove('save-and-approve');
        }

        return parent::setLayout($layout);
    }

    /**
     * Retrieve text for header element depending on loaded Rule
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var Requests $request */
        $request = $this->coreRegistry->registry('current_request');
        if ($request->getId()) {
            return __("Edit Request '%1'", $this->escapeHtml($request->getName()));
        }

        return __('New Request');
    }

    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        /** @var Requests $request */
        $request = $this->coreRegistry->registry('current_request');

        if ($requestId = $request->getId()) {
            return $this->getUrl('*/*/save', ['id' => $requestId]);
        }

        return parent::getFormActionUrl();
    }
}
