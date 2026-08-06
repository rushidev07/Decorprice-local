<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    \Unirgy\Dropship
 * @copyright  Copyright (c) 2015-2016 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\Dropship\Block\Adminhtml\Vendor\Helper\Form;

use \Magento\Framework\Data\Form\Element\CollectionFactory;
use \Magento\Framework\Data\Form\Element\Factory;
use \Magento\Framework\Data\Form\Element\Textarea;
use \Magento\Framework\Escaper;

class Wysiwyg extends Textarea
{
    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendHelper;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $wysiwygConfig;
    /**
     * @var \Unirgy\Dropship\Helper\Data
     */
    protected $udropshipHelper;

    public function __construct(
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Backend\Helper\Data $backendHelper,
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = []
    ) {

        $this->_layout = $layout;
        $this->_backendHelper = $backendHelper;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->wysiwygConfig = $wysiwygConfig;
        $this->udropshipHelper = $udropshipHelper;
    }

    protected $toogleBtn;
    protected function getToggleButton()
    {
        if (!$this->toogleBtn) {
            $tbId = 'wsw'.$this->getId();
            $this->toogleBtn = $this->_layout
                ->createBlock('Magento\Backend\Block\Widget\Button', $tbId, [
                    'data' => [
                        'id' => $tbId,
                        'label' => __('WYSIWYG Editor'),
                        'type'  => 'button',
                        'disabled' => false,
                        'class' => ''
                    ]
                ]);
        }
        return $this->toogleBtn;
    }

    public function getAfterElementHtml()
    {
        $html = parent::getAfterElementHtml();
        $id = $this->getId();
        $html .= $this->getToggleButton()->toHtml();
        $html .=<<<EOT
<script type="text/javascript">
//<![CDATA[
window.tinyMCE_GZ = window.tinyMCE_GZ || {};
window.tinyMCE_GZ.loaded = true;
require([
"jquery",
"mage/translate",
"mage/adminhtml/events",
"mage/adminhtml/wysiwyg/tiny_mce/setup",
"mage/adminhtml/wysiwyg/widget"
], function(jQuery){
(function($) {\$.mage.translate.add({"Insert Image...":"Insert Image...","Insert Media...":"Insert Media...","Insert File...":"Insert File..."})})(jQuery);
wysiwyg{$id} = new wysiwygSetup("{$id}", {$this->getWysiwygConfig()});jQuery(window).on("load", wysiwyg{$id}.setup.bind(wysiwyg{$id}, "exact"));
    editorFormValidationHandler = wysiwyg{$id}.onFormValidation.bind(wysiwyg{$id});
    Event.observe("wsw{$id}", "click", wysiwyg{$id}.toggle.bind(wysiwyg{$id}));
    varienGlobalEvents.attachEventHandler("formSubmit", editorFormValidationHandler);
    varienGlobalEvents.clearEventHandlers("open_browser_callback");
    varienGlobalEvents.attachEventHandler("open_browser_callback", wysiwyg{$id}.openFileBrowser);
//]]>
});
</script>
EOT;

        return $html;
    }
    public function getWysiwygConfig($asJson=true)
    {
        $config = $this->wysiwygConfig->getConfig([
            'add_variables' => false,
            'add_widgets' => false,
        ]);
        return $asJson ? $this->udropshipHelper->jsonEncode($config) : $config;
    }
}
