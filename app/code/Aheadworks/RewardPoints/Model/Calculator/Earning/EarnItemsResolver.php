<?php
namespace Aheadworks\RewardPoints\Model\Calculator\Earning;

use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemProcessor;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor\CreditmemoProcessor;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor\InvoiceProcessor;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor\QuoteProcessor;
use Magento\Catalog\Model\Product;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\InvoiceInterface;

/**
 * Class EarnItemsResolver
 * @package Aheadworks\RewardPoints\Model\Calculator\Earning
 */
class EarnItemsResolver
{
    /**
     * @var QuoteProcessor
     */
    private $quoteProcessor;

    /**
     * @var InvoiceProcessor
     */
    private $invoiceProcessor;

    /**
     * @var CreditmemoProcessor
     */
    private $creditmemoProcessor;

    /**
     * @var ProductProcessor
     */
    private $productProcessor;

    /**
     * @var ItemProcessor
     */
    private $itemProcessor;

    /**
     * @param QuoteProcessor $quoteProcessor
     * @param InvoiceProcessor $invoiceProcessor
     * @param CreditmemoProcessor $creditmemoProcessor
     * @param ProductProcessor $productProcessor
     * @param ItemProcessor $itemProcessor
     */
    public function __construct(
        QuoteProcessor $quoteProcessor,
        InvoiceProcessor $invoiceProcessor,
        CreditmemoProcessor $creditmemoProcessor,
        ProductProcessor $productProcessor,
        ItemProcessor $itemProcessor
    ) {
        $this->quoteProcessor = $quoteProcessor;
        $this->invoiceProcessor = $invoiceProcessor;
        $this->creditmemoProcessor = $creditmemoProcessor;
        $this->productProcessor = $productProcessor;
        $this->itemProcessor = $itemProcessor;
    }

    /**
     * Get earn items from quote
     *
     * @param CartInterface|Quote $quote
     * @param bool $beforeTax
     * @return EarnItemInterface[]
     * @throws \Exception
     */
    public function getItemsByQuote($quote, $beforeTax = true)
    {
        $itemGroups = $this->quoteProcessor->getItemGroups($quote);
        $earnItems = $this->getEarnItems($itemGroups, $beforeTax);

        return $earnItems;
    }

    /**
     * Get earn items from invoice
     *
     * @param InvoiceInterface $invoice
     * @param bool $beforeTax
     * @return EarnItemInterface[]
     * @throws \Exception
     */
    public function getItemsByInvoice($invoice, $beforeTax = true)
    {
        $itemGroups = $this->invoiceProcessor->getItemGroups($invoice);
        $earnItems = $this->getEarnItems($itemGroups, $beforeTax);

        return $earnItems;
    }

    /**
     * Get earn items from creditmemo
     *
     * @param CreditmemoInterface $creditmemo
     * @param bool $beforeTax
     * @return EarnItemInterface[]
     * @throws \Exception
     */
    public function getItemsByCreditmemo($creditmemo, $beforeTax = true)
    {
        $itemGroups = $this->creditmemoProcessor->getItemGroups($creditmemo);
        $earnItems = $this->getEarnItems($itemGroups, $beforeTax);

        return $earnItems;
    }

    /**
     * Get earn items
     *
     * @param array $itemGroups
     * @param $beforeTax
     * @return EarnItemInterface[]
     * @throws \Exception
     */
    private function getEarnItems($itemGroups, $beforeTax)
    {
        $earnItems = [];
        foreach ($itemGroups as $itemGroup) {
            $earnItems[] = $this->itemProcessor->getEarnItem($itemGroup, $beforeTax);
        }
        return $earnItems;
    }

    /**
     * Get earn items by product
     *
     * @param Product $product
     * @param bool $beforeTax
     * @return EarnItemInterface[]
     * @throws \Exception
     */
    public function getItemsByProduct($product, $beforeTax = true)
    {
        return $this->productProcessor->getEarnItems($product, $beforeTax);
    }
}
