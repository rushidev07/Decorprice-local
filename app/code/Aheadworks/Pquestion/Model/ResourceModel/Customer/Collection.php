<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Model\ResourceModel\Customer;

class Collection extends \Magento\Customer\Model\ResourceModel\Customer\Collection
{
    /**
     * @return void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addNameToSelect();
        $this
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left')
        ;
    }
}
