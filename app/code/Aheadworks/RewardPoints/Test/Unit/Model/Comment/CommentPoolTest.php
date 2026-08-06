<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Comment;

use Aheadworks\RewardPoints\Model\Comment\CommentPool;
use Aheadworks\RewardPoints\Model\Comment\CommentInterface;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\RewardPoints\Model\Source\Transaction\Type as TransactionType;

/**
 * Class Aheadworks\RewardPoints\Test\Unit\Model\Comment\CommentPoolTest
 */
class CommentPoolTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CommentPool
     */
    private $object;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ObjectManagerInterface
     */
    private $objectManagerMock;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var array
     */
    private $comments = [];

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMockForAbstractClass();

        $this->data = [
            'objectManager' => $this->objectManagerMock,
        ];

        $this->comments = [
            'default' => CommentInterface::class,
            'comment_for_purchases' => CommentInterface::class,
        ];
    }

    /**
     * Init object
     */
    private function initCommentPool()
    {
        $this->data['comments'] = $this->comments;
        $this->object = $this->objectManager->getObject(CommentPool::class, $this->data);
    }

    /**
     * Tests construct for logic exception
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Default comment should be provided.
     */
    public function testConstructLogicException()
    {
        $this->data['comments'] = [];
        $this->expectException(\LogicException::class);
        $this->objectManager->getObject(CommentPool::class, $this->data);
    }

    /**
     * Test construct
     */
    public function testConstruct()
    {
        $this->initCommentPool();
        $ref = new \ReflectionClass($this->object);

        $prop = $ref->getProperty('objectManager');
        $prop->setAccessible(true);
        $value = $prop->getValue($this->object);
        $prop->setAccessible(false);

        $propComments = $ref->getProperty('comments');
        $propComments->setAccessible(true);
        $valueComments = $propComments->getValue($this->object);
        $propComments->setAccessible(false);

        $this->assertTrue($this->comments == $valueComments);
        $this->assertTrue($this->objectManagerMock == $value);
    }

    /**
     * Tests get method, retrieve comment for purchases instance
     */
    public function testGetMethodRetrievCommentForPurchasesInstance()
    {
        $this->initCommentPool();

        $commentInstanceMock = $this->getMockForAbstractClass(
            CommentInterface::class,
            ['getType'],
            '',
            false
        );
        $commentInstanceMock->expects($this->exactly(2))
            ->method('getType')
            ->willReturn(TransactionType::POINTS_REWARDED_FOR_ORDER);

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(CommentInterface::class)
            ->willReturn($commentInstanceMock);

        $this->assertSame($commentInstanceMock, $this->object->get(TransactionType::POINTS_REWARDED_FOR_ORDER));
        //test cache instance
        $this->assertSame($commentInstanceMock, $this->object->get(TransactionType::POINTS_REWARDED_FOR_ORDER));
    }

    /**
     * Tests get method, retrieve comment for specific comment
     */
    public function testGetMethodRetrievCommentForPurchasesSpecificComment()
    {
        $this->initCommentPool();

        $commentDefaultInstanceMock = $this->getMockForAbstractClass(
            CommentInterface::class,
            [],
            '',
            false
        );

        $commentForPurchaseInstanceMock = $this->getMockForAbstractClass(
            CommentInterface::class,
            ['getType'],
            '',
            false
        );
        $commentForPurchaseInstanceMock->expects($this->once())
            ->method('getType')
            ->willReturn(TransactionType::POINTS_REWARDED_FOR_ORDER);

        $this->objectManagerMock->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [CommentInterface::class],
                [CommentInterface::class]
            )
            ->willReturnOnConsecutiveCalls(
                $commentDefaultInstanceMock,
                $commentForPurchaseInstanceMock
            );

        $this->assertSame(
            $commentForPurchaseInstanceMock,
            $this->object->get(TransactionType::POINTS_REWARDED_FOR_ORDER)
        );
    }

    /**
     * Tests get method, retrieve default instance
     */
    public function testGetMethodRetrievDefaultInstance()
    {
        $this->initCommentPool();

        $commentDefaultInstanceMock = $this->getMockForAbstractClass(
            CommentInterface::class,
            [],
            '',
            false
        );

        $commentDefaultInstanceMock->expects($this->any())
            ->method('getType')
            ->willReturn('default');

        $this->objectManagerMock->expects($this->exactly(2))
            ->method('get')
            ->with(CommentInterface::class)
            ->willReturn($commentDefaultInstanceMock);

        $this->assertSame($commentDefaultInstanceMock, $this->object->get('test_comment'));
    }
}
