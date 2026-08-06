<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Model\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;

class Checker
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Check customer existence by email
     *
     * @param string $email
     * @return bool
     */
    public function checkCustomerExistByEmail($email)
    {
        try {
            $this->customerRepository->get($email);
        } catch (\Exception $exception) {
            return false;
        }

        return true;
    }
}
