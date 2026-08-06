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

use \Magento\Framework\Data\Form\Element\Select;

class VendorStatus extends DependSelect
{
    public function getAfterElementHtml()
    {
        $html = parent::getAfterElementHtml();
        $html .=<<<EOT
<script type="text/javascript">
require(["jquery","prototype","domReady!"], function(jQuery) {
    var oldValue = $('{$this->getHtmlId()}').value;
	var vendorStatusChange = function() {
	    var newValue = $('{$this->getHtmlId()}').value;
	    if (oldValue=='P') {
	        if (newValue=='A') {
	            $('send_confirmation_email').value=1;
	        } else {
	            $('send_confirmation_email').value=0;
	        }
	    }
	}
    $('{$this->getHtmlId()}').observe('change', vendorStatusChange);

})
</script>
EOT;
        return $html;
    }
}
