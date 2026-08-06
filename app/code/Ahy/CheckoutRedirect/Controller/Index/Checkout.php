<?php

namespace Ahy\CheckoutRedirect\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Model\Product;
use Magento\Framework\Controller\ResultFactory;

class Checkout extends Action
{
    protected $cart;
    protected $product;

    public function __construct(
        Context $context,
        Cart $cart,
        Product $product
    ) {
        $this->cart = $cart;
        $this->product = $product;
        parent::__construct($context);
    }

    public function execute()
    {
        $sku = $this->getRequest()->getParam('item_id'); // Get SKU from the URL parameter

        // Load the product based on the SKU
        $product = $this->product->loadByAttribute('sku', $sku);

        if ($product && $product->getId()) {
            try {
                // Add the product to the cart
                $params = [
                    'product' => $product->getId(),
                    'qty' => 1,
                ];
                $this->cart->addProduct($product, $params);
                $this->cart->save();

                // Redirect to the checkout page
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl($this->_url->getUrl('checkout')); // Redirect to checkout
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Error: %1', $e->getMessage()));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Product not found.'));
        }

        // Redirect back to the homepage or any other page as needed
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_url->getUrl('/'));
        return $resultRedirect;
    }
}
