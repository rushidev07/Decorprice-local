<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Indexer;

use Aheadworks\RewardPoints\Model\Indexer\EarnRule as EarnRuleIndexer;
use Aheadworks\RewardPoints\Model\Indexer\EarnRule\Action\Row as ActionRow;
use Aheadworks\RewardPoints\Model\Indexer\EarnRule\Action\Rows as ActionRows;
use Aheadworks\RewardPoints\Model\Indexer\EarnRule\Action\Full as ActionFull;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\RewardPoints\Model\Indexer\EarnRule
 */
class EarnRuleTest extends TestCase
{
    /**
     * @var EarnRuleIndexer
     */
    private $indexer;

    /**
     * @var ActionRow|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ruleIndexerRowMock;

    /**
     * @var ActionRows|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ruleIndexerRowsMock;

    /**
     * @var ActionFull|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ruleIndexerFullMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->ruleIndexerRowMock = $this->createMock(ActionRow::class);
        $this->ruleIndexerRowsMock = $this->createMock(ActionRows::class);
        $this->ruleIndexerFullMock = $this->createMock(ActionFull::class);

        $this->indexer = $objectManager->getObject(
            EarnRuleIndexer::class,
            [
                'ruleIndexerRow' => $this->ruleIndexerRowMock,
                'ruleIndexerRows' => $this->ruleIndexerRowsMock,
                'ruleIndexerFull' => $this->ruleIndexerFullMock,
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $rowIds = [125, 126, 127];

        $this->ruleIndexerRowsMock->expects($this->once())
            ->method('execute')
            ->with($rowIds)
            ->willReturn(null);

        $this->assertNull($this->indexer->execute($rowIds));
    }

    /**
     * Test execute method if an empty row ids specified
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Bad value was supplied.
     */
    public function testExecuteEmpty()
    {
        $rowIds = [];

        $this->ruleIndexerRowsMock->expects($this->once())
            ->method('execute')
            ->with($rowIds)
            ->willThrowException(new \Exception('Bad value was supplied.'));

        $this->expectException(\Exception::class);
        $this->indexer->execute($rowIds);
    }

    /**
     * Test execute method if an error occurs
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Error!
     */
    public function testExecuteError()
    {
        $rowIds = [125, 126, 127];

        $this->ruleIndexerRowsMock->expects($this->once())
            ->method('execute')
            ->with($rowIds)
            ->willThrowException(new LocalizedException(__('Error!')));

$this->expectException(LocalizedException::class);
$this->indexer->execute($rowIds);
    }

    /**
     * Test executeFull method
     */
    public function testExecuteFull()
    {
        $this->ruleIndexerFullMock->expects($this->once())
            ->method('execute')
            ->willReturn(null);

        $this->assertNull($this->indexer->executeFull());
    }

    /**
     * Test executeFull method if an error occurs
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Error!
     */
    public function testExecuteFullError()
    {
        $this->ruleIndexerFullMock->expects($this->once())
            ->method('execute')
            ->willThrowException(new LocalizedException(__('Error!')));
        $this->expectException(LocalizedException::class);
        $this->indexer->executeFull();
    }

    /**
     * Test executeList method
     */
    public function testExecuteList()
    {
        $rowIds = [125, 126, 127];

        $this->ruleIndexerRowsMock->expects($this->once())
            ->method('execute')
            ->with($rowIds)
            ->willReturn(null);

        $this->assertNull($this->indexer->executeList($rowIds));
    }

    /**
     * Test executeList method if an empty row ids specified
     *
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Bad value was supplied.
     */
    public function testExecuteListEmpty()
    {
        $rowIds = [];

        $this->ruleIndexerRowsMock->expects($this->once())
            ->method('execute')
            ->with($rowIds)
            ->willThrowException(new InputException(__('Bad value was supplied.')));
        $this->expectException(InputException::class);
        $this->indexer->executeList($rowIds);
    }

    /**
     * Test executeList method if an error occurs
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Error!
     */
    public function testExecuteListError()
    {
        $rowIds = [125, 126, 127];

        $this->ruleIndexerRowsMock->expects($this->once())
            ->method('execute')
            ->with($rowIds)
            ->willThrowException(new LocalizedException(__('Error!')));
        $this->expectException(LocalizedException::class);
        $this->indexer->executeList($rowIds);
    }

    /**
     * Test executeRow method
     */
    public function testExecuteRow()
    {
        $rowId = 125;

        $this->ruleIndexerRowMock->expects($this->once())
            ->method('execute')
            ->with($rowId)
            ->willReturn(null);

        $this->assertNull($this->indexer->executeRow($rowId));
    }

    /**
     * Test executeRow method if an empty row id specified
     *
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage We can't rebuild the index for an undefined entity.
     */
    public function testExecuteRowEmpty()
    {
        $rowId = null;

        $this->ruleIndexerRowMock->expects($this->once())
            ->method('execute')
            ->with($rowId)
            ->willThrowException(new InputException(__('We can\'t rebuild the index for an undefined entity.')));
        $this->expectException(InputException::class);
        $this->indexer->executeRow($rowId);
    }

    /**
     * Test executeRow method if an error occurs
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Error!
     */
    public function testExecuteRowError()
    {
        $rowId = 125;

        $this->ruleIndexerRowMock->expects($this->once())
            ->method('execute')
            ->with($rowId)
            ->willThrowException(new LocalizedException(__('Error!')));
        $this->expectException(LocalizedException::class);
        $this->indexer->executeRow($rowId);
    }
}
