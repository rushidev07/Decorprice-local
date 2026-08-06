<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    \Unirgy\RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\RapidFlow\Block\Adminhtml\Profile\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Helper\Data as HelperData;
use Magento\Framework\Registry;
use Unirgy\RapidFlow\Model\ProfileFactory;
use Unirgy\RapidFlow\Model\Profile\HistoryFactory;

class History extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var Registry
     */
    protected $_registry;

    protected $_profileFactory;

    protected $_historyFactory;

    public function __construct(
        Context $context,
        HelperData $backendHelper,
        Registry $registry,
        ProfileFactory $profileFactory,
        HistoryFactory $historyFactory,
        array $data = [])
    {
        $this->_registry = $registry;
        $this->_profileFactory = $profileFactory;
        $this->_historyFactory = $historyFactory;

        parent::__construct($context, $backendHelper, $data);
        $this->setId('urapidflow_history');
        $this->setDefaultSort('history_id');
        $this->setUseAjax(true);
    }

    protected $_profile;
    public function getProfile()
    {
        if ($this->_profile==null) {
            $profileData = $this->_registry->registry('profile_data');
            if (!$profileData) {
                $id = (int) $this->getRequest()->getParam('id');
                $this->_profile = $this->_profileFactory->create()->load($id);
            } else {
                $this->_profile = $profileData->getProfile();
            }
        }
        return $this->_profile;
    }
    public function setProfile($profile)
    {
        $this->_profile = $profile;
        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = $this->_historyFactory->create()->getCollection()
            ->addFieldToFilter('profile_id', $this->getProfile()->getId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('history_id', [
            'header'    => __('ID'),
            'index'     => 'history_id'
        ]);
        $this->addColumn('percent', [
            'header'    => __('Percent'),
            'index'     => 'percent',
            'format'    => '$percent%'
        ]);
        $this->addColumn('started_at', [
            'header'    => __('Started'),
            'index'     => 'started_at',
            'type'      => 'datetime',
            'format'    => \IntlDateFormatter::SHORT,
            'width'     => '300'
        ]);
        $this->addColumn('runtime_string', [
            'header'    => __('Running For'),
            'index'     => 'runtime_string'
        ]);
        $this->addColumn('crunch_rate_string', [
            'header'    => __('Crunch Rate'),
            'index'     => 'crunch_rate_string'
        ]);
        $this->addColumn('rows_found', [
            'header'    => __('Rows Found'),
            'index'     => 'rows_found'
        ]);
        $this->addColumn('rows_processed', [
            'header'    => __('Rows Processed'),
            'index'     => 'rows_processed'
        ]);
        $this->addColumn('rows_success', [
            'header'    => __('Rows Successful'),
            'index'     => 'rows_success'
        ]);
        $this->addColumn('rows_depends', [
            'header'    => __('Rows Depends'),
            'index'     => 'rows_depends'
        ]);
        $this->addColumn('rows_nochange', [
            'header'    => __('Rows Not Changed'),
            'index'     => 'rows_nochange'
        ]);
        $this->addColumn('rows_empty', [
            'header'    => __('Rows Empty/Comment'),
            'index'     => 'rows_empty'
        ]);
        $this->addColumn('rows_errors', [
            'header'    => __('Rows With Errors'),
            'index'     => 'rows_errors'
        ]);
        $this->addColumn('num_errors', [
            'header'    => __('Total Errors'),
            'index'     => 'num_errors'
        ]);
        $this->addColumn('num_warnings', [
            'header'    => __('Total Warnings'),
            'index'     => 'num_warnings'
        ]);
        $this->addColumn('memory_usage', [
            'header'    => __('Memory Used'),
            'index'     => 'memory_usage'
        ]);
        $this->addColumn('memory_peak_usage', [
            'header'    => __('Peak Memory Used'),
            'index'     => 'memory_peak_usage'
        ]);
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/historyGrid', ['_current'=>true]);
    }

    public function getTabLabel()
    {
        return $this->getData('label');
    }
    public function getTabTitle()
    {
        return $this->getData('title');
    }
    public function canShowTab()
    {
        return true;
    }
    public function isHidden()
    {
        return false;
    }
}
