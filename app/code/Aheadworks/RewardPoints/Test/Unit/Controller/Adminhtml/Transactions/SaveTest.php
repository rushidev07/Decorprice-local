<?php
namespace Aheadworks\RewardPoints\Test\Unit\Controller\Adminhtml\Transactions;

use Aheadworks\RewardPoints\Controller\Adminhtml\Transactions\Save;
use Aheadworks\RewardPoints\Model\Data\CommandInterface;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class Aheadworks\RewardPoints\Test\Unit\Controller\Adminhtml\Transactions\SaveTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Save
     */
    private $object;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RequestHttp
     */
    private $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|MessageManagerInterface
     */
    private $messageManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RedirectFactory
     */
    private $resultRedirectFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Redirect
     */
    private $resultRedirectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DataPersistorInterface
     */
    private $dataPersistorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CommandInterface
     */
    private $createCommandMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(RequestHttp::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPostValue'])
            ->getMock();

        $this->messageManagerMock = $this->getMockBuilder(MessageManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['addSuccessMessage'])
            ->getMockForAbstractClass();

        $this->resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath'])
            ->getMock();

        $this->dataPersistorMock = $this->getMockBuilder(DataPersistorInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['set', 'clear'])
            ->getMockForAbstractClass();

        $this->createCommandMock = $this->createMock(CommandInterface::class);

        $this->context = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock,
                'messageManager' => $this->messageManagerMock,
            ]
        );

        $data = [
            'context' => $this->context,
            'dataPersistor' => $this->dataPersistorMock,
            'createCommand' => $this->createCommandMock
        ];

        $this->object = $objectManager->getObject(Save::class, $data);
    }

    /**
     * Test execute method for null POST
     */
    public function testExecuteMethodEmptyPost()
    {
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn([]);

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->object->execute();
    }

    /**
     * Test execute method
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteMethod()
    {
        $postData = [
            'website_id' => 1,
            'balance' => 50,
            'comment_to_customer' => 'comment to customer',
            'comment_to_admin' => 'comment to admin',
            'expire_in_days' => 10,
            'expire' => 'expire_in_x_days',
            'expiration_date' => '',
            'customer_selections' => json_encode([
                [
                    'customer_id' => 1,
                    'customer_name' => 'Veronica Costello',
                    'customer_email' => 'roni_cost@example.com',
                ],
            ])
        ];
        $preparedData = [
            'website_id' => 1,
            'balance' => 50,
            'comment_to_customer' => 'comment to customer',
            'comment_to_admin' => 'comment to admin',
            'expire_in_days' => 10,
            'expire' => 'expire_in_x_days',
            'expiration_date' => '',
            'customer_selections' => [
                [
                    'customer_id' => 1,
                    'customer_name' => 'Veronica Costello',
                    'customer_email' => 'roni_cost@example.com',
                ],
            ]
        ];

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($postData);

        $this->dataPersistorMock->expects($this->once())
            ->method('set')
            ->with('transaction', $preparedData)
            ->willReturnSelf();

        $this->createCommandMock->expects($this->once())
            ->method('execute')
            ->with($preparedData)
            ->willReturn(true);

        $this->dataPersistorMock->expects($this->once())
            ->method('clear')
            ->willReturnSelf();

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('You saved the transactions.')
            ->willReturnSelf();

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->object->execute();
    }
}
