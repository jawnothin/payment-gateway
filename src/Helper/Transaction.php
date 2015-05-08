<?php

namespace Augwa\PaymentGateway\Helper;

/**
 * Class Transaction
 * @package Augwa\PaymentGateway\Helper
 */
class Transaction {

    /** @var string */
    protected $transactionId;

    /** @var null|Transaction */
    protected $parentTransaction;

    /** @var float */
    protected $amount = 0.00;

    /** @var string */
    protected $comment;

    /** @var \DateTime */
    protected $transactionDate;

    /** @var string */
    protected $referenceNumber;

    /** @var string */
    protected $currencyCode;

    /** @var int */
    protected $status = 0;

    /** @var string */
    protected $billingProfile;

    /** @var string */
    protected $apiResponse;

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     *
     * @return $this
     */
    public function setTransactionId($transactionId = null)
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    /**
     * @return null|Transaction
     */
    public function getParentTransaction()
    {
        return $this->parentTransaction;
    }

    /**
     * @param Transaction $parentTransaction
     *
     * @return $this
     */
    public function setParentTransaction(Transaction $parentTransaction = null)
    {
        $this->parentTransaction = $parentTransaction;
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
     * @param float $amount
     *
     * @return $this
     */
    public function setAmount($amount = null)
    {
        $this->amount = (float)$amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     *
     * @return $this
     */
    public function setComment($comment = null)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * dateCreated getter
     *
     * @return \DateTime
     */
    public function getTransactionDate()
    {
        return $this->transactionDate;
    }

    /**
     * @param \DateTime $transactionDate
     *
     * @return $this
     */
    public function setTransactionDate(\DateTime $transactionDate = null)
    {
        $this->transactionDate = $transactionDate;
        return $this;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function getApiResponse($key = null)
    {
        if ($key === null) {
            return $this->apiResponse;
        } else {
            return is_array($this->apiResponse) && array_key_exists($key, $this->apiResponse) ? $this->apiResponse[$key] : null;
        }
    }

    /**
     * @param array $apiResponse
     *
     * @return $this
     */
    public function setApiResponse(array $apiResponse = null)
    {
        $this->apiResponse = $apiResponse;
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
     * @param string $referenceNumber
     *
     * @return $this
     */
    public function setReferenceNumber($referenceNumber = null)
    {
        $this->referenceNumber = $referenceNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @param string $currencyCode
     *
     * @return $this
     */
    public function setCurrency($currencyCode = null)
    {
        $this->currencyCode = $currencyCode;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     *
     * @return $this
     */
    public function setStatus($status = null)
    {
        $this->status = (int)$status;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingProfile()
    {
        return $this->billingProfile;
    }

    /**
     * @param string $billingProfile
     *
     * @return $this
     */
    public function setBillingProfile($billingProfile = null)
    {
        $this->billingProfile = $billingProfile;
        return $this;
    }


}