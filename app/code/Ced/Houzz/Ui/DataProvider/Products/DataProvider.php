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
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Houzz\Ui\DataProvider\Products;

use Magento\Backend\App\Action\Context;
/**
 * Class DataProvider for Houzz Products
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Product Collection
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public $collection;

    /**
     * Add Field Strategies
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    public $addFieldStrategies;

    /**
     * Add Filter Strategies
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    public $addFilterStrategies;

    /**
     * Filter Builder
     * @var \Magento\Framework\Api\FilterBuilder
     */
    public $filterBuilder;

    /**
     * Object Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Request Params
     */
    public $params;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Ced\Houzz\Model\ResourceModel\Profile\CollectionFactory $profilecollection
     * @param Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Ced\Houzz\Model\ResourceModel\Profile\CollectionFactory $profilecollection,
        Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        $addFieldStrategies = [],
        $addFilterStrategies = [],
        $meta = [],
        $data = []

    ) {
       parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->objectManager = $context->getObjectManager();//$objectManager;
        $this->filterBuilder = $filterBuilder;
        $this->addFieldStrategies = $addFieldStrategies;
        $this->profilecollection=$profilecollection;
        $this->addFilterStrategies = $addFilterStrategies;
        $this->collection = $collectionFactory->create();
        $_collection = $collectionFactory->create();
        $profileId = $objectManager->get('Magento\Framework\App\RequestInterface')->getParam('profile_id');
        $profiledat=$this->profilecollection->create();
        $profileIds=[];
        $profile=$profiledat->addFieldToFilter('profile_status' , 1)->getData();
         if(isset($profile) && !empty($profile)) {
             foreach ($profile as $key => $val) {
                 $profileIds[] = $val['id'];
             }
         }
        $cond = null;
        if($profileId) {
                $bookmar_coll = $objectManager->create('Magento\Ui\Model\Bookmark')->getCollection()
                    ->addFieldToFilter('namespace',['eq' => 'houzz_products_index']);
                foreach($bookmar_coll as $bookmark){
                    $bookmark_model = $objectManager->create('Magento\Ui\Model\Bookmark')
                        ->load($bookmark->getBookmarkId());
                    if($bookmark_model->getIdentifier() == 'current'){
                        $config_value = $bookmark_model->getConfig();
                        $config_value['current']['filters']['applied']['profile_id'] = $profileId;
                        $bookmark_model->setConfig(json_encode($config_value));
                        $bookmark_model->save();
                    }
            }
            $cond = "profile_id =".$profileId;
        }
        $_collection->joinField(
            'profile_id',
            'houzz_profile_products',
            'profile_id',
            'product_id = entity_id',
            $cond
        );

        $this->collection  = $_collection;
        $this->collection->joinField('qty', 'cataloginventory_stock_item', 'qty', 'product_id = entity_id', '{{table}}.stock_id=1', null);
        $ids = array_unique($_collection->getAllIds());
        $this->addField('houzz_product_status');
        $this->addField('houzz_product_validation');

        $this->addFilter($this->filterBuilder->setField('entity_id')->setConditionType('in')
            ->setValue($ids)
            ->create());
        $this->addFilter($this->filterBuilder->setField('type_id')->setConditionType('in')
            ->setValue(['simple', 'configurable'])
            ->create());
        $this->addFilter($this->filterBuilder->setField('visibility')->setConditionType('nin')
            ->setValue([1])
            ->create());

        $this->addFilter($this->filterBuilder->setField('profile_id')->setConditionType('in')
            ->setValue($profileIds)
            ->create());
        $this->params = $context->getRequest()->getParams();

    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        $collection = $this->getCollection();
        $items = $collection->toArray();
        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if (isset($this->addFilterStrategies[$filter->getField()])) {
            $this->addFilterStrategies[$filter->getField()]
                ->addFilter(
                    $this->getCollection(),
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
        } else {
            parent::addFilter($filter);
        }
    }

    /**
     * Add field to select
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        } else {
            parent::addField($field, $alias);
        }
    }
}

