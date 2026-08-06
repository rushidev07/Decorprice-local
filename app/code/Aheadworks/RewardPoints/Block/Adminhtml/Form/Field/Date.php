<?php
namespace Aheadworks\RewardPoints\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class Date
 * @package Aheadworks\RewardPoints\Block\Adminhtml\Form\Field
 */
class Date extends Field
{
    /**
     * @inheritDoc
     */
    public function render(AbstractElement $element)
    {
        $element->setDateFormat(DateTime::DATE_INTERNAL_FORMAT);

        return parent::render($element);
    }
}
