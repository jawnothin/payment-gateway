<?php

namespace Augwa\PaymentGateway\Integration;

use Augwa\PaymentGateway\Exception;
use Augwa\PaymentGateway\Helper;
use Augwa\PaymentGateway\PaymentGateway;
use Augwa\PaymentGateway\Response;

/**
 * Class Moneris
 * @package Augwa\PaymentGateway\Integration
 */
class Moneris extends PaymentGateway
{

    /**
     * @param $storeId
     * @param $apiToken
     *
     * @return $this
     */
    public function setCredentials($storeId, $apiToken)
    {
        $this->setApi(new API\Moneris($storeId, $apiToken, $this->getTestMode()));
        return $this;
    }
    /**
     * @param Response\CreateCharge $response
     */
    public function createChargeResponse(Response\CreateCharge $response)
    {
        $this->setSuccess($response);
        $response->setReferenceNumber($response->getApiResponse('ReferenceNum'));
        $response->getTransaction()->setApiResponse($response->getApiResponse());
        var_dump($response->getApiResponse());
    }

    /**
     * @param Response\CreateChargeSaved $response
     */
    public function createChargeSavedResponse(Response\CreateChargeSaved $response)
    {
        $this->setSuccess($response);
        $response->setReferenceNumber($response->getApiResponse('ReferenceNum'));
        $response->getTransaction()->setApiResponse($response->getApiResponse());
    }

    /**
     * @param Response\RemoveCard $response
     */
    public function removeCardResponse(Response\RemoveCard $response)
    {
        $this->setSuccess($response);
    }

    /**
     * @param Response\ReturnTransaction $response
     */
    public function returnTransactionResponse(Response\ReturnTransaction $response)
    {
        $this->setSuccess($response);
        if ($response->getSuccess()) {
            $response->setReferenceNumber($response->getApiResponse('ReferenceNum'));
            $response->getTransaction()->setApiResponse($response->getApiResponse());
        }
    }

    /**
     * @param Response\SaveCard $response
     */
    public function saveCardResponse(Response\SaveCard $response)
    {
        $this->setSuccess($response);
        if ($response->getSuccess()) {
            $response->setToken($response->getApiResponse('DataKey'));
            $expiry = new \DateTime;
            $month = (int)substr($response->getApiResponse('expdate'), -2)+1;
            $year = (int)substr($response->getApiResponse('expdate'), 0, 2) + 2000;
            $expiry->setTimestamp(mktime(0, 0, -1, $month, 1, $year));
            $response->setTokenExpiryDate($expiry);
        }
    }

    /**
     * @param Response\TransactionInfo $response
     *
     * @throws Exception\MethodNotSupportedException
     */
    public function transactionInfoResponse(Response\TransactionInfo $response)
    {
        throw new Exception\MethodNotSupportedException('Transaction Info not supported');
    }

    /**
     * @param Response\ValidateCard $response
     */
    public function validateCardResponse(Response\ValidateCard $response)
    {
        $this->setSuccess($response);
    }

    /**
     * @param Response\VoidTransaction $response
     */
    public function voidTransactionResponse(Response\VoidTransaction $response)
    {
        $this->setSuccess($response);
    }

    /**
     * @param Response\Response $response
     */
    private function setSuccess($response) {
        $response->setSuccess($response->getApiResponse('Complete') === 'true' && (int)$response->getApiResponse('ResponseCode') < 50);
    }

}
