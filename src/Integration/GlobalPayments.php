<?php

namespace Augwa\PaymentGateway\Integration;

use Augwa\PaymentGateway\Exception;
use Augwa\PaymentGateway\PaymentGateway;
use Augwa\PaymentGateway\Response;
use Augwa\PaymentGateway\Helper;
use Augwa\PaymentGateway\Integration\API;

/**
 * Class GlobalPayments
 * @package Augwa\PaymentGateway\Integration
 */
class GlobalPayments extends PaymentGateway
{

    /**
     * @param string $username
     * @param string $password
     *
     * @return $this
     */
    public function setCredentials($username, $password)
    {
        $this->setApi(new API\GlobalPayments($username, $password, $this->getTestMode()));
        return $this;
    }

    /**
     * @param Response\CreateCharge $response
     */
    public function createChargeResponse(Response\CreateCharge $response)
    {
        $this->setSuccess($response, $response->getApiResponse('Result') === '0');
        if ($response->getSuccess()) {
            $response->setReferenceNumber($response->getApiResponse('PNRef'));
        }
        $response->getTransaction()->setApiResponse($response->getApiResponse());
    }

    /**
     * @param Response\CreateChargeSaved $response
     */
    public function createChargeSavedResponse(Response\CreateChargeSaved $response)
    {
        $this->setSuccess($response, $response->getApiResponse('Result') === '0');
        if ($response->getSuccess()) {
            $response->setReferenceNumber($response->getApiResponse('PNRef'));
            $response->setBillingProfileExpiryDate(new \DateTime('+24 months'));
            $response->setBillingProfile($response->getApiResponse('PNRef'));
        }
        $response->getTransaction()->setApiResponse($response->getApiResponse());
    }

    /**
     * @param Response\RemoveCard $response
     */
    public function removeCardResponse(Response\RemoveCard $response)
    {
        $this->setSuccess($response, $response->getApiResponse('Result') === '0');
    }

    /**
     * @param Response\ReturnTransaction $response
     */
    public function returnTransactionResponse(Response\ReturnTransaction $response)
    {
        $this->setSuccess($response, $response->getApiResponse('Result') === '0');

        if ($response->getSuccess()) {
            $response->setReferenceNumber($response->getApiResponse('PNRef'));
        }
        $response->getTransaction()->setApiResponse($response->getApiResponse());
    }

    /**
     * @param Response\SaveCard $response
     *
     * @throws Exception\MissingDataException
     */
    public function saveCardResponse(Response\SaveCard $response)
    {
        $this->setSuccess($response, $response->getApiResponse('Result') === '0');

        if ($response->getSuccess()) {

            if (null === $response->getApiResponse('PNRef')) {
                throw new Exception\MissingDataException('Token was not issued');
            }

            $response->setToken($response->getApiResponse('PNRef'));
            $response->setTokenExpiryDate(new \DateTime('+24 months'));
        }
    }

    /**
     * @param Response\TransactionInfo $response
     */
    public function transactionInfoResponse(Response\TransactionInfo $response)
    {
        $this->setSuccess($response, $response->getApiResponse('ResultCode') === '0');

        $creditCard = new Helper\CreditCard;

        $cardExp = $response->getApiResponse('ExpDate');
        if (sizeOf($cardExp)) {
            $creditCard->setCardExpiry(substr($cardExp, 0, 2), ((int)substr($cardExp, 2) + 2000));
        }

        $creditCard->setCardNumber($response->getApiResponse('CardNumber'));

        $response->setCreditCard($creditCard);
        $response->setAmount($response->getApiResponse('Amount'));
        $response->setOperation($response->getApiResponse('TransactionType'));
        $response->setVoided($response->getApiResponse('Voided') === '1');
        $response->setDate(new \DateTime($response->getApiResponse('CreateDate')));
    }

    /**
     * @param Response\ValidateCard $response
     */
    public function validateCardResponse(Response\ValidateCard $response)
    {
        $this->setSuccess($response, $response->getApiResponse('Result') === '0');
    }

    /**
     * @param Response\VoidTransaction $response
     */
    public function voidTransactionResponse(Response\VoidTransaction $response)
    {
        $this->setSuccess($response, $response->getApiResponse('Result') === '0');
    }

    private function setSuccess(Response\Response $response, $success)
    {
        $response->setSuccess($success);
        if ($response->getSuccess() === false) {
            $response->setApiError($response->getApiResponse('RespMSG'));
        }
    }

}
