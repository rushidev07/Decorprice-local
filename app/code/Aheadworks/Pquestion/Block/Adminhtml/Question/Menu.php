<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Block\Adminhtml\Question;

class Menu extends \Magento\Backend\Block\Template
{
    const ITEM_PRODUCTQUESTION = 'productquestion';
    const ITEM_BY_PRODUCT = 'by_product';
    const ITEM_SUPPORT = 'support';
    const ITEM_SETTINGS = 'settings';
    const ITEM_README = 'readme';

    /**
     * @var null
     */
    protected $_currentItemKey = null;

    /**
     * @return array
     */
    public function getItems()
    {
        return [
            self::ITEM_PRODUCTQUESTION => [
                'title' => __('Manage Questions'),
                'url' => $this->getUrl('productquestion/question/index')
            ],
            self::ITEM_BY_PRODUCT => [
                'title' => __('By Product'),
                'url' => $this->getUrl('productquestion/product/index')
            ],
            self::ITEM_SETTINGS => [
                'title' => __('Settings'),
                'url' => $this->getUrl('adminhtml/system_config/edit', ['section' => 'aw_pquestion'])
            ],
            self::ITEM_README => [
                'title' => __('Readme'),
                'url' => 'https://aheadworks.atlassian.net/'
                    . 'wiki/spaces/EUDOC/pages/1229227019/Product+Questions+-+Magento+2',
                'target' => '__blank',
                'class' => 'aw_get_support_menu_item'
            ],
            self::ITEM_SUPPORT => [
                'title' => __('Get Support'),
                'url' => ' http://ecommerce.aheadworks.com/contacts/',
                'target' => '__blank',
            ],
        ];
    }

    /**
     * @return string
     */
    public function getCurrentItemKey()
    {
        return $this->_currentItemKey;
    }

    /**
     * @param string $key
     *
     * @return $this
     */
    public function setCurrentItemKey($key)
    {
        $this->_currentItemKey = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentItemTitle()
    {
        $items = $this->getItems();
        $key = $this->getCurrentItemKey();
        if (!array_key_exists($key, $items)) {
            return '';
        }
        return $items[$key]['title'];
    }
}
