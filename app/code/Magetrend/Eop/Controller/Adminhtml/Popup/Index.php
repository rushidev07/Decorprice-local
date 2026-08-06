<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.3 or later
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */

namespace Magetrend\Eop\Controller\Adminhtml\Popup;

/**
 * Popup management controller class
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Index extends \Magetrend\Eop\Controller\Adminhtml\Popup
{
    /**
     * Newsletter subscribers page
     *
     * @return void
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->_view->loadLayout();

        $this->_setActiveMenu('Magetrend_Eop::popup_index');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Exit Offer Popups'));

        $this->_addBreadcrumb(__('Exit Offer '), __('Exit Offer'));
        $this->_addBreadcrumb(__('Exit Offer'), __('Popups'));

        $this->_view->renderLayout();
    }
}
