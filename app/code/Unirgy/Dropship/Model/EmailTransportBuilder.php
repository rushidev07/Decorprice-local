<?php

namespace Unirgy\Dropship\Model;

use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\App\ObjectManager;

class EmailTransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    public function udReset()
    {
        $this->reset();
        return $this;
    }
    protected function prepareMessage()
    {
        $variables = $this->templateVars;
        parent::prepareMessage();
        if (!empty($variables['_BCC'])) {
            $bcc = $variables['_BCC'];
            if (is_string($bcc)) {
                $bcc = explode(',', $bcc);
            }
            $this->uAddBcc($this, $bcc);
            unset($this->templateVars['_BCC']);
        }
        if (!empty($variables['_ATTACHMENTS'])) {
            foreach ((array)$variables['_ATTACHMENTS'] as $a) {
                if (is_string($a)) {
                    $a = ['filename'=>$a];
                }
                if (empty($a['content']) && (empty($a['filename']) || !is_readable($a['filename']))) {
                    throw new \Exception('Invalid attachment data: '.print_r($a, 1));
                }
                $this->uAddAttachment(
                    $this,
                    !empty($a['content']) ? $a['content'] : file_get_contents($a['filename']),
                    $a['filename'],
                    $a['type']
                );
            }
            unset($this->templateVars['_ATTACHMENTS']);
        }
        return $this;
    }

    public function uAddBcc($transportBuilder, $bcc)
    {
        $h = $this->_hlp();
        $uBcc = $h->getObjectPrivateProperty($transportBuilder, 'uBcc');
        if (!is_array($uBcc)) {
            $uBcc = [];
        }
        $uBcc = array_merge($uBcc, $bcc);
        $h->setObjectPrivateProperty($transportBuilder, 'uBcc', $uBcc);
        return $this;
    }

    public function uAddAttachment($transportBuilder, $attachmentData, $name, $type = \Zend_Mime::TYPE_TEXT)
    {
        $h = $this->_hlp();
        $attachment = $this->partFactory($attachmentData);
        $attachment->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
        $attachment->encoding = \Zend_Mime::ENCODING_BASE64;
        $attachment->filename = $name;
        $attachment->type = $type;
        $uAttachments = $h->getObjectPrivateProperty($transportBuilder, 'uAttachments');
        if (!is_array($uAttachments)) {
            $uAttachments = [];
        }
        $uAttachments[] = $attachment;
        $h->setObjectPrivateProperty($transportBuilder, 'uAttachments', $uAttachments);
        return $this;
    }
    /**
     * @return \Unirgy\Dropship\Helper\Data
     */
    protected function _hlp()
    {
        return ObjectManager::getInstance()->get('\Unirgy\Dropship\Helper\Data');
    }
    protected function partFactory($content)
    {
        if ($this->_hlp()->compareMageVer('2.2.8')) {
            return new \Zend\Mime\Part($content);
        } else {
            return new \Zend_Mime_Part($content);
        }
    }
}
