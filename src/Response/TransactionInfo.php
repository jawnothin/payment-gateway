<?php

namespace Augwa\PaymentGateway\Response;

use Augwa\PaymentGateway\Exception;
use Augwa\PaymentGateway\Helper;
use Augwa\PaymentGateway\Enum;

/**
 * Class TransactionInfo
 * @package Augwa\PaymentGateway\Response
 */
class TransactionInfo extends Response
{

    /** @var Helper\Transaction */
    protected $transaction;

    /** @var Helper\CreditCard */
    protected $creditCard;

    /** @var string */
    protected $referenceNumber;

    /** @var \DateTime */
    protected $date;

    /** @var float */
    protected $amount;

    /** @var string */
    protected $operation;

    /** @var bool */
    protected $voided;

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
     * @param string $operation
     *
     * @return $this
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;
        return $this;
    }

    /**
     * @return string
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * @param float $amount
     *
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = (float)$amount;
        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
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

    /**
     * @param \DateTime $datetime
     *
     * @return $this
     */
    public function setDate(\DateTime $datetime)
    {
        $this->date = $datetime;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param bool $voided
     *
     * @return $this
     */
    public function setVoided($voided)
    {
        $this->voided = (bool)$voided;
        return $this;
    }

    /**
     * @return bool
     */
    public function getVoided()
    {
        return $this->voided;
    }

    protected function fetch()
    {
        $this->api->transactionInfo($this->getTransaction());
    }

    protected function postResponseAction()
    {
        // do nothing
    }

}