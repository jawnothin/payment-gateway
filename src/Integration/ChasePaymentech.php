<?php

namespace Augwa\PaymentGateway\Integration;

use Augwa\PaymentGateway\Exception;
use Augwa\PaymentGateway\Helper;
use Augwa\PaymentGateway\PaymentGateway;
use Augwa\PaymentGateway\Integration\API;
use Augwa\PaymentGateway\Response;

/**
 * Class ChasePaymentech
 * @package Augwa\PaymentGateway\Integration
 */
class ChasePaymentech extends PaymentGateway
{

    /**
     * @param string $username
     * @param string $password
     * @param string $bin
     * @param string $merchantId
     * @param string $terminalId
     * @param int $currencyCode
     *
     * @return $this
     */
    public function setCredentials($username, $password, $bin, $merchantId, $terminalId, $currencyCode)
    {
        $this->setApi(new API\ChasePaymentech($username, $password, $bin, $merchantId, $terminalId, $currencyCode, $this->getTestMode()));
        return $this;
    }

    /**
     * @param Response\CreateCharge $response
     */
    public function createChargeResponse(Response\CreateCharge $response)
    {
        $this->setSuccess($response, $response->getApiResponse('ProcStatus') === '0' && $response->getApiResponse('ApprovalStatus') === '1');
        $response->setReferenceNumber($response->getApiResponse('TxRefNum'));
        $response->getTransaction()->setApiResponse($response->getApiResponse());
    }

    /**
     * @param Response\CreateChargeSaved $response
     */
    public function createChargeSavedResponse(Response\CreateChargeSaved $response)
    {
        $this->setSuccess($response, $response->getApiResponse('ProcStatus') === '0' && $response->getApiResponse('ApprovalStatus') === '1');
        $response->setReferenceNumber($response->getApiResponse('TxRefNum'));
        $response->getTransaction()->setApiResponse($response->getApiResponse());
    }

    /**
     * @param Response\RemoveCard $response
     */
    public function removeCardResponse(Response\RemoveCard $response)
    {
        $this->setSuccess($response, $response->getApiResponse('ProfileProcStatus') === '0');
    }

    /**
     * @param Response\ReturnTransaction $response
     */
    public function returnTransactionResponse(Response\ReturnTransaction $response)
    {
        $this->setSuccess($response, $response->getApiResponse('ProcStatus') === '0');
        if ($response->getSuccess()) {
            $response->setReferenceNumber($response->getApiResponse('TxRefNum'));
        }
        $response->getTransaction()->setApiResponse($response->getApiResponse());
    }

    /**
     * @param Response\SaveCard $response
     */
    public function saveCardResponse(Response\SaveCard $response)
    {
        $this->setSuccess($response, $response->getApiResponse('CustomerRefNum') !== null);
        if ($response->getSuccess()) {
            $response->setToken($response->getApiResponse('CustomerRefNum'));
            $response->setTokenExpiryDate(new \DateTime('+1 year'));
        }
    }

    /**
     * @param Response\TransactionInfo $response
     */
    public function transactionInfoResponse(Response\TransactionInfo $response)
    {
        $this->setSuccess($response, $response->getApiResponse('ProcStatus') === '0' && $response->getApiResponse('ApprovalStatus') === '1');
        if ($response->getSuccess()) {

            $creditCard = new Helper\CreditCard;

            $creditCard->setName($response->getApiResponse('CustomerName'));
            $creditCard->setCardNumber($response->getApiResponse('AccountNum'));

            $response->setCreditCard($creditCard);
            $response->setAmount($response->getApiResponse('RequestedAmount'));
            $response->setOperation($response->getApiResponse('MessageType'));

        }
    }

    /**
     * @param Response\ValidateCard $response
     */
    public function validateCardResponse(Response\ValidateCard $response)
    {
        $this->setSuccess($response, $response->getApiResponse('ProcStatus') === '0' && $response->getApiResponse('ApprovalStatus') === '1');
    }

    /**
     * @param Response\VoidTransaction $response
     */
    public function voidTransactionResponse(Response\VoidTransaction $response)
    {
        $this->setSuccess($response, $response->getApiResponse('ProcStatus') === '0');
    }

    private function setSuccess(Response\Response $response, $success)
    {
        $response->setSuccess($success);
        if ($response->getSuccess() === false) {
            $response->setApiError($response->getApiResponse('StatusMsg'));
        }
    }

}