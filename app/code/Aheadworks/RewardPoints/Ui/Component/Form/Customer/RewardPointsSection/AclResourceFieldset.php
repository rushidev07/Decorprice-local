<?php
namespace Aheadworks\RewardPoints\Ui\Component\Form\Customer\RewardPointsSection;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\ComponentVisibilityInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Form\Fieldset as UiFieldset;

/**
 * Class AclResourceFieldset
 *
 * @package Aheadworks\RewardPoints\Ui\Component\Form\Customer\RewardPointsSection
 */
class AclResourceFieldset extends UiFieldset implements ComponentVisibilityInterface
{
    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * @param ContextInterface $context
     * @param AuthorizationInterface $authorization
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        AuthorizationInterface $authorization,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->authorization = $authorization;
    }

    /**
     * Retrieve acl resource for the fieldset
     *
     * @return string
     */
    public function getAclResource()
    {
        return (string)$this->getData('acl_resource');
    }

    /**
     * @inheridoc
     */
    public function isComponentVisible(): bool
    {
        return $this->authorization->isAllowed($this->getAclResource());
    }

    /**
     * @inheridoc
     */
    public function prepare()
    {
        parent::prepare();

        $config = (array)$this->getData('config');
        $config['componentDisabled'] = !$this->isComponentVisible();
        $this->setData('config', $config);
    }
}
