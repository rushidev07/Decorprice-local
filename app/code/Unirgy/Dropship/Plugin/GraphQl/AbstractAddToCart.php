<?php

namespace Unirgy\Dropship\Plugin\GraphQl;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Unirgy\Dropship\Helper\Data as uDropshipHelper;

class AbstractAddToCart
{
    /**
     * @var uDropshipHelper
     */
    protected $_hlp;

    public function __construct(
        uDropshipHelper $uDropshipHelper
    )
    {
        $this->_hlp = $uDropshipHelper;
    }

    public function beforeResolve(ResolverInterface $subject)
    {
        $this->_hlp->iHlp()->setIsCartUpdateActionFlag(true);
    }
}
