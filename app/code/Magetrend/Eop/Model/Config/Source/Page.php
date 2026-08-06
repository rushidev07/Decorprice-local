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
 * Places source
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Page implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\CollectionFactory
     */
    public $collectionFactory;

    /**
     * Page constructor.
     * @param \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $collectionFactory
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

        $values = [
            'all'   => __('All Pages'),
            'product_page'     => __('Product Page'),
            'category_page'     => __('Category Page'),
            's_product_page'    => __('Specific Product Page'),
            's_category_page'   => __('Specific Category Page'),
            'cart_page'    => __('Cart'),
            'checkout_page'    => __('Checkout')
        ];

        $collection = $this->collectionFactory->create();
        if ($collection->getSize() > 0) {
            foreach ($collection as $item) {
                $values['cms_page_'.$item->getId()] = $item->getTitle();
            }
        }

        return $values;
    }
}
