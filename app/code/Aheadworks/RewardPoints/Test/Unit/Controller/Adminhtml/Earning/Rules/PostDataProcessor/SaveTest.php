<?php
namespace Aheadworks\RewardPoints\Test\Unit\Controller\Adminhtml\Earning\Rules;

use Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\Save;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Api\EarnRuleManagementInterface;
use Aheadworks\RewardPoints\Model\Data\ProcessorInterface;
use Aheadworks\RewardPoints\Ui\DataProvider\EarnRule\FormDataProvider;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Test for \Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\Save
 */
class SaveTest extends TestCase
{
    /**
     * @var Save
     */
    private $controller;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var DataPersistorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataPersistorMock;

    /**
     * @var ProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $postDataProcessorMock;

    /**
     * @var EarnRuleManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ruleManagementMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->createMock(Http::class);
        $this->resultRedirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);

        $this->contextMock = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'messageManager' => $this->messageManagerMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock,
            ]
        );

        $this->dataPersistorMock = $this->createMock(DataPersistorInterface::class);
        $this->postDataProcessorMock = $this->createMock(ProcessorInterface::class);
        $this->ruleManagementMock = $this->createMock(EarnRuleManagementInterface::class);

        $this->controller = $objectManager->getObject(
            Save::class,
            [
                'context' => $this->contextMock,
                'dataPersistor' => $this->dataPersistorMock,
                'postDataProcessor' => $this->postDataProcessorMock,
                'ruleManagement' => $this->ruleManagementMock,
            ]
        );
    }

    /**
     * Test execute
     *
     * @param int|null $ruleId
     * @param string|null $backParam
     * @dataProvider executeDataProvider
     */
    public function testExecute($ruleId, $backParam)
    {
        $postData = [
            EarnRuleInterface::ID => $ruleId,
            'data' => 'data'
        ];
        $preparedData = [
            EarnRuleInterface::ID => $ruleId,
            'data' => 'prepared_data'
        ];

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($postData);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('back')
            ->willReturn($backParam);

        $this->postDataProcessorMock->expects($this->once())
            ->method('process')
            ->with($postData)
            ->willReturn($preparedData);

        $ruleMock = $this->createMock(EarnRuleInterface::class);
        if ($ruleId) {
            $this->ruleManagementMock->expects($this->once())
                ->method('updateRule')
                ->with($ruleId, $preparedData)
                ->willReturn($ruleMock);
        } else {
            $this->ruleManagementMock->expects($this->once())
                ->method('createRule')
                ->with($preparedData)
                ->willReturn($ruleMock);
        }

        $this->dataPersistorMock->expects($this->once())
            ->method('clear')
            ->with(FormDataProvider::DATA_PERSISTOR_FORM_DATA_KEY)
            ->willReturn(null);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('Rule was saved successfully'))
            ->willReturnSelf();

        $redirectMock = $this->createMock(Redirect::class);

        if ($backParam == 'edit') {
            $ruleMock->expects($this->once())
                ->method('getId')
                ->willReturn($ruleId);

            $redirectMock->expects($this->once())
                ->method('setPath')
                ->with('*/*/edit', ['id' => $ruleId])
                ->willReturnSelf();
        } else {
            $redirectMock->expects($this->once())
                ->method('setPath')
                ->with('*/*/')
                ->willReturnSelf();
        }

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($redirectMock);

        $this->assertSame($redirectMock, $this->controller->execute());
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            ['ruleId' => 10, 'backParam' => null],
            ['ruleId' => 10, 'backParam' => 'edit'],
            ['ruleId' => null, 'backParam' => null],
            ['ruleId' => null, 'backParam' => 'edit'],
        ];
    }

    /**
     * Test execute if no post data
     */
    public function testExecuteNoPostData()
    {
        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn(null);

        $redirectMock = $this->createMock(Redirect::class);
        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($redirectMock);

        $this->assertSame($redirectMock, $this->controller->execute());
    }

    /**
     * Test execute if save error occurs
     *
     * @param int|null $ruleId
     * @dataProvider executeSaveErrorDataProvider
     */
    public function testExecuteSaveError($ruleId)
    {
        $postData = [
            EarnRuleInterface::ID => $ruleId,
            'data' => 'data'
        ];
        $preparedData = [
            EarnRuleInterface::ID => $ruleId,
            'data' => 'prepared_data'
        ];
        $errorMessage = 'Error!';

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($postData);

        $this->postDataProcessorMock->expects($this->once())
            ->method('process')
            ->with($postData)
            ->willReturn($preparedData);

        if ($ruleId) {
            $this->ruleManagementMock->expects($this->once())
                ->method('updateRule')
                ->with($ruleId, $preparedData)
                ->willThrowException(new CouldNotSaveException(__($errorMessage)));

        } else {
            $this->ruleManagementMock->expects($this->once())
                ->method('createRule')
                ->with($preparedData)
                ->willThrowException(new CouldNotSaveException(__($errorMessage)));
        }

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__($errorMessage))
            ->willReturnSelf();

        $this->dataPersistorMock->expects($this->once())
            ->method('set')
            ->with(FormDataProvider::DATA_PERSISTOR_FORM_DATA_KEY)
            ->willReturn($postData);

        $redirectMock = $this->createMock(Redirect::class);

        if ($ruleId) {
            $redirectMock->expects($this->once())
                ->method('setPath')
                ->with('*/*/edit', ['id' => $ruleId, '_current' => true])
                ->willReturnSelf();
        } else {
            $redirectMock->expects($this->once())
                ->method('setPath')
                ->with('*/*/new', ['_current' => true])
                ->willReturnSelf();
        }

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($redirectMock);

        $this->assertSame($redirectMock, $this->controller->execute());
    }

    /**
     * @return array
     */
    public function executeSaveErrorDataProvider()
    {
        return [
            ['ruleId' => 10],
            ['ruleId' => null]
        ];
    }
}
