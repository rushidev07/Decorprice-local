<?php
namespace Aheadworks\RewardPoints\Test\Unit\Block\Adminhtml\EarnRule\Edit\Conditions\Form;

use Aheadworks\RewardPoints\Block\Adminhtml\EarnRule\Edit\Conditions\Form\DataProvider;
use Aheadworks\RewardPoints\Api\Data\ConditionInterface;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\Edit as RuleEditAction;
use Aheadworks\RewardPoints\Model\EarnRule\Condition\Rule\Loader as ConditionRuleLoader;
use Aheadworks\RewardPoints\Model\EarnRule\Condition\Rule as ConditionRule;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Registry;

/**
 * Test for \Aheadworks\RewardPoints\Block\Adminhtml\EarnRule\Edit\Conditions\Form\DataProvider
 */
class DataProviderTest extends TestCase
{
    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * @var Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registryMock;

    /**
     * @var ConditionRuleLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $conditionRuleLoaderMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->registryMock = $this->createMock(Registry::class);
        $this->conditionRuleLoaderMock = $this->createMock(ConditionRuleLoader::class);

        $this->dataProvider = $objectManager->getObject(
            DataProvider::class,
            [
                'registry' => $this->registryMock,
                'conditionRuleLoader' => $this->conditionRuleLoaderMock,
            ]
        );
    }

    /**
     * Test getConditionRule method
     */
    public function testGetConditionRule()
    {
        $conditionMock = $this->createMock(ConditionInterface::class);
        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $ruleMock->expects($this->once())
            ->method('getCondition')
            ->willReturn($conditionMock);

        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with(RuleEditAction::CURRENT_RULE_KEY)
            ->willReturn($ruleMock);

        $conditionRuleMock = $this->createMock(ConditionRule::class);
        $this->conditionRuleLoaderMock->expects($this->once())
            ->method('loadRule')
            ->with($conditionMock)
            ->willReturn($conditionRuleMock);

        $this->assertSame($conditionRuleMock, $this->dataProvider->getConditionRule());
    }

    /**
     * Test getConditionRule method if no rule in registry
     */
    public function testGetConditionRuleNoRule()
    {
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with(RuleEditAction::CURRENT_RULE_KEY)
            ->willReturn(null);

        $conditionRuleMock = $this->createMock(ConditionRule::class);
        $this->conditionRuleLoaderMock->expects($this->once())
            ->method('loadRule')
            ->with(null)
            ->willReturn($conditionRuleMock);

        $this->assertSame($conditionRuleMock, $this->dataProvider->getConditionRule());
    }
}
