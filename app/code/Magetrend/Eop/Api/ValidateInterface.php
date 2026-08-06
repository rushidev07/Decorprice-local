<?php

namespace Magetrend\Eop\Api;

/**
 * Interface to check conditions
 * @api
 */
interface ValidateInterface
{
    /**
     * @param int $campaignId
     * @return mixed
     */
    public function canShowPopup($campaignId);
}