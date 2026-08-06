<?php
namespace Aheadworks\RewardPoints\Model\Config\Backend;

use Aheadworks\RewardPoints\Model\Validator\Config\Rates as RatesValidator;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Aheadworks\RewardPoints\Model\ResourceModel\SpendRate as SpendRateResource;
use Aheadworks\RewardPoints\Model\ResourceModel\SpendRate\CollectionFactory as SpendRateCollectionFactory;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class Aheadworks\RewardPoints\Model\Config\Backend\SpendRate
 */
class SpendRate extends \Magento\Framework\App\Config\Value
{
    /**
     * @var SpendRateResource
     */
    private $spendRateResource;

    /**
     * @var SpendRateCollectionFactory
     */
    private $spendRateCollectionFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var RatesValidator
     */
    private $ratesValidator;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param SpendRateResource $spendRateResource
     * @param SpendRateCollectionFactory $spendRateCollectionFactory
     * @param SerializerInterface $serializer
     * @param RatesValidator $ratesValidator
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        SpendRateResource $spendRateResource,
        SpendRateCollectionFactory $spendRateCollectionFactory,
        SerializerInterface $serializer,
        RatesValidator $ratesValidator,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->spendRateResource = $spendRateResource;
        $this->spendRateCollectionFactory = $spendRateCollectionFactory;
        $this->serializer = $serializer;
        $this->ratesValidator = $ratesValidator;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $this->setSpendRateValue($value);

        //to be able to check that the values have changed
        $this->setValue($this->serializer->serialize($value));
        return parent::beforeSave();
    }

    /**
     * {@inheritDoc}
     * @throws AlreadyExistsException
     * @throws \Exception
     */
    public function afterSave()
    {
        $rates = $this->getSpendRateValue();
        if ($this->ratesValidator->hasDuplicateValue($rates)) {
            throw new AlreadyExistsException(
                __('"Customer Lifetime Sales" values can\'t be the same for one group')
            );
        }
        $this->spendRateResource->saveConfigValue($rates);
        return parent::afterSave();
    }

    /**
     * {@inheritDoc}
     */
    protected function _afterLoad()
    {
        $collection = $this->spendRateCollectionFactory->create();
        $value = $collection->toConfigDataArray();
        $this->setValue($value);
    }
}
