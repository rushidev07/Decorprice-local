<?php
namespace Aheadworks\RewardPoints\Test\Unit\Controller\Adminhtml\Earning\Rules;

use Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\MassEnable;
use Aheadworks\RewardPoints\Api\EarnRuleManagementInterface;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRule\Collection as EarnRuleCollection;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRule\CollectionFactory as EarnRuleCollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Test for \Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\MassEnable
 */
class MassEnableTest extends TestCase
{
    /**
     * @var MassEnable
     */
    private $controller;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterMock;

    /**
     * @var EarnRuleCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ruleCollectionFactoryMock;

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

        $this->resultRedirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);

        $this->contextMock = $objectManager->getObject(
            Context::class,
            [
                'messageManager' => $this->messageManagerMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock,
            ]
        );

        $this->filterMock = $this->createMock(Filter::class);
        $this->ruleCollectionFactoryMock = $this->createMock(EarnRuleCollectionFactory::class);
        $this->ruleManagementMock = $this->createMock(EarnRuleManagementInterface::class);

        $this->controller = $objectManager->getObject(
            MassEnable::class,
            [
                'context' => $this->contextMock,
                'filter' => $this->filterMock,
                'ruleCollectionFactory' => $this->ruleCollectionFactoryMock,
                'ruleManagement' => $this->ruleManagementMock
            ]
        );
    }

    /**
     * Test execute
     */
    public function testExecute()
    {
        $ruleIds = [10, 11];
        $ruleCount = 2;

        $collectionMock = $this->createMock(EarnRuleCollection::class);
        $collectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn($ruleIds);
        $this->ruleCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($collectionMock)
            ->willReturn($collectionMock);

        $this->ruleManagementMock->expects($this->exactly(2))
            ->method('enable')
            ->withConsecutive([10], [11])
            ->willReturn(true);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('A total of %1 rule(s) were enabled.', $ruleCount))
            ->willReturnSelf();

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
     * Test execute method if no rule found
     */
    public function testExecuteNoRuleFound()
    {
        $ruleIds = [10, 11];
        $errorMessage = 'No such entity!';

        $collectionMock = $this->createMock(EarnRuleCollection::class);
        $collectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn($ruleIds);
        $this->ruleCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($collectionMock)
            ->willReturn($collectionMock);

        $this->ruleManagementMock->expects($this->once())
            ->method('enable')
            ->with(10)
            ->willThrowException(new NoSuchEntityException(__($errorMessage)));

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($errorMessage)
            ->willReturnSelf();

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
     * Test execute method if rule can not be saved
     */
    public function testExecuteSaveError()
    {
        $ruleIds = [10, 11];
        $errorMessage = 'Rule can not be saved!';

        $collectionMock = $this->createMock(EarnRuleCollection::class);
        $collectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn($ruleIds);
        $this->ruleCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($collectionMock)
            ->willReturn($collectionMock);

        $this->ruleManagementMock->expects($this->once())
            ->method('enable')
            ->with(10)
            ->willThrowException(new CouldNotSaveException(__($errorMessage)));

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($errorMessage)
            ->willReturnSelf();

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
}
