<?php

namespace Augwa\PaymentGateway\Integration;

use Augwa\PaymentGateway\Exception;
use Augwa\PaymentGateway\Helper;
use Augwa\PaymentGateway\PaymentGateway;
use Augwa\PaymentGateway\Integration\API;
use Augwa\PaymentGateway\Response;

/**
 * Class Mercury
 * @package Augwa\PaymentGateway\Integration
 */
class Mercury extends PaymentGateway
{

    /**
     * @param string $merchantId
     * @param string $password
     *
     * @return $this
     */
    public function setCredentials($merchantId, $password)
    {
        $this->setApi(new API\Mercury($merchantId, $password, $this->getTestMode()));
        return $this;
    }

    /**
     * @param Response\CreateCharge $response
     */
    public function createChargeResponse(Response\CreateCharge $response)
    {
        $this->setSuccess($response, $response->getApiResponse('CmdStatus') === 'Approved');
        if ($response->getSuccess()) {
            $response->setReferenceNumber($response->getApiResponse('RecordNo'));
        }
        $response->getTransaction()->setApiResponse($response->getApiResponse());
    }

    /**
     * @param Response\CreateChargeSaved $response
     */
    public function createChargeSavedResponse(Response\CreateChargeSaved $response)
    {
        $this->setSuccess($response, $response->getApiResponse('CmdStatus') === 'Approved');
        if ($response->getSuccess()) {
            $response->setReferenceNumber($response->getApiResponse('RecordNo'));
            $response->setBillingProfileExpiryDate(new \DateTime('+6 months'));
            $response->setBillingProfile($response->getApiResponse('RecordNo'));
        }
        $response->getTransaction()->setApiResponse($response->getApiResponse());
    }

    /**
     * @param Response\RemoveCard $response
     */
    public function removeCardResponse(Response\RemoveCard $response)
    {
        $this->setSuccess($response, $response->getApiResponse('CmdStatus') === 'Approved');
    }

    /**
     * @param Response\ReturnTransaction $response
     */
    public function returnTransactionResponse(Response\ReturnTransaction $response)
    {
        $this->setSuccess($response, $response->getApiResponse('CmdStatus') === 'Approved');
        if ($response->getSuccess()) {
            $response->setReferenceNumber($response->getApiResponse('RecordNo'));
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
        $this->setSuccess($response, $response->getApiResponse('CmdStatus') === 'Approved');

        if ($response->getSuccess()) {

            if (null === $response->getApiResponse('RecordNo')) {
                throw new Exception\MissingDataException('Token was not issued');
            }

            $response->setToken($response->getApiResponse('RecordNo'));
            $response->setTokenExpiryDate(new \DateTime('+6 months'));
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

        $this->setSuccess($response, $transactionInfo('status') === 'Approved');

        if ($transactionInfo('status') !== null) {

            $creditCard = new Helper\CreditCard;

            $cardExp = $transactionInfo('expiration');
            if (sizeOf($cardExp)) {
                $creditCard->setCardExpiry(substr($cardExp, 0, 2), ((int)substr($cardExp, 2) + 2000));
            }

            $creditCard->setCardNumber($transactionInfo('account'));

            $response->setCreditCard($creditCard);
            $response->setAmount($transactionInfo('total'));
            $response->setOperation($transactionInfo('trantype'));
            $response->setVoided($transactionInfo('voided') === 'true');
            $response->setDate(new \DateTime($transactionInfo('transdatetime')));

        }
    }

    /**
     * @param Response\ValidateCard $response
     */
    public function validateCardResponse(Response\ValidateCard $response)
    {
        $this->setSuccess($response, $response->getApiResponse('CmdStatus') === 'Approved');
    }

    /**
     * @param Response\VoidTransaction $response
     */
    public function voidTransactionResponse(Response\VoidTransaction $response)
    {
        $this->setSuccess($response, $response->getApiResponse('CmdStatus') === 'Approved');
    }

    /**
     * @param Response\Response $response
     * @param $success
     */
    private function setSuccess(Response\Response $response, $success)
    {
        $response->setSuccess($success);
        if ($response->getSuccess() === false) {
            $message = $response->getApiResponse('TextResponse') ?: 'Unknown Error';
            $response->setApiError($message);
        }
    }

}
