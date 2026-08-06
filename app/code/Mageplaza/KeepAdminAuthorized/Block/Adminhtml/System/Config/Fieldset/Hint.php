<?php
/**
 * Copyright © Mageplaza. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageplaza\KeepAdminAuthorized\Block\Adminhtml\System\Config\Fieldset;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ModuleList\Loader;
use Mageplaza\KeepAdminAuthorized\Helper\Config as Helper;
use \Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class Hint extends \Magento\Backend\Block\Template implements RendererInterface
{
    /**
     * @var string
     */
    protected $_template = 'Mageplaza_KeepAdminAuthorized::system/config/fieldset/hint.phtml';

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $_metaData;

    /**
     * @var \Magento\Framework\Module\ModuleList\Loader
     */
    protected $_loader;

    /**
     * @var \Mageplaza\KeepAdminAuthorized\Helper\Config
     */
    protected $_helper;

    /**
     * @param Context $context
     * @param ProductMetadataInterface $productMetaData
     * @param Loader $loader
     * @param Helper $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductMetadataInterface $productMetaData,
        Loader $loader,
        Helper $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_metaData = $productMetaData;
        $this->_loader = $loader;
        $this->_helper = $helper;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return mixed
     */
    public function render(AbstractElement $element)
    {
        return $this->toHtml();
    }

    /**
     * @return mixed
     */
    public function getModuleName()
    {
        return $this->_helper->getConfigModule('module_name');
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        $modules = $this->_loader->load();
        $v = "";
        if (isset($modules['Mageplaza_KeepAdminAuthorized'])) {
            $v = "v" . $modules['Mageplaza_KeepAdminAuthorized']['setup_version'];
        }

        return $v;
    }

    /**
     * @return mixed
     */
    public function getModulePage()
    {
        if ($this->_helper->getConfigModule('is_marketplace')) {
            return $this->_helper->getConfigModule('marketplace_link');
        }
        return $this->_helper->getConfigModule('module_page_link');
    }

    /**
     * @return mixed
     */
    public function getExtensionsPage()
    {
        if ($this->_helper->getConfigModule('is_marketplace')) {
            return $this->_helper->getConfigModule('marketplace_extensions_link');
        }
        return $this->_helper->getConfigModule('extensions_link');
    }
}
