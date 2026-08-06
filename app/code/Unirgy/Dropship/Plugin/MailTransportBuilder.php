<?php

namespace Unirgy\Dropship\Plugin;

class MailTransportBuilder
{
    protected $_hlp;
    public function __construct(
        \Unirgy\Dropship\Helper\Data $ugiftcertHelper
    )
    {
        $this->_hlp = $ugiftcertHelper;
    }
    public function afterGetTransport(\Magento\Framework\Mail\Template\TransportBuilder $subject, $result)
    {
        $message = $this->uTransportMessage($result);
        $uAttachments = $this->_hlp->getObjectPrivateProperty($subject, 'uAttachments');
        $uBcc = $this->_hlp->getObjectPrivateProperty($subject, 'uBcc');
        $this->uProcessExtras($message, $uAttachments, $uBcc);
        $this->_hlp->setObjectPrivateProperty($subject, 'uAttachments', []);
        $this->_hlp->setObjectPrivateProperty($subject, 'uBcc', []);
        return $result;
    }
    public function uTransportMessage($mailTransport)
    {
        if (is_callable([$mailTransport, 'getMessage'])) {
            $message = $mailTransport->getMessage();
        } else {
            $message = $this->_hlp->getObjectPrivateProperty($mailTransport, 'message');
            if (!$message) {
                $message = $this->_hlp->getObjectPrivateProperty($mailTransport, '_message');
            }
        }
        return $message;
    }

    public function uProcessExtras($message, $attachments, $bcc)
    {
        if ($message instanceof \Zend_Mail) {
            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    $message->addPart($attachment);
                }
                $message->hasAttachments = true;
            }
            if (!empty($bcc)) {
                foreach ($bcc as $e) {
                    $message->addBcc($e);
                }
            }
        } else {
            $zendMessage = $this->_hlp->getObjectPrivateProperty($message, 'zendMessage');
            if (!$zendMessage) {
                $zendMessage = $this->_hlp->getObjectPrivateProperty($message, 'message');
            }
            if (is_a($zendMessage, '\Zend\Mail\Message')
                || is_subclass_of($zendMessage, '\Zend\Mail\Message')
            ) {
                if (!empty($attachments)) {
                    $body = $zendMessage->getBody();
                    foreach ($attachments as $attachment) {
                        $body->addPart($attachment);
                    }
                    $zendMessage->setBody($body);
                }
                if (!empty($bcc)) {
                    foreach ($bcc as $e) {
                        $zendMessage->addBcc($e);
                    }
                }
            }
        }
        return $this;
    }
}