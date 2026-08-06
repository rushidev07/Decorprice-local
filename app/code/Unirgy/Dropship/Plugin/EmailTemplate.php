<?php

namespace Unirgy\Dropship\Plugin;

use Magento\Email\Model\Template as CoreEmailTemplate;

class EmailTemplate
{
    protected $_hlp;
    public function __construct(
        \Unirgy\Dropship\Helper\Data $udropshipHelper
    ) {
        $this->_hlp = $udropshipHelper;
    }
    public function afterLoad(CoreEmailTemplate $template, $result)
    {
        $origTpl = $template->getOrigTemplateCode();
        if (0===stripos($origTpl, 'udropship_')
            || 0===stripos($origTpl, 'udqa_')
            || false!==stripos($origTpl, 'rma')
        ) {
            $template->setIsLegacy(true);
        }
        return $result;
    }
}
