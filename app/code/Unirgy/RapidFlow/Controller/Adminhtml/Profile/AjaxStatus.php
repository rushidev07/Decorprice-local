<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Helper\Data as HelperData;
use Magento\Framework\View\LayoutFactory;
use Unirgy\RapidFlow\Model\Profile;
use Unirgy\RapidFlow\Model\ResourceModel\Profile as ProfileResource;

class AjaxStatus extends AbstractProfile
{
    /**
     * @var LayoutFactory
     */
    protected $_layoutFactory;

    public function __construct(Context $context,
                                Profile $profile,
                                HelperData $catalogHelper,
                                ProfileResource $profileResource,
                                LayoutFactory $layoutFactory)
    {
        $this->_layoutFactory = $layoutFactory;

        parent::__construct($context, $profile, $catalogHelper, $profileResource);
    }

    public function execute()
    {
        $profile = $this->_getProfile();

        $_resultHtml = $this->_layoutFactory->create()
            ->createBlock('Unirgy\RapidFlow\Block\Adminhtml\Profile\Status')
            ->setProfile($profile)
            ->toHtml();

        $_result = [
            'run_status' => $profile->getRunStatus(),
            'html' => $_resultHtml
        ];

        $result = @json_encode($_result);

        if ($result === false) {
            $_result['html'] = utf8_encode($_result['html']);
            $result = @json_encode($_result);
        }

        $this->getResponse()->representJson($result);
    }
}
