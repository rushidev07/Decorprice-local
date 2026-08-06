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

namespace Magetrend\Eop\Model\Mail\Template;

use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Mail\MessageInterface;

/**
 * Mail Template Transport Builder
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class ContactTransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * Message subject
     * @var string
     */
    private $subject = '';

    /**
     * Message Subject Setter
     * @param $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        if (!empty($subject)) {
            $this->subject = $subject;
        }
        return $this;
    }

    /**
     * Prepare message
     * @return $this
     */
    public function prepareMessage()
    {
        if (!$this->message) {
            return parent::prepareMessage();
        }

        $subject = $this->subject;
        $template = $this->getTemplate();
        if (empty($subject)) {
            $subject = $template->getSubject();
        }
        $types = [
            TemplateTypesInterface::TYPE_TEXT => MessageInterface::TYPE_TEXT,
            TemplateTypesInterface::TYPE_HTML => MessageInterface::TYPE_HTML,
        ];

        $body = $template->processTemplate();
        $this->message->setMessageType($types[$template->getType()])
            ->setBody($body)
            ->setSubject($subject);

        return $this;
    }

    /**
     * Set mail from address
     *
     * @param string|array $from
     * @return $this
     */
    public function setFrom($from)
    {
        if ($this->message == null) {
            return parent::setFrom($from);
        }

        $this->message->setFrom($from['email'], $from['name']);
        return $this;
    }
}
