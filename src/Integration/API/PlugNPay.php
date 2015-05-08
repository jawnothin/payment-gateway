<?php

namespace Augwa\PaymentGateway\Integration\API;

use Augwa\PaymentGateway\Exception;
use Augwa\PaymentGateway\Helper;
use Augwa\PaymentGateway\Enum;

/**
 * Class PlugNPay
 * @package Augwa\PaymentGateway\Integration\API
 */
class PlugNPay extends Core\FormApi
{

    /** @var string */
    protected $username;

    /** @var string */
    protected $password;

    /**
     * @param string $username
     * @param string $password
     * @param bool $testMode
     */
    public function __construct($username, $password, $testMode = false)
    {
        $this->username = $username;
        $this->password = $password;
        $this->setTestMode($testMode);
    }

    protected function updateEndpoint()
    {
        /**
         * endpoint is the same for both production and testing
         */
        $this->endpoint = 'https://pay1.plugnpay.com/payment/pnpremote.cgi';
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function voidTransaction(Helper\Transaction $transaction)
    {
        $this->api('void', [
            'orderID' => $transaction->getReferenceNumber(),
            'card-amount' => number_format($transaction->getAmount(), 2, '.', ''),
            'txn-type' => 'auth'
        ]);
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function returnTransaction(Helper\Transaction $transaction)
    {
        $this->api('returnprev', [
            'prevorderid' => $transaction->getParentTransaction()->getReferenceNumber(),
            'orderID' => $transaction->getReferenceNumber(),
            'card-amount' => number_format(abs($transaction->getAmount()), 2, '.', '')
        ]);
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function transactionInfo(Helper\Transaction $transaction)
    {
        $this->api('query_trans', [
            'orderID' => $transaction->getReferenceNumber(),
            'startdate' => '20000101'
        ]);
    }

    /**
     * @param Helper\CreditCard $creditCard
     * @param Helper\Transaction $transaction
     */
    public function createCharge(Helper\CreditCard $creditCard, Helper\Transaction $transaction)
    {
         $this->api('auth', [
            'orderID' => $transaction->getTransactionId(),
            'authtype' => 'authonly',
            'card-amount' => number_format($transaction->getAmount(), 2, '.', ''),
            'card-name' => $creditCard->getName(),
            'card-address1' => $creditCard->getAddress1(),
            'card-address2' => $creditCard->getAddress2(),
            'card-city' => $creditCard->getCity(),
            'card-state' => $creditCard->getState(),
            'card-zip' => $creditCard->getZipCode(),
            'card-country' => $creditCard->getCountry(),
            'card-number' => $creditCard->getCardNumber(),
            'card-exp' => $creditCard->getCardExpiry()->format('m/y'),
            'card-cvv' => $creditCard->getCardCVV(),
            'currency' => $transaction->getCurrencyCode(),
            'ipaddress' => isset($_SERVER) && array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : null,
            'paymethod' => 'credit'
        ]);
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function createChargeSaved(Helper\Transaction $transaction)
    {
         $this->api('authprev', [
            'prevorderid' => $transaction->getBillingProfile(),
            'card-amount' => number_format($transaction->getAmount(), 2, '.', '')
        ]);
    }

    /**
     * @param Helper\CreditCard $creditCard
     */
    public function validateCard(Helper\CreditCard $creditCard)
    {
        $this->api('checkcard', [
            'card-number' => $creditCard->getCardNumber(),
            'card-exp' => $creditCard->getCardExpiry()->format('m/y')
        ]);
    }

    /**
     * @param Helper\CreditCard $creditCard
     */
    public function saveCard(Helper\CreditCard $creditCard)
    {
        $this->validateCard($creditCard);
    }

    /**
     * @param string $billingProfile
     *
     * @return array
     */
    public function removeCard($billingProfile)
    {
        /**
         * There is no way to invalidate a token on Plug n' Pay, so we just return a success message
         */
        $this->mockExecute(
            [ 'FinalStatus' => 'success' ]
        );
    }

    /**
     * @param $method
     * @param array $data
     */
    public function api($method, Array $data = [])
    {
        $data = array_merge(
            $data,
            [
                'mode' => $method,
                'publisher-name' => $this->username,
                'publisher-password' => $this->password
            ]
        );

        $headers = [
            'Content-type: text/plain'
        ];

        $this->curlExecute($this->endpoint, Enum\HttpMethod::POST, $data, Enum\DataMode::STRING, Enum\DataMode::STRING, $headers);
    }

}
