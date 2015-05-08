<?php

namespace Augwa\PaymentGateway\Integration;

use Augwa\PaymentGateway\Exception;
use Augwa\PaymentGateway\Helper;
use Augwa\PaymentGateway\PaymentGateway;
use Augwa\PaymentGateway\Response;
use Augwa\PaymentGateway\Integration\API;

/**
 * Class PlugNPay
 * @package Augwa\PaymentGateway\Integration
 */
class PlugNPay extends PaymentGateway
{

    /**
     * @param string $username
     * @param string $password
     *
     * @return $this
     */
    public function setCredentials($username, $password)
    {
        $this->setApi(new API\PlugNPay($username, $password, $this->getTestMode()));
        return $this;
    }

    /**
     * @param Response\CreateCharge $response
     */
    public function createChargeResponse(Response\CreateCharge $response)
    {
        $response->setSuccess($response->getApiResponse('FinalStatus') === 'success');
        $response->setReferenceNumber($response->getApiResponse('orderID'));
        $response->getTransaction()->setApiResponse($response->getApiResponse());
    }

    /**
     * @param Response\CreateChargeSaved $response
     */
    public function createChargeSavedResponse(Response\CreateChargeSaved $response)
    {
        $response->setSuccess($response->getApiResponse('FinalStatus') === 'success');
        $response->setReferenceNumber($response->getApiResponse('orderID'));
        if ($response->getSuccess()) {
            $response->setBillingProfileExpiryDate(new \DateTime('+1 year'));
            $response->setBillingProfile($response->getApiResponse('orderID'));
        }
        $response->getTransaction()->setApiResponse($response->getApiResponse());
    }

    /**
     * @param Response\RemoveCard $response
     */
    public function removeCardResponse(Response\RemoveCard $response)
    {
        $response->setSuccess($response->getApiResponse('FinalStatus') === 'success');
    }

    /**
     * @param Response\ReturnTransaction $response
     */
    public function returnTransactionResponse(Response\ReturnTransaction $response)
    {
        $response->setSuccess($response->getApiResponse('FinalStatus') === 'success');
        if ($response->getSuccess()) {
            $response->setReferenceNumber($response->getApiResponse('orderID'));
        }
        $response->getTransaction()->setApiResponse($response->getApiResponse());
    }

    /**
     * @param Response\SaveCard $response
     */
    public function saveCardResponse(Response\SaveCard $response)
    {
        $response->setSuccess($response->getApiResponse('FinalStatus') === 'success');
        if ($response->getSuccess()) {
            $response->setToken($response->getApiResponse('orderID'));
            $response->setTokenExpiryDate(new \DateTime('+1 year'));
        }
    }

    /**
     * @param Response\TransactionInfo $response
     */
    public function transactionInfoResponse(Response\TransactionInfo $response)
    {
        $transactionData = [];
        if (null !== $response->getApiResponse('a001')) {
            parse_str($response->getApiResponse('a001'), $transactionData);
        }

        $transactionInfo = function($key) use ($transactionData) {
            return false === isset($transactionData[$key]) ? null : $transactionData[$key];
        };

        $response->setSuccess($transactionInfo('FinalStatus') === 'success');

        if ($transactionInfo('FinalStatus') !== null) {

            $creditCard = new Helper\CreditCard;

            $cardExp = $transactionInfo('card-exp');
            $cardExpMonth = null;
            $cardExpYear = null;
            if (sizeOf($cardExp)) {
                list($cardExpMonth, $cardExpYear) = explode('/', $cardExp);
                $cardExpYear += 2000;
                $creditCard->setCardExpiry($cardExpMonth, $cardExpYear);
            }

            $creditCard->setName($transactionInfo('card-name'));
            $creditCard->setAddress1($transactionInfo('card-address1'));
            $creditCard->setAddress2($transactionInfo('card-address2'));
            $creditCard->setCity($transactionInfo('card-city'));
            $creditCard->setState($transactionInfo('card-state'));
            $creditCard->setZipCode($transactionInfo('card-zip'));
            $creditCard->setCountry($transactionInfo('card-country'));
            $creditCard->setCardNumber($transactionInfo('card-number'));

            $response->setCreditCard($creditCard);
            $response->setAmount($transactionInfo('card-amount'));
            $response->setOperation($transactionInfo('operation'));
            $response->setDate(new \DateTime($transactionInfo('trans_date')));

        }
    }

    /**
     * @param Response\ValidateCard $response
     */
    public function validateCardResponse(Response\ValidateCard $response)
    {
        $response->setSuccess($response->getApiResponse('FinalStatus') === 'success');
    }

    /**
     * @param Response\VoidTransaction $response
     */
    public function voidTransactionResponse(Response\VoidTransaction $response)
    {
        $response->setSuccess($response->getApiResponse('FinalStatus') === 'success');
    }
}
