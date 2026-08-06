<?php
namespace Aheadworks\RewardPoints\Model\Calculator\Spending\Validator\Product;

use Magento\Framework\Validator\AbstractValidator;
use Aheadworks\RewardPoints\Model\CategoryAllowed as CategoryAllowedModel;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem as QuoteAbstractItem;

/**
 * Class CategoryAllowed
 *
 * @package Aheadworks\RewardPoints\Model\Calculator\Spending\Validator\Product
 */
class CategoryAllowed extends AbstractValidator
{
    /**
     * @var CategoryAllowedModel
     */
    private $categoryAllowed;

    /**
     * @param CategoryAllowedModel $categoryAllowed
     */
    public function __construct(
        CategoryAllowedModel $categoryAllowed
    ) {
        $this->categoryAllowed = $categoryAllowed;
    }

    /**
     * Returns true if and only if quote item entity meets the validation requirements
     *
     * @param CartItemInterface|QuoteAbstractItem $quoteItem
     * @return bool
     */
    public function isValid($quoteItem)
    {
        $this->_clearMessages();

        $categoryIds = $quoteItem->getProduct()->getCategoryIds();
        if (!$this->categoryAllowed->isAllowedCategoryForSpendPoints($categoryIds)) {
            $this->_addMessages(
                [
                    'Category of quote item product isn\'t allowed for points applying'
                ]
            );
            return false;
        }

        return true;
    }
}
