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

namespace Magetrend\Eop\Block\Adminhtml;

/**
 * Backend popup grid container block
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Popup extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var \Magetrend\Eop\Model\Config\Source\Type
     */
    public $contentType;

    /**
     * Popup constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magetrend\Eop\Model\Config\Source\Type $contentType
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magetrend\Eop\Model\Config\Source\Type $contentType,
        array $data = []
    ) {
        $this->contentType = $contentType;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    //@codingStandardsIgnoreLine
    protected function _construct()
    {
        $this->_controller = 'popup_index';
        $this->_headerText = __('Manage Exit Offer');
        parent::_construct();
    }

    /**
     * @inheritdoc
     */
    //@codingStandardsIgnoreLine
    protected function _prepareLayout()
    {
        $this->removeButton('add');
        $this->addButton('add', [
            'id' => 'add_new_blog_post',
            'label' => '&nbsp;'.__('Add New Popup').'&nbsp;&nbsp;&nbsp;&nbsp;',
            'class' => 'add',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->getAddButtonOptions(),
        ]);
        return parent::_prepareLayout();
    }

    /**
     * Returns add new button sub-list
     * @return array
     */
    public function getAddButtonOptions()
    {
        $splitButtonOptions = [];
        $options = $this->contentType->toArray();
        foreach ($options as $key => $value) {
            $splitButtonOptions[] = [
                'label' => __($value),
                'onclick' => "setLocation('" . $this->_getCreateUrl($key) . "')"
            ];
        }

        return $splitButtonOptions;
    }

    /**
     * Returns create new popup url
     *
     * @param $key
     * @return string
     */
    public function _getCreateUrl($key)
    {
        return $this->getUrl(
            'eop/*/new',
            ['content_type' => $key]
        );
    }
}
