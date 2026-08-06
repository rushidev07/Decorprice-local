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
 * Popup source
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Popup implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magetrend\Eop\Model\ResourceModel\Popup\CollectionFactory
     */
    public $collectionFactory;

    /**
     * Popup constructor.
     * @param \Magetrend\Eop\Model\ResourceModel\Popup\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magetrend\Eop\Model\ResourceModel\Popup\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->toArray();
        $optionsArray = [];
        foreach ($options as $value => $label) {
            $optionsArray[] = ['value' => $value,  'label' => $label];
        }

        return $optionsArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $dataArray = [
            '' => __('-- Please Select --')
        ];
        $collection = $this->collectionFactory->create();
        if ($collection->getSize() > 0) {
            foreach ($collection as $item) {
                $dataArray[$item->getId()] = $item->getName();
            }
        }

        return $dataArray;
    }
}
