<?php
namespace Aheadworks\RewardPoints\Test\Unit\Ui\DataProvider\EarnRule;

use Aheadworks\RewardPoints\Ui\DataProvider\EarnRule\FormDataProvider;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Api\EarnRuleRepositoryInterface;
use Aheadworks\RewardPoints\Model\Data\ProcessorInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\RewardPoints\Ui\DataProvider\EarnRule\FormDataProvider
 */
class FormDataProviderTest extends TestCase
{
    /**
     * @var FormDataProvider
     */
    private $provider;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var DataPersistorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataPersistorMock;

    /**
     * @var DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectProcessorMock;

    /**
     * @var EarnRuleRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ruleRepositoryMock;

    /**
     * @var ProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataProcessorMock;

    /**
     * @var ProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metaProcessorMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->dataPersistorMock = $this->createMock(DataPersistorInterface::class);
        $this->dataObjectProcessorMock = $this->createMock(DataObjectProcessor::class);
        $this->ruleRepositoryMock = $this->createMock(EarnRuleRepositoryInterface::class);
        $this->dataProcessorMock = $this->createMock(ProcessorInterface::class);
        $this->metaProcessorMock = $this->createMock(ProcessorInterface::class);

        $this->provider = $objectManager->getObject(
            FormDataProvider::class,
            [
                'request' => $this->requestMock,
                'dataPersistor' => $this->dataPersistorMock,
                'dataObjectProcessor' => $this->dataObjectProcessorMock,
                'ruleRepository' => $this->ruleRepositoryMock,
                'dataProcessor' => $this->dataProcessorMock,
                'metaProcessor' => $this->metaProcessorMock
            ]
        );
    }

    /**
     * Test getData method
     */
    public function testGetData()
    {
        $requestFieldName = 'id';
        $ruleId = 10;
        $ruleData = [
            EarnRuleInterface::ID => $ruleId
        ];
        $preparedRuleData = [
            EarnRuleInterface::ID => 'prepared_rule_id',
        ];
        $resultData = [
            $ruleId => $preparedRuleData
        ];

        $this->setProperty('requestFieldName', $requestFieldName);

        $this->dataPersistorMock->expects($this->once())
            ->method('get')
            ->with(FormDataProvider::DATA_PERSISTOR_FORM_DATA_KEY)
            ->willReturn(null);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($requestFieldName)
            ->willReturn($ruleId);

        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $this->ruleRepositoryMock->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->willReturn($ruleMock);

        $this->dataObjectProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($ruleMock, EarnRuleInterface::class)
            ->willReturn($ruleData);

        $this->dataProcessorMock->expects($this->once())
            ->method('process')
            ->with($ruleData)
            ->willReturn($preparedRuleData);

        $this->assertSame($resultData, $this->provider->getData());
    }

    /**
     * Test getData method if no rule found
     */
    public function testGetDataNoRule()
    {
        $requestFieldName = 'id';
        $ruleId = 10;
        $resultData = [];

        $this->setProperty('requestFieldName', $requestFieldName);

        $this->dataPersistorMock->expects($this->once())
            ->method('get')
            ->with(FormDataProvider::DATA_PERSISTOR_FORM_DATA_KEY)
            ->willReturn(null);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($requestFieldName)
            ->willReturn($ruleId);

        $this->ruleRepositoryMock->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->willThrowException(new NoSuchEntityException(__('No such entity!')));

        $this->assertSame($resultData, $this->provider->getData());
    }

    /**
     * Test getData method if form data previously stored in the persistor
     */
    public function testGetDataStored()
    {
        $requestFieldName = 'id';
        $ruleId = 10;
        $savedData = [
            EarnRuleInterface::ID => $ruleId
        ];

        $this->setProperty('requestFieldName', $requestFieldName);

        $this->dataPersistorMock->expects($this->once())
            ->method('get')
            ->with(FormDataProvider::DATA_PERSISTOR_FORM_DATA_KEY)
            ->willReturn($savedData);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($requestFieldName)
            ->willReturn($ruleId);

        $this->dataPersistorMock->expects($this->once())
            ->method('clear')
            ->with(FormDataProvider::DATA_PERSISTOR_FORM_DATA_KEY)
            ->willReturn(null);

        $this->assertSame($savedData, $this->provider->getData());
    }

    /**
     * Test getMeta method
     */
    public function testGetMeta()
    {
        $metadata = [
            'meta' => 'data'
        ];
        $preparedMetadata = [
            'meta' => 'data',
            'additional' => 'data'
        ];

        $this->setProperty('meta', $metadata);

        $this->metaProcessorMock->expects($this->once())
            ->method('process')
            ->with($metadata)
            ->willReturn($preparedMetadata);

        $this->assertSame($preparedMetadata, $this->provider->getMeta());
    }

    /**
     * Set property
     *
     * @param string $propertyName
     * @param mixed $value
     * @return mixed
     * @throws \ReflectionException
     */
    private function setProperty($propertyName, $value)
    {
        $class = new \ReflectionClass($this->provider);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($this->provider, $value);

        return $this;
    }
}
