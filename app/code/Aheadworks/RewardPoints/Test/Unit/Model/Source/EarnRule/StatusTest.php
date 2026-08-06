<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Source\EarnRule;

use Aheadworks\RewardPoints\Model\Source\EarnRule\Status;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\RewardPoints\Model\Source\EarnRule\Status
 */
class StatusTest extends TestCase
{
    /**
     * @var Status
     */
    private $source;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->source = $objectManager->getObject(Status::class, []);
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $this->assertTrue(is_array($this->source->toOptionArray()));
    }
}
