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

namespace Ced\Houzz\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class ProductValidation
 */
class ProductStatus extends Column
{
    /**
     * @var UrlInterface
     */
    public $urlBuilder;


    /**
     * Json Parser
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $json;

    /**
     * Product Model
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $product;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Magento\Framework\Json\Helper\Data $json,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        $components = [],
        $data = []
    ) {
        $this->product = $productFactory;
        $this->urlBuilder = $urlBuilder;
        $this->json = $json;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');

            foreach ($dataSource['data']['items'] as &$item) {
                try {
                    if (isset($item[$fieldName])) {
                        $childStatus = array();
                        $product = $this->product->create()->load($item['entity_id']);
                        if ($product->getTypeId() == 'configurable') {
                            $houzzStatus = $product->getHouzzProductStatus();
                            /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productType */
                            $productType = $product->getTypeInstance();
                            $chProducts = $productType->getUsedProducts($product);
                            foreach ($chProducts as $chProduct) {
                                $childProduct = $this->product->create()->load($chProduct->getId());
                                $chStatus = $childProduct->getHouzzProductStatus();
                                $childStatus[$childProduct->getSku()] = ($chStatus != '') ? $chStatus : 'Not Uploaded';
                            }
                            $statusCounts = array_count_values($childStatus);
                            $item[$fieldName . '_html'] = '';
                            foreach ($statusCounts as $status => $statusCount) {
                                $item[$fieldName . '_html'] .= '<div class="grid-severity-notice"><span>' . $status . ' ( ' . $statusCount .  ' )' . '</span></div></br>';
                            }
                            $childStatus = $this->json->jsonEncode($childStatus);
                            $item[$fieldName . '_productstatus'] = $childStatus;
                        } else {
                            $houzzStatus = $product->getHouzzProductStatus();
                            $item[$fieldName . '_html'] = '<div class="grid-severity-notice"><span>
                            ' . $houzzStatus . '</span></div>';
                        }
                    }
                } catch (\Exception $e) {
                }
            }
        }
        return $dataSource;
    }
}
