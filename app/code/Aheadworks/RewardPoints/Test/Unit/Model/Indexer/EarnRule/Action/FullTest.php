<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Indexer\EarnRule\Action;

use Aheadworks\RewardPoints\Model\Indexer\EarnRule\Action\Full as FullIndexer;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRule\Indexer\Product as EarnRuleProductIndexerResource;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\RewardPoints\Model\Indexer\EarnRule\Action\Full
 */
class FullTest extends TestCase
{
    /**
     * @var FullIndexer
     */
    private $indexer;

    /**
     * @var EarnRuleProductIndexerResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $earnRuleProductIndexerResourceMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->earnRuleProductIndexerResourceMock = $this->createMock(EarnRuleProductIndexerResource::class);

        $this->indexer = $objectManager->getObject(
            FullIndexer::class,
            [
                'earnRuleProductIndexerResource' => $this->earnRuleProductIndexerResourceMock,
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $this->earnRuleProductIndexerResourceMock->expects($this->once())
            ->method('reindexAll')
            ->willReturnSelf();

        $this->assertNull($this->indexer->execute());
    }

    /**
     * Test execute method if incorrect ids specified
     *
     * @param array|null $rowIds
     * @dataProvider executeIncorrectIdsDataProvider
     */
    public function testExecuteIncorrectIds($rowIds)
    {
        $this->earnRuleProductIndexerResourceMock->expects($this->once())
            ->method('reindexAll')
            ->willReturnSelf();

        $this->assertNull($this->indexer->execute($rowIds));
    }

    /**
     * @return array
     */
    public function executeIncorrectIdsDataProvider()
    {
        return [
            ['rowIds' => null],
            ['rowIds' => []],
            ['rowId' => 0]
        ];
    }

    /**
     * Test execute method if an error occurs
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Error!
     */
    public function testExecuteError()
    {
        $errorMessage = 'Error!';

        $this->earnRuleProductIndexerResourceMock->expects($this->once())
            ->method('reindexAll')
            ->willThrowException(new \Exception($errorMessage));
$this->expectException(\Exception::class);
        $this->indexer->execute();
    }
}
