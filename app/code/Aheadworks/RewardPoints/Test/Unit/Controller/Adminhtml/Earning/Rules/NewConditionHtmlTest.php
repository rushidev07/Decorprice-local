<?php
namespace Aheadworks\RewardPoints\Test\Unit\Controller\Adminhtml\Earning\Rules;

use Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\NewConditionHtml;
use Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\Condition\Factory as RuleConditionFactory;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\PhpEnvironment\Response;
use Magento\CatalogRule\Model\Rule\Condition\Product as ProductCondition;

/**
 * Test for \Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\NewConditionHtml
 */
class NewConditionHtmlTest extends TestCase
{
    /**
     * @var NewConditionHtml
     */
    private $controller;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var Response|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var RuleConditionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $conditionFactoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->responseMock = $this->createMock(Response::class);

        $this->contextMock = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'response' => $this->responseMock,
            ]
        );

        $this->conditionFactoryMock = $this->createMock(RuleConditionFactory::class);

        $this->controller = $objectManager->getObject(
            NewConditionHtml::class,
            [
                'context' => $this->contextMock,
                'conditionFactory' => $this->conditionFactoryMock,
            ]
        );
    }

    /**
     * Test execute
     *
     * @param bool $exception
     * @dataProvider executeDataProvider
     */
    public function testExecute($exception)
    {
        $id = '1--2';
        $prefix = 'conditions';
        $type = ProductCondition::class;
        $attribute = 'category_ids';
        $formNamespace = 'aw_rp_rule_form';
        $jsFormObject = 'rule_conditions_fieldset';
        $typeParam = $type . '|' . $attribute;
        $html = '<div>Condtition content</div>';

        $requestParams = [
            ['id', null, $id],
            ['prefix',  NewConditionHtml::DEFAULT_CONDITIONS_PREFIX, $prefix],
            ['type',  null, $typeParam],
            ['form_namespace',  null, $formNamespace],
            ['form',  null, $jsFormObject]
        ];
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->will($this->returnValueMap($requestParams));

        $conditionModelMock = $this->createMock(ProductCondition::class);

        if ($exception) {
            $conditionModelMock->expects($this->once())
                ->method('asHtmlRecursive')
                ->willThrowException(new \Exception('Condition must be instance of AbstractCondition'));
        $html = '';
        } else {
            $conditionModelMock->expects($this->once())
                ->method('asHtmlRecursive')
                ->willReturn($html);
        }

        $this->conditionFactoryMock->expects($this->once())
            ->method('create')
            ->with($type, $id, $prefix, $attribute, $jsFormObject, $formNamespace)
            ->willReturn($conditionModelMock);

        $this->responseMock->expects($this->once())
            ->method('setBody')
            ->with($html)
            ->willReturnSelf();

        $this->assertNull($this->controller->execute());
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            ['exception' => false],
            ['exception' => true],
        ];
    }
}
