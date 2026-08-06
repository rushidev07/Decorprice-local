<?php
namespace Aheadworks\RewardPoints\Ui\DataProvider\EarnRule;

use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Api\EarnRuleRepositoryInterface;
use Aheadworks\RewardPoints\Model\Data\ProcessorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\Filter;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class FormDataProvider
 * @package Aheadworks\RewardPoints\Ui\DataProvider\EarnRule
 */
class FormDataProvider extends AbstractDataProvider
{
    /**
     * Key for saving and getting form data from data persistor
     */
    const DATA_PERSISTOR_FORM_DATA_KEY = 'aw_reward_points_earn_rule';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var EarnRuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var ProcessorInterface
     */
    private $dataProcessor;

    /**
     * @var ProcessorInterface
     */
    private $metaProcessor;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param RequestInterface $request
     * @param DataPersistorInterface $dataPersistor
     * @param DataObjectProcessor $dataObjectProcessor
     * @param EarnRuleRepositoryInterface $ruleRepository
     * @param ProcessorInterface $dataProcessor
     * @param ProcessorInterface $metaProcessor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        DataPersistorInterface $dataPersistor,
        DataObjectProcessor $dataObjectProcessor,
        EarnRuleRepositoryInterface $ruleRepository,
        ProcessorInterface $dataProcessor,
        ProcessorInterface $metaProcessor,
        Logger $logger,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $request;
        $this->dataPersistor = $dataPersistor;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->ruleRepository = $ruleRepository;
        $this->dataProcessor = $dataProcessor;
        $this->metaProcessor = $metaProcessor;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $preparedData = [];
        $dataFromForm = $this->dataPersistor->get(self::DATA_PERSISTOR_FORM_DATA_KEY);
        $id = $this->request->getParam($this->getRequestFieldName());

        if (!empty($dataFromForm)) {
            $preparedData = $dataFromForm;
            $this->dataPersistor->clear(self::DATA_PERSISTOR_FORM_DATA_KEY);
        } elseif (!empty($id)) {
            try {
                $rule = $this->ruleRepository->get($id);
                $data = $this->dataObjectProcessor->buildOutputDataArray($rule, EarnRuleInterface::class);
                $preparedData[$id] = $this->dataProcessor->process($data);
            } catch (NoSuchEntityException $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }

        return $preparedData;
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        $meta = $this->metaProcessor->process($meta);

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(Filter $filter)
    {
        return $this;
    }
}
