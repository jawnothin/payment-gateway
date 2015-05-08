<?php

namespace Augwa\PaymentGateway\Integration;

use Augwa\PaymentGateway\Exception;
use Augwa\PaymentGateway\Helper;
use Augwa\PaymentGateway\PaymentGateway;
use Augwa\PaymentGateway\Response;
use Augwa\PaymentGateway\Integration\API;

/**
 * Class Stripe
 * @package Augwa\PaymentGateway\Integration
 */
class Stripe extends PaymentGateway
{

    /**
     * @param string $secretKey
     *
     * @return Stripe $this
     */
    public function setCredentials($secretKey)
    {
        $this->setApi(new API\Stripe($secretKey, $this->getTestMode()));
        return $this;
    }

    /**
     * @param Response\CreateCharge $response
     */
    public function createChargeResponse(Response\CreateCharge $response)
    {
        $this->setSuccess($response);
        $response->setReferenceNumber($response->getApiResponse('id'));
        $response->getTransaction()->setApiResponse($response->getApiResponse());
    }

    /**
     * @param Response\CreateChargeSaved $response
     */
    public function createChargeSavedResponse(Response\CreateChargeSaved $response)
    {
        $this->setSuccess($response);
        $response->setReferenceNumber($response->getApiResponse('id'));
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
            $response->setReferenceNumber($response->getApiResponse('id'));
        }
        $response->getTransaction()->setApiResponse($response->getApiResponse());
    }

    /**
     * @param Response\SaveCard $response
     */
    public function saveCardResponse(Response\SaveCard $response)
    {
        $this->setSuccess($response);
        if ($response->getSuccess()) {
            $response->setToken($response->getApiResponse('id'));
            $response->setTokenExpiryDate($response->getCreditCard()->getCardExpiry());
        }
    }

    /**
     * @param Response\TransactionInfo $response
     */
    public function transactionInfoResponse(Response\TransactionInfo $response)
    {
        $this->setSuccess($response);

        if ($response->getSuccess()) {

            $creditCard = new Helper\CreditCard;

            $cardInfo = $response->getApiResponse('card');
            $customerInfo = $cardInfo['customer'];

            $creditCard->setCardExpiry($cardInfo['exp_month'], $cardInfo['exp_year']);
            $creditCard->setCardNumber('0000 0000 0000 ' . $cardInfo['last4']);

            $creditCard->setName($cardInfo['name']);
            $creditCard->setAddress1($cardInfo['address_line1']);
            $creditCard->setAddress2($cardInfo['address_line2']);
            $creditCard->setCity($cardInfo['address_city']);
            $creditCard->setState($cardInfo['address_state']);
            $creditCard->setZipCode($cardInfo['address_zip']);
            $creditCard->setCountry($cardInfo['address_country']);
            $creditCard->setEmailAddress(isset($customerInfo['email']) ? $customerInfo['email'] : null);

            $response->setCreditCard($creditCard);
            $response->setAmount($response->getApiResponse('amount'));
            $response->setOperation($response->getApiResponse('object'));
            $response->setDate((new \DateTime)->setTimestamp($response->getApiResponse('created')));

        }
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
        $response->setSuccess($response->getHttpStatusCode() === 200);
        if ($response->getSuccess() === false) {
            $response->setApiError($response->getApiResponse('error')['message']);
        }
    }

}
