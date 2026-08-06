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

namespace Magetrend\Eop\Model\Config\Source;

/**
 * Email template source
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class EmailTemplate implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Email\Model\ResourceModel\Template\CollectionFactory|null
     */
    private $templateCollection = null;

    /**
     * EmailTemplate constructor.
     * @param \Magento\Email\Model\ResourceModel\Template\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $collectionFactory
    ) {
        $this->templateCollection = $collectionFactory;
    }

    /**
     * Options getter
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->toArray();
        $optionArray = [];
        foreach ($options as $value => $label) {
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $collection = $this->templateCollection->create();
        $templateList = [
            'exit_offer_email_template_request' => __('Exit Offer Popup Request (Default)')
        ];
        if ($collection->getSize() > 0) {
            foreach ($collection as $template) {
                $templateList[$template->getId()] = $template->getTemplateCode();
            }
        }
        return $templateList;
    }
}
