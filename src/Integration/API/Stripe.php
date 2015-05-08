<?php

namespace Augwa\PaymentGateway\Integration\API;

use Augwa\PaymentGateway\Exception;
use Augwa\PaymentGateway\Helper;
use Augwa\PaymentGateway\Enum;

/**
 * Class Stripe
 * @package Augwa\PaymentGateway\Integration\API
 */
class Stripe extends Core\JsonApi
{

    /** @var string */
    protected $secretKey;

    /**
     * @param $secretKey
     * @param bool $testMode
     */
    public function __construct($secretKey, $testMode = false)
    {
        $this->secretKey = $secretKey;
        $this->setTestMode($testMode);
    }

    protected function updateEndpoint()
    {
        /**
         * endpoint is the same for both production and testing
         */
        $this->endpoint = 'https://api.stripe.com/v1';
    }

    /**
     * @param Helper\CreditCard $creditCard
     * @param Helper\Transaction $transaction
     */
    public function createCharge(Helper\CreditCard $creditCard, Helper\Transaction $transaction)
    {
        $this->api(
            '/charges',
            Enum\HttpMethod::POST,
            [
                'amount' => round($transaction->getAmount() * 100),
                'currency' => strtolower($transaction->getCurrencyCode()),
                'card' => [
                    'number' => $creditCard->getCardNumber(),
                    'exp_month' => $creditCard->getCardExpiry()->format('m'),
                    'exp_year' => $creditCard->getCardExpiry()->format('Y'),
                    'cvc' => $creditCard->getCardCVV(),
                    'name' => $creditCard->getName(),
                    'address_line1' => $creditCard->getAddress1(),
                    'address_line2' => $creditCard->getAddress2(),
                    'address_city' => $creditCard->getCity(),
                    'address_zip' => $creditCard->getState(),
                    'address_state' => $creditCard->getZipCode(),
                    'address_country' => $creditCard->getCountry()
                ],
                'metadata' => [
                    'user_ip_address' => isset($_SERVER) && array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : null,
                    'transaction_id' => $transaction->getTransactionId()
                ]
            ]
        );
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function createChargeSaved(Helper\Transaction $transaction)
    {
        $this->api(
            '/charges',
            Enum\HttpMethod::POST,
            [
                'amount' => round($transaction->getAmount() * 100),
                'currency' => strtolower($transaction->getCurrencyCode()),
                'customer' => $transaction->getBillingprofile(),
                'metadata' => [
                    'user_ip_address' => isset($_SERVER) && array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : null,
                    'transaction_id' => $transaction->getTransactionId()
                ]
            ]
        );
    }

    /**
     * @param string $billingProfile
     */
    public function removeCard($billingProfile)
    {
         $this->api(
            sprintf('/customers/%s', $billingProfile),
            Enum\HttpMethod::DELETE
        );
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function returnTransaction(Helper\Transaction $transaction)
    {
        $this->api(
            sprintf('/charges/%s/refunds', $transaction->getParentTransaction()->getReferenceNumber()),
            Enum\HttpMethod::POST,
            [
                'amount' => round($transaction->getAmount() * 100)
            ]
        );
    }

    /**
     * @param Helper\CreditCard $creditCard
     */
    public function saveCard(Helper\CreditCard $creditCard)
    {
        $this->api(
            '/customers',
            Enum\HttpMethod::POST,
            [
                'card' => [
                    'number' => $creditCard->getCardNumber(),
                    'exp_month' => $creditCard->getCardExpiry()->format('m'),
                    'exp_year' => $creditCard->getCardExpiry()->format('Y'),
                    'cvc' => $creditCard->getCardCVV(),
                    'name' => $creditCard->getName(),
                    'address_line1' => $creditCard->getAddress1(),
                    'address_line2' => $creditCard->getAddress2(),
                    'address_city' => $creditCard->getCity(),
                    'address_zip' => $creditCard->getState(),
                    'address_state' => $creditCard->getZipCode(),
                    'address_country' => $creditCard->getCountry()
                ]
            ]
        );
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function transactionInfo(Helper\Transaction $transaction)
    {
        $this->api(
            sprintf('/charges/%s', $transaction->getReferenceNumber()),
            Enum\HttpMethod::GET
        );
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function voidTransaction(Helper\Transaction $transaction)
    {
        $this->api(
            sprintf('/charges/%s/refunds', $transaction->getReferenceNumber()),
            Enum\HttpMethod::POST
        );
    }

    /**
     * @param Helper\CreditCard $creditCard
     */
    public function validateCard(Helper\CreditCard $creditCard)
    {
        $this->mockExecute(
            [],
            Enum\DataMode::NONE,
            Enum\DataMode::NONE,
            $creditCard->validate() ? 200 : 403
        );
    }

    /**
     * @param string $slug
     * @param string $method
     * @param array $data
     */
    public function api($slug, $method = Enum\HttpMethod::GET, array $data = [])
    {
        $headers = [
            sprintf('Content-Type: %s', 'application/x-www-form-urlencoded'),
            sprintf('Authorization: Bearer %s', $this->secretKey)
        ];

        $endpoint = sprintf('%s%s', $this->endpoint, $slug);

        $this->curlExecute($endpoint, $method, $data, Enum\DataMode::STRING, Enum\DataMode::JSON, $headers);
    }

}
