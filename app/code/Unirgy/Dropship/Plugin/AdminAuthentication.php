<?php

namespace Unirgy\Dropship\Plugin;

class AdminAuthentication
{
    /**
     * @var \Unirgy\Dropship\Helper\Data
     */
    protected $_hlp;

    /**
     * @var \Unirgy\Dropship\Model\VendorAsAdminInterface
     */
    protected $_vendorAsAdmin;

    public function __construct(
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        \Unirgy\Dropship\Model\VendorAsAdminInterface $vendorAsAdmin
    ) {
        $this->_hlp = $udropshipHelper;
        $this->_vendorAsAdmin = $vendorAsAdmin;
    }
    public function aroundDispatch(
        \Magento\Backend\App\AbstractAction $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $redirect = $this->_vendorAsAdmin->redirectAdminVendor();
        if ($redirect) {
            return $redirect;
        } else {
            $result = $proceed($request);
        }
        if ($this->_vendorAsAdmin->getVendorId()) {
            if ($subject instanceof \Magento\Backend\Controller\Adminhtml\Auth\Logout
                && $result instanceof \Magento\Backend\Model\View\Result\Redirect
            ) {
                /** @var \Magento\Framework\Url $urlBuilder */
                $urlBuilder = $this->_hlp->getObj('Magento\Framework\Url');
                $url = $urlBuilder->getUrl('udropship', ['_scope'=>'default']);
                if (false !== strpos($url, 'ajax')) {
                    $url = $urlBuilder->getUrl('udropship', ['_scope'=>'default']);
                } elseif (false !== strpos($url, 'cms/index/noRoute')) {
                    $url = $urlBuilder->getUrl('udropship', ['_scope'=>'default']);
                }
                $result->setPath($url);
            }
        }
        return $result;
    }
}