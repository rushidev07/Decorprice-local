<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\EarnRule;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\RewardPoints\Model\EarnRule\Validator;
use Aheadworks\RewardPoints\Model\StorefrontLabelsEntity\Validator as StorefrontLabelsEntityValidator;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;

/**
 * Test for \Aheadworks\RewardPoints\Model\EarnRule\Validator
 */
class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var StorefrontLabelsEntityValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storefrontLabelsEntityValidatorMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->storefrontLabelsEntityValidatorMock = $this->createMock(
            StorefrontLabelsEntityValidator::class
        );

        $this->validator = $objectManager->getObject(
            Validator::class,
            [
                'storefrontLabelsEntityValidator' => $this->storefrontLabelsEntityValidatorMock
            ]
        );
    }

    /**
     * Test isValid method
     */
    public function testIsValidRuleValid()
    {
        $isValid = true;

        $earnRuleMock = $this->createMock(EarnRuleInterface::class);

        $this->storefrontLabelsEntityValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($earnRuleMock)
            ->willReturn($isValid);

        $this->storefrontLabelsEntityValidatorMock->expects($this->never())
            ->method('getMessages');

        $this->assertEquals($isValid, $this->validator->isValid($earnRuleMock));
    }

    /**
     * Test isValid method for invalid instance
     */
    public function testIsValidRuleInvalid()
    {
        $isValid = false;
        $messages = [];

        $earnRuleMock = $this->createMock(EarnRuleInterface::class);

        $this->storefrontLabelsEntityValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($earnRuleMock)
            ->willReturn($isValid);

        $this->storefrontLabelsEntityValidatorMock->expects($this->once())
            ->method('getMessages')
            ->willReturn($messages);

        $this->assertEquals($isValid, $this->validator->isValid($earnRuleMock));
    }
}
