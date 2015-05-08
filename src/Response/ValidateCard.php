<?php

namespace Augwa\PaymentGateway\Response;

use Augwa\PaymentGateway\Exception;
use Augwa\PaymentGateway\Helper;
use Augwa\PaymentGateway\Enum;

/**
 * Class ValidateCard
 * @package Augwa\PaymentGateway\Response
 */
class ValidateCard extends Response
{

    /** @var Helper\CreditCard */
    protected $creditCard;

    /**
     * @param Helper\CreditCard $creditCard
     *
     * @return $this
     */
    public function setCreditCard(Helper\CreditCard $creditCard)
    {
        $this->creditCard = $creditCard;
        return $this;
    }

    /**
     * @return Helper\CreditCard
     * @throws Exception\MissingDataException
     */
    public function getCreditCard()
    {
        if ($this->creditCard === null)
        {
            throw new Exception\MissingDataException('Credit Card not set');
        }
        return $this->creditCard;
    }

    protected function fetch()
    {
        $this->api->validateCard($this->getCreditCard());
    }

    protected function postResponseAction()
    {
        // do nothing
    }

}