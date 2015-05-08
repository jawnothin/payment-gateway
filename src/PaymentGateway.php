<?php

namespace Augwa\PaymentGateway;

use Augwa\PaymentGateway\Helper;
use Augwa\PaymentGateway\Exception;
use Augwa\PaymentGateway\Enum;

/**
 * Class PaymentGateway
 * @package Augwa\PaymentGateway
 */
abstract class PaymentGateway
{

    /** @var Integration\API\Core\AbstractApi */
    private $api = null;

    /** @var bool  */
    private $testMode = false;

    /**
     * @param Response\VoidTransaction $response
     */
    abstract protected function voidTransactionResponse(Response\VoidTransaction $response);

    /**
     * @param Response\ReturnTransaction $response
     */
    abstract protected function returnTransactionResponse(Response\ReturnTransaction $response);

    /**
     * @param Response\TransactionInfo $response
     */
    abstract protected function transactionInfoResponse(Response\TransactionInfo $response);

    /**
     * @param Response\CreateCharge $response
     */
    abstract protected function createChargeResponse(Response\CreateCharge $response);

    /**
     * @param Response\CreateChargeSaved $response
     */
    abstract protected function createChargeSavedResponse(Response\CreateChargeSaved $response);

    /**
     * @param Response\ValidateCard $response
     */
    abstract protected function validateCardResponse(Response\ValidateCard $response);

    /**
     * @param Response\SaveCard $response
     */
    abstract protected function saveCardResponse(Response\SaveCard $response);

    /**
     * @param Response\RemoveCard $response
     */
    abstract protected function removeCardResponse(Response\RemoveCard $response);

    /**
     * @param bool $testMode
     */
    function __construct($testMode = false)
    {
        $this->testMode = $testMode;
    }

    /**
     * @return bool
     */
    public function getTestMode()
    {
        return $this->testMode;
    }

    /**
     * @param Integration\API\Core\AbstractApi $api
     *
     * @return $this
     */
    protected function setApi(Integration\API\Core\AbstractApi $api)
    {
        $this->api = $api;
        return $this;
    }

    /**
     * @return Integration\API\Core\AbstractApi
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * @param $func
     *
     * @throws Exception\MethodNotSupportedException
     */
    private function apiCheck($func)
    {
        if (null === $this->getApi() || false === method_exists($this->getApi(), $func)) {
            throw new Exception\MethodNotSupportedException('Method not supported');
        }
    }

    /**
     * @param Helper\Transaction $transaction
     *
     * @throws Exception\MissingDataException
     */
    private function transactionIdCheck(Helper\Transaction $transaction)
    {
        if ($transaction->getTransactionId() == null) {
            throw new Exception\MissingDataException('TransactionId is missing');
        }
    }

    /**
     * @param Helper\Transaction $transaction
     *
     * @throws Exception\MissingDataException
     */
    private function parentTransactionCheck(Helper\Transaction $transaction)
    {
        if (NULL === $transaction->getParentTransaction()) {
            throw new Exception\MissingDataException('Parent transaction not set');
        }
    }

    /**
     * @param Helper\Transaction $transaction
     *
     * @throws Exception\VoidException
     */
    private function voidCheck(Helper\Transaction $transaction)
    {
        if ($transaction->getStatus() === Enum\Status::CANCELED) {
            throw new Exception\VoidException('This transaction has already been voided');
        }
    }

    /**
     * @param Helper\CreditCard $creditCard
     *
     * @throws Exception\BlacklistedCreditCardException
     */
    private function blacklistCheck(Helper\CreditCard $creditCard)
    {
        $blacklist = []; // TODO: store blacklist in separate file

        if (true === in_array($creditCard->getCardNumber(), $blacklist)) {
            throw new Exception\BlacklistedCreditCardException('Invalid credit card');
        }
    }

    /**
     * @param Helper\CreditCard $creditCard
     * @param Helper\Transaction $transaction
     * @param callable $success
     * @param callable $failure
     *
     * @return Response\CreateCharge
     */
    final public function createCharge(Helper\CreditCard $creditCard, Helper\Transaction $transaction, callable $success, callable $failure)
    {
        $this->apiCheck(__FUNCTION__);
        $this->transactionIdCheck($transaction);
        $this->blacklistCheck($creditCard);
        $response = new Response\CreateCharge($this->getApi());
        $response->setCreditCard($creditCard);
        $response->setTransaction($transaction);
        $this->execute($response, __FUNCTION__, $success, $failure);
        return $response;
    }

    /**
     * @param Helper\Transaction $transaction
     * @param callable $success
     * @param callable $failure
     *
     * @return Response\CreateChargeSaved
     */
    final public function createChargeSaved(Helper\Transaction $transaction, callable $success, callable $failure)
    {
        $this->apiCheck(__FUNCTION__);
        $response = new Response\CreateChargeSaved($this->getApi());
        $response->setTransaction($transaction);
        $this->execute($response, __FUNCTION__, $success, $failure);
        return $response;
    }

    /**
     * @param string $billingProfile
     * @param callable $success
     * @param callable $failure
     *
     * @return Response\RemoveCard
     */
    final public function removeCard($billingProfile, callable $success, callable $failure)
    {
        $this->apiCheck(__FUNCTION__);
        $response = new Response\RemoveCard($this->getApi());
        $response->setBillingProfile($billingProfile);
        $this->execute($response, __FUNCTION__, $success, $failure);
        return $response;
    }

    /**
     * @param Helper\Transaction $transaction
     * @param callable $success
     * @param callable $failure
     *
     * @throws \Exception
     * @return Response\ReturnTransaction
     */
    final public function returnTransaction(Helper\Transaction $transaction, callable $success, callable $failure)
    {
        $this->apiCheck(__FUNCTION__);
        try {
            $this->parentTransactionCheck($transaction);
        } catch (\Exception $e)
        {
            $transaction->setStatus(Enum\Status::INCOMPLETE);
            $transaction->setApiResponse(json_encode([ 'message' => $e->getMessage() ]));
            throw $e;
        }
        $response = new Response\ReturnTransaction($this->getApi());
        $response->setTransaction($transaction);
        $this->execute($response, __FUNCTION__, $success, $failure);
        return $response;
    }

    /**
     * @param Helper\CreditCard $creditCard
     * @param callable $success
     * @param callable $failure
     *
     * @return Response\SaveCard
     */
    final public function saveCard(Helper\CreditCard $creditCard, callable $success, callable $failure)
    {
        $this->apiCheck(__FUNCTION__);
        $this->blacklistCheck($creditCard);
        $response = new Response\SaveCard($this->getApi());
        $response->setCreditCard($creditCard);
        $this->execute($response, __FUNCTION__, $success, $failure);
        return $response;
    }

    /**
     * @param Helper\Transaction $transaction
     * @param callable $success
     * @param callable $failure
     *
     * @return Response\TransactionInfo
     */
    final public function transactionInfo(Helper\Transaction $transaction, callable $success, callable $failure)
    {
        $this->apiCheck(__FUNCTION__);
        $response = new Response\TransactionInfo($this->getApi());
        $response->setTransaction($transaction);
        $this->execute($response, __FUNCTION__, $success, $failure);
        return $response;
    }

    /**
     * @param Helper\CreditCard $creditCard
     * @param callable $success
     * @param callable $failure
     *
     * @return Response\ValidateCard
     */
    final public function validateCard(Helper\CreditCard $creditCard, callable $success, callable $failure)
    {
        $this->apiCheck(__FUNCTION__);
        $this->blacklistCheck($creditCard);
        $response = new Response\ValidateCard($this->getApi());
        $response->setCreditCard($creditCard);
        $this->execute($response, __FUNCTION__, $success, $failure);
        return $response;
    }

    /**
     * @param Helper\Transaction $transaction
     * @param callable $success
     * @param callable $failure
     *
     * @return Response\VoidTransaction
     */
    final public function voidTransaction(Helper\Transaction $transaction, callable $success, callable $failure)
    {
        $this->apiCheck(__FUNCTION__);
        $this->voidCheck($transaction);
        $response = new Response\VoidTransaction($this->getApi());
        $response->setTransaction($transaction);
        $this->execute($response, __FUNCTION__, $success, $failure);
        return $response;
    }

    /**
     * @param Response\Response $response
     * @param string $method
     * @param callable $success
     * @param callable $failure
     */
    protected function execute(Response\Response $response, $method, callable $success, callable $failure)
    {
        $response->execute([ $this, sprintf('%sResponse', $method) ], $success, $failure);
    }

}
