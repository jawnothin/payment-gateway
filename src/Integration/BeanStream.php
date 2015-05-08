<?php

namespace Augwa\PaymentGateway\Integration;

use Augwa\PaymentGateway\Exception;
use Augwa\PaymentGateway\Helper;
use Augwa\PaymentGateway\PaymentGateway;
use Augwa\PaymentGateway\Integration\API;
use Augwa\PaymentGateway\Response;

/**
 * Class BeanStream
 * @package Augwa\PaymentGateway\Integration
 */
class BeanStream extends PaymentGateway
{

    /**
     * @param string $merchantId
     * @param string $passCode
     *
     * @return $this
     */
    public function setCredentials($merchantId, $passCode)
    {
        $this->setApi(new API\BeanStream($merchantId, $passCode, $this->getTestMode()));
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
            $response->setToken($response->getApiResponse('customer_code'));
            $response->setTokenExpiryDate($response->getCreditCard()->getCardExpiry());
        }
    }

    /**
     * @param Response\TransactionInfo $response
     *
     * @throws Exception\MethodNotSupportedException
     */
    public function transactionInfoResponse(Response\TransactionInfo $response)
    {
        $this->setSuccess($response);

        if ($response->getSuccess()) {

            $creditCard = new Helper\CreditCard;

            $cardInfo = $response->getApiResponse('card');
            $billingData = $response->getApiResponse('billing');

            $cardExpMonth = $cardInfo['expiry_month'];
            $cardExpYear = $cardInfo['expiry_year'] + 2000;
            $creditCard->setCardExpiry($cardExpMonth, $cardExpYear);
            $creditCard->setCardNumber('0000 0000 0000 ' . $cardInfo['last_four']);

            $creditCard->setName($cardInfo['name']);
            $creditCard->setAddress1($billingData['address_line1']);
            $creditCard->setAddress2($billingData['address_line2']);
            $creditCard->setCity($billingData['city']);
            $creditCard->setState($billingData['province']);
            $creditCard->setZipCode($billingData['postal_code']);
            $creditCard->setCountry($billingData['country']);
            $creditCard->setPhoneNumber($billingData['phone_number']);
            $creditCard->setEmailAddress($billingData['email_address']);

            $response->setCreditCard($creditCard);
            $response->setAmount($response->getApiResponse('amount'));
            $response->setOperation($response->getApiResponse('type'));
            $response->setDate(new \DateTime($response->getApiResponse('created')));

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
            $message = $response->getApiResponse('message');
            if ($message == null) {
                $message = $response->getHttpStatusCode();
            }
            $response->setApiError($message);
        }
    }

}
