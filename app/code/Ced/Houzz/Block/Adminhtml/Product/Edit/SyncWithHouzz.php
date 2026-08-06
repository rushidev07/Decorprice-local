<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Houzz
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Houzz\Block\Adminhtml\Product\Edit;
/**
 * Class AddAttribute
 */
class SyncWithHouzz extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic
{

    /**
     * @return array
     */
    public function getButtonData()
    {
        $productId= $this->getProduct()->getId();
        return [
            'label' => __('Sync With Houzz'),
            'class' => 'action-secondary',
            'on_click' => sprintf("location.href = '%s';",
                $this->getUrl('houzz/products/sync/id/'. $productId)),
            'sort_order' => 10
        ];
    }
}
