<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Helper;

class Request extends \Magento\Framework\App\Helper\AbstractHelper
{
    const URL_PARAM_KEY = 'aw_pq_answer';

    /** @var \Magento\Framework\Encryption\EncryptorInterface  */
    protected $_encryptor;

    /**
     * @var \Magento\UrlRewrite\Model\UrlRewrite
     */
    protected $_urlRewrite;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\UrlRewrite\Model\UrlRewrite $urlRewrite
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\UrlRewrite\Model\UrlRewrite $urlRewrite,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_encryptor = $encryptor;
        $this->_urlRewrite = $urlRewrite;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return array|mixed
     */
    public function getPopupData()
    {
        if (!$requestParamValue = $this->getParamValue()) {
            return [];
        }
        return $this->decode($requestParamValue);
    }

    /**
     * @param mixed $questionId
     * @param mixed $customerName
     * @param mixed $customerEmail
     * @param null $customerId
     * @return string
     */
    public function generateUrlParam($questionId, $customerName, $customerEmail, $customerId = null)
    {
        $params = [
            'question_id'    => $questionId,
            'customer_name'  => $customerName,
            'customer_email' => $customerEmail,
        ];
        if ($customerId !== null) {
            $params['customer_id'] = $customerId;
        }
        return $this->encode($params);
    }

    /**
     * @param mixed $data
     * @return string
     */
    public function encode($data)
    {
        return base64_encode(
            $this->_encryptor->encrypt(\Zend_Json::encode($data))
        );
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public function decode($data)
    {
        return \Zend_Json::decode(
            $this->_encryptor->decrypt(base64_decode($data))
        );
    }

    /**
     * @return mixed
     */
    public function getParamValue()
    {
        return $this->_request->getParam(self::URL_PARAM_KEY, false);
    }

    /**
     * @param mixed $question
     * @param mixed $customerName
     * @param mixed $customerEmail
     * @param null $customerId
     * @return string
     */
    public function getEmailProductUrl($question, $customerName, $customerEmail, $customerId = null)
    {
        $_key = $this->generateUrlParam(
            $question->getId(),
            $customerName,
            $customerEmail,
            $customerId
        );
        $_productUrl = $question->getProduct()->getProductUrl();
        if (false !== strpos($_productUrl, '?')) {
            $_productUrl .= '&' . self::URL_PARAM_KEY . '=' . $_key;
        } else {
            $_productUrl .= '?' . self::URL_PARAM_KEY . '=' . $_key;
        }
        return $_productUrl;
    }

    /**
     * @return int
     */
    public function getRewriteProductId()
    {
        $pathInfo = trim($this->_request->getOriginalPathInfo(), '/');
        $this->_urlRewrite->setStoreId(
            $this->_storeManager->getStore()->getId()
        );
        $this->_urlRewrite->load($pathInfo, 'request_path');
        if ($this->_urlRewrite->getProductId()) {
            return $this->_urlRewrite->getProductId();
        }
        return -1;
    }
}
