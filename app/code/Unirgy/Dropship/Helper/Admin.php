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

namespace Unirgy\Dropship\Helper;

use Magento\Framework\App\Area as AppArea;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Backend\Model\Session\AdminConfig as SessionAdminConfig;

class Admin extends AbstractHelper
{
    const VENDOR_ADMIN_COOKIE = 'udvendor_admin';
    /**
     * @var \Unirgy\Dropship\Helper\Data
     */
    protected $_hlp;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    protected $_userFactory;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Framework\Url
     */
    protected $_frontUrl;

    public function __construct(
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Framework\Url $frontUrl,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->_hlp = $udropshipHelper;
        $this->_userFactory = $userFactory;
        $this->_backendUrl = $backendUrl;
        $this->_frontUrl = $frontUrl;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }
    public function loginAsAdmin($vendor)
    {
        $vendor = $this->_hlp->getVendor($vendor);
        $user = $this->_userFactory->create()->load($vendor->getId(), 'udropship_vendor');
        if ($user->getId()) {
            $coreSession = ObjectManager::getInstance()->get('Magento\Framework\Session\SessionManager');
            $oId = $coreSession->getSessionId();
            $sId = !empty($_COOKIE[SessionAdminConfig::SESSION_NAME_ADMIN]) ? $_COOKIE[SessionAdminConfig::SESSION_NAME_ADMIN] : null;
            $this->switchSession(AppArea::AREA_ADMINHTML, $sId);
            /** @var \Magento\Backend\Model\Auth $auth */
            $auth = ObjectManager::getInstance()->get('\Magento\Backend\Model\Auth');
            $user->getResource()->recordLogin($user);
            $auth->getAuthStorage()->setIsFirstVisit(true);
            $auth->getAuthStorage()->setUser($user);
            $auth->getAuthStorage()->processLogin();
            if ($this->_backendUrl->useSecretKey()) {
                $this->_backendUrl->renewSecretUrls();
            }
            $adminSessionId = $auth->getAuthStorage()->getSessionId();
            $auth->getAuthStorage()->prolong();
            /** @var \Magento\Security\Model\AdminSessionsManager $aSessMan */
            $aSessMan = $this->_hlp->getObj('Magento\Security\Model\AdminSessionsManager');
            $aSessMan->processLogin();
            $this->switchSession(AppArea::AREA_FRONTEND, $oId, true);
            $this->createVendorAdminCookie($this->_hlp->session(), $adminSessionId);
        }
    }
    public function logoutAsAdmin($vendor)
    {
        $vendor = $this->_hlp->getVendor($vendor);
        $user = $this->_userFactory->create()->load($vendor->getId(), 'udropship_vendor');
        if ($user->getId() && !empty($_COOKIE[SessionAdminConfig::SESSION_NAME_ADMIN])) {
            $coreSession = ObjectManager::getInstance()->get('Magento\Framework\Session\SessionManager');
            $oId = $coreSession->getSessionId();
            $sId = !empty($_COOKIE[SessionAdminConfig::SESSION_NAME_ADMIN]) ? $_COOKIE[SessionAdminConfig::SESSION_NAME_ADMIN] : null;
            $this->switchSession(AppArea::AREA_ADMINHTML, $sId);
            /** @var \Magento\Backend\Model\Auth $auth */
            $auth = $this->_hlp->getObj('\Magento\Backend\Model\Auth');
            if ($auth->isLoggedIn() && $auth->getUser()->getId()==$user->getId()) {
                ObjectManager::getInstance()->get('Magento\Backend\Model\Session')->unsetAll();
                ObjectManager::getInstance()->get('Magento\Backend\Model\Session')->unsetAll();
            }
            /** @var \Magento\Security\Model\AdminSessionsManager $aSessMan */
            $aSessMan = $this->_hlp->getObj('Magento\Security\Model\AdminSessionsManager');
            $aSessMan->processLogout();
            $this->switchSession(AppArea::AREA_FRONTEND, $oId, true);
        }
        $this->deleteVendorAdminCookie($this->_hlp->session());
    }
    public function loginAdminVendor($user)
    {
        $vendorId = $user->getUdropshipVendor();
        $this->_hlp->aHlp()->loginAdminVendor($user);
        /** @var \Magento\Backend\Model\Auth $auth */
        $auth = $this->_hlp->getObj('Magento\Backend\Model\Auth');
        $session = $auth->getAuthStorage();
        $oId = $session->getSessionId();

        if ($user->getUdropshipVendor()) {
            $this->_hlp->aHlp()->switchSession(AppArea::AREA_FRONTEND);
            $session = ObjectManager::getInstance()->get('Unirgy\Dropship\Model\Session');
            if (!$session->isLoggedIn()) {
                $session->loginById($vendorId);
            }
            $this->_hlp->aHlp()->switchSession(AppArea::AREA_ADMINHTML, $oId, true);
        }
    }
    public function logoutAdminVendor($vendor)
    {
        $vendor = $this->_hlp->getVendor($vendor);
        /** @var \Magento\Framework\Url $urlBuilder */
        $urlBuilder = ObjectManager::getInstance()->get('Magento\Framework\Url');
        $coreSession = ObjectManager::getInstance()->get('Magento\Framework\Session\SessionManagerInterface');
        $oId = $coreSession->getSessionId();

        $this->_hlp->aHlp()->switchSession(AppArea::AREA_FRONTEND, null);
        $session = $this->_hlp->session();
        if ($session->isLoggedIn() && $session->getVendorId()==$vendor->getId()) {
            $session->logout();
        }

        $url = $urlBuilder->getUrl('udropship', ['_scope'=>'default']);
        if (false !== strpos($url, 'ajax')) {
            $url = $urlBuilder->getUrl('udropship', ['_scope'=>'default']);
        } elseif (false !== strpos($url, 'cms/index/noRoute')) {
            $url = $urlBuilder->getUrl('udropship', ['_scope'=>'default']);
        }
        $this->deleteVendorAdminCookie($this->_hlp->session());
        $this->_hlp->aHlp()->switchSession(AppArea::AREA_ADMINHTML, $oId, true);
    }
    protected $_oldArea;
    protected $_oldSessCfg;
    protected $_oldSessName;
    protected $_oldCookiePath;
    public function switchSession($area, $id=null, $restore=false)
    {
        /** @var \Magento\Backend\Model\Auth $auth */
        $auth = ObjectManager::getInstance()->get('\Magento\Backend\Model\Auth');
        if ($this->_oldArea== AppArea::AREA_ADMINHTML) {
            $session = $auth->getAuthStorage();
        } else {
            $session = $this->_hlp->session();
        }
        $session->writeClose();
        $session->clearStorage();
        //$GLOBALS['_SESSION'] = null;
        if ($restore) {
            //$this->_hlp->setDesignStore();
            $this->_hlp->setObjectPrivateProperty($this->appState(), '_areaCode', $this->_oldArea);
            $this->_hlp->setObjectPrivateProperty($session, 'sessionConfig', $this->_oldSessCfg);
            $this->_oldSessCfg->setCookiePath($this->_oldCookiePath);
            $this->_oldSessCfg->setName($this->_oldSessName);
            $this->_oldArea = null;
            $this->_oldSessCfg = null;
            $this->_oldCookiePath = null;
            $this->_oldSessName = null;
        } else {
            $this->_oldArea = $this->appState()->getAreaCode();
            $this->_oldSessCfg = $this->_hlp->getObjectPrivateProperty($session, 'sessionConfig');
            $this->_oldCookiePath = $this->_oldSessCfg->getCookiePath();
            $this->_oldSessName = $this->_oldSessCfg->getName();
            $this->_hlp->setObjectPrivateProperty($this->appState(), '_areaCode', $area);
            if ($area== AppArea::AREA_ADMINHTML) {
                $newSessConfig = $this->_hlp->getObj('Magento\Backend\Model\Session\AdminConfig');
            } else {
                $newSessConfig = $this->_hlp->getObj('Magento\Framework\Session\Config');
            }
            $this->_hlp->setObjectPrivateProperty($session, 'sessionConfig', $newSessConfig);
            //$this->_hlp->setDesignStore(true, $area);
        }
        if ($area== AppArea::AREA_ADMINHTML) {
            $session = $auth->getAuthStorage();
            $session->setName(SessionAdminConfig::SESSION_NAME_ADMIN);
            /** @var \Magento\Backend\Model\UrlFactory $bUrlFactory */
            $bUrlFactory = $this->_hlp->getObj('Magento\Backend\Model\UrlFactory');
            /** @var \Magento\Backend\App\Area\FrontNameResolver $adminFnRes */
            $adminFnRes = $this->_hlp->getObj('Magento\Backend\App\Area\FrontNameResolver');
            //$this->_hlp->setDesignStore();
            if (!$restore) {
                $this->_hlp->setObjectPrivateProperty($this->appState(), '_areaCode', $this->_oldArea);
            }
            $sCfg = $this->_hlp->getObjectPrivateProperty($session, 'sessionConfig');
            $baseUrl = parse_url($bUrlFactory->create()->getBaseUrl(), PHP_URL_PATH);
            $cookiePath = $baseUrl . $adminFnRes->getFrontName();
            $sCfg->setCookiePath($cookiePath);
        } else {
            $session = $this->_hlp->session();
            /** @var \Magento\Framework\UrlFactory $urlFactory */
            $urlFactory = $this->_hlp->getObj('Magento\Framework\UrlFactory');
            $sCfg = $this->_hlp->getObjectPrivateProperty($session, 'sessionConfig');
            $baseUrl = parse_url($urlFactory->create()->getBaseUrl(), PHP_URL_PATH);
            $cookiePath = $baseUrl;
            $session->setName('PHPSESSID');
            $sCfg->setName('PHPSESSID');
            $sCfg->setCookiePath($cookiePath);
        }
        if (!$id) {
            $id = $this->cookieManager->getCookie($session->getName());
        }
        $session->setSessionId($id);
        $session->start();
    }
    public function redirectAdminVendor()
    {
        /** @var \Magento\Backend\Model\Auth $auth */
        $auth = $this->_hlp->getObj('\Magento\Backend\Model\Auth');
        $session = $auth->getAuthStorage();
        $oId = $session->getSessionId();
        $user = $auth->getUser();
        if (!$user || !$user->getId()) {
            $this->_hlp->aHlp()->switchSession(AppArea::AREA_FRONTEND, null);
            $vSession = $this->_hlp->session();
            $isVendor = $this->_hlp->session()->isLoggedIn();
            if (!$isVendor) {
                $this->deleteVendorAdminCookie($vSession);
            }
            $this->_hlp->aHlp()->switchSession(AppArea::AREA_ADMINHTML, $oId, true);
            if (!$isVendor) {
                $this->deleteVendorAdminCookie($session);
            }
            if ($isVendor && $this->cookieManager->getCookie(self::VENDOR_ADMIN_COOKIE)) {
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setUrl($this->_frontUrl->getUrl('udropship/vendor/login'));
            }
        }
        return false;
    }

    public function hookBackendSessionStart($session)
    {
        /** @var \Magento\Backend\Model\Auth\Session $session */
        if ($this->cookieManager->getCookie(self::VENDOR_ADMIN_COOKIE)) {
            $_COOKIE[SessionAdminConfig::SESSION_NAME_ADMIN] = $this->cookieManager->getCookie(self::VENDOR_ADMIN_COOKIE);
        }
        return $this;
    }

    protected function getVendorAdminCookieMeta($session)
    {
        $sessionConfig = $this->_hlp->getObjectPrivateProperty($session, 'sessionConfig');
        $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
        $metadata->setPath($sessionConfig->getCookiePath());
        $metadata->setDomain($sessionConfig->getCookieDomain());
        $metadata->setDuration($sessionConfig->getCookieLifetime());
        $metadata->setSecure($sessionConfig->getCookieSecure());
        $metadata->setHttpOnly($sessionConfig->getCookieHttpOnly());
        return $metadata;
    }

    protected function createVendorAdminCookie($session, $adminSessionId)
    {
        $metadata = $this->getVendorAdminCookieMeta($session);
        $this->cookieManager->setPublicCookie(self::VENDOR_ADMIN_COOKIE, $adminSessionId, $metadata);
    }
    protected function deleteVendorAdminCookie($session)
    {
        $metadata = $this->getVendorAdminCookieMeta($session);
        $this->cookieManager->deleteCookie(self::VENDOR_ADMIN_COOKIE, $metadata);
    }
    /**
     * @return \Magento\Framework\App\State
     */
    protected function appState()
    {
        return $this->_hlp->getObj('Magento\Framework\App\State');
    }
}
