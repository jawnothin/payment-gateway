<?php

namespace Augwa\PaymentGateway\Response;

use Augwa\PaymentGateway\Helper;
use Augwa\PaymentGateway\Exception;
use Augwa\PaymentGateway\Enum;

/**
 * Class CreateChargeSaved
 * @package Augwa\PaymentGateway\Response
 */
class CreateChargeSaved extends Response
{

    /** @var Helper\Transaction */
    protected $transaction;

    /** @var string */
    protected $billingProfile;

    /** @var string */
    protected $referenceNumber;

    /** @var \DateTime */
    protected $billingProfileExpiryDate;

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
     * @param \DateTime $billingProfileExpiryDate
     *
     * @return $this
     */
    public function setBillingProfileExpiryDate(\DateTime $billingProfileExpiryDate)
    {
        $this->billingProfileExpiryDate = $billingProfileExpiryDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getBillingProfileExpiryDate()
    {
        return $this->billingProfileExpiryDate;
    }

    /**
     * @param string $billingProfile
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
     */
    public function getBillingProfile()
    {
        return $this->billingProfile;
    }

    /**
     * @param $referenceNumber
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

    protected function fetch()
    {
        $this->api->createChargeSaved($this->getTransaction());
    }

    protected function postResponseAction()
    {
        $this->getTransaction()->setReferenceNumber($this->getReferenceNumber());
    }

}