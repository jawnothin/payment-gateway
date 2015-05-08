<?php

namespace Augwa\PaymentGateway\Response;

use Augwa\PaymentGateway\Exception;
use Augwa\PaymentGateway\Helper;
use Augwa\PaymentGateway\Enum;

/**
 * Class RemoveCard
 * @package Augwa\PaymentGateway\Response
 */
class RemoveCard extends Response
{

    /** @var string */
    protected $billingProfile;

    /**
     * @param $billingProfile
     *
     * @return $this
     */
    public function setBillingProfile($billingProfile)
    {
        $this->billingProfile = $billingProfile;
        return $this;
    }

    /**
     * @return string
     * @throws Exception\MissingDataException
     */
    public function getBillingProfile()
    {
        if ($this->billingProfile === null)
        {
            throw new Exception\MissingDataException('Billing Profile not set');
        }

        return $this->billingProfile;
    }

    protected function fetch()
    {
        $this->api->removeCard($this->getBillingProfile());
    }

    protected function postResponseAction()
    {
        // do nothing
    }

}