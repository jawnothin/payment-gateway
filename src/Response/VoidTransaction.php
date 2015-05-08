<?php

namespace Augwa\PaymentGateway\Response;

use Augwa\PaymentGateway\Exception;
use Augwa\PaymentGateway\Helper;
use Augwa\PaymentGateway\Enum;

/**
 * Class VoidTransaction
 * @package Augwa\PaymentGateway\Response
 */
class VoidTransaction extends Response
{

    /** @var Helper\Transaction */
    protected $transaction;
    /**
     * @var string
     */
    protected $message;

    /**
     * @param Helper\Transaction $transaction
     *
     * @return VoidTransaction $this
     */
    public function setTransaction(Helper\Transaction $transaction) {
        $this->transaction = $transaction;
        return $this;
    }

    /**
     * @return Helper\Transaction
     * @throws Exception\MissingDataException
     */
    public function getTransaction() {

        if ($this->transaction === null) {
            throw new Exception\MissingDataException('Transaction not set');
        }
        return $this->transaction;
    }

    /**
     *
     */
    protected function fetch() {
        $this->api->voidTransaction($this->getTransaction());
    }

    protected function postResponseAction()
    {
        // do nothing
    }

}