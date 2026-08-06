<?php
namespace Aheadworks\RewardPoints\Model\Config\Backend;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRate as EarnRateResource;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRate\CollectionFactory as EarnRateCollectionFactory;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Serialize\SerializerInterface;
use Aheadworks\RewardPoints\Model\Validator\Config\Rates as RatesValidator;

/**
 * Class Aheadworks\RewardPoints\Model\Config\Backend\EarnRate
 */
class EarnRate extends \Magento\Framework\App\Config\Value
{
    /**
     * @var EarnRateResource
     */
    private $earnRateResource;

    /**
     * @var EarnRateCollectionFactory
     */
    private $earnRateCollectionFactory;

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
     * @param EarnRateResource $earnRateResource
     * @param EarnRateCollectionFactory $earnRateCollectionFactory
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
        EarnRateResource $earnRateResource,
        EarnRateCollectionFactory $earnRateCollectionFactory,
        SerializerInterface $serializer,
        RatesValidator $ratesValidator,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->earnRateResource = $earnRateResource;
        $this->earnRateCollectionFactory = $earnRateCollectionFactory;
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
        $this->setEarnRateValue($value);

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
        $rates = $this->getEarnRateValue();
        if ($this->ratesValidator->hasDuplicateValue($rates)) {
            throw new AlreadyExistsException(
                __('"Customer Lifetime Sales" values can\'t be the same for one group')
            );
        }
        $this->earnRateResource->saveConfigValue($rates);
        return parent::afterSave();
    }

    /**
     * {@inheritDoc}
     */
    protected function _afterLoad()
    {
        $collection = $this->earnRateCollectionFactory->create();
        $value = $collection->toConfigDataArray();
        $this->setValue($value);
    }
}
