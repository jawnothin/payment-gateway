<?php

namespace Augwa\PaymentGateway\Response;

use Augwa\PaymentGateway\Exception;
use Augwa\PaymentGateway\Helper;
use Augwa\PaymentGateway\Enum;

/**
 * Class CreateCharge
 * @package Augwa\PaymentGateway\Response
 */
class CreateCharge extends Response
{

    /** @var Helper\Transaction */
    protected $transaction;

    /** @var Helper\CreditCard */
    protected $creditCard;

    /** @var string */
    protected $referenceNumber;

    /**
     * @param Helper\Transaction $transaction
     *
     * @return $this
     */
    public function setTransaction(Helper\Transaction $transaction)
    {
        $this->transaction = $transaction;
        return $this;
    }

    /**
     * @return Helper\Transaction
     * @throws Exception\MissingDataException
     */
    public function getTransaction()
    {
        if ($this->transaction === null)
        {
            throw new Exception\MissingDataException('Transaction not set');
        }
        return $this->transaction;
    }

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

    /**
     * @param string $referenceNumber
     *
     * @return $this
     */
    public function setReferenceNumber($referenceNumber)
    {
        $this->referenceNumber = $referenceNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getReferenceNumber()
    {
        return $this->referenceNumber;
    }

    protected function fetch() {
        $this->api->createCharge($this->getCreditCard(), $this->getTransaction());
    }

    protected function postResponseAction()
    {
        $this->getTransaction()->setReferenceNumber($this->getReferenceNumber());
    }

}