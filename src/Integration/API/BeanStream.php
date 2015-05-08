<?php

namespace Augwa\PaymentGateway\Integration\API;

use Augwa\PaymentGateway\Exception;
use Augwa\PaymentGateway\Helper;
use Augwa\PaymentGateway\Enum;

/**
 * Class BeanStream
 * @package Augwa\PaymentGateway\Integration\API
 */
class BeanStream extends Core\JsonApi
{

    /** @var string */
    protected $passCode;

    /**
     * @param string $merchantId
     * @param string $passCode
     * @param bool $testMode
     */
    public function __construct($merchantId, $passCode, $testMode = false)
    {
        $this->merchantId = $merchantId;
        $this->passCode = $passCode;
        $this->setTestMode($testMode);
    }

    protected function updateEndpoint()
    {
        /**
         * endpoint is the same for both production and testing
         */
        $this->endpoint = 'https://www.beanstream.com/api/v1';
    }

    /**
     * @param Helper\CreditCard $creditCard
     * @param Helper\Transaction $transaction
     */
    public function createCharge(Helper\CreditCard $creditCard, Helper\Transaction $transaction)
    {
        $this->api(
            '/payments',
            Enum\HttpMethod::POST,
            [
                'payment_method' => 'card',
                'order_number' => $transaction->getTransactionId(),
                'amount' => number_format($transaction->getAmount(), 2, '.', ''),
                'customer_ip' => isset($_SERVER) && isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
                'card' => [
                    'complete' => true,
                    'name' => $creditCard->getName(),
                    'number' => $creditCard->getCardNumber(),
                    'expiry_month' => $creditCard->getCardExpiry()->format('m'),
                    'expiry_year' => $creditCard->getCardExpiry()->format('y'),
                    'cvd' => $creditCard->getCardCVV()
                ],
                'billing' => [
                    'name' => $creditCard->getName(),
                    'address_line1' => $creditCard->getAddress1(),
                    'address_line2' => $creditCard->getAddress2(),
                    'city' => $creditCard->getCity(),
                    'province' => $creditCard->getState(),
                    'country' => $creditCard->getCountry(),
                    'postal_code' => $creditCard->getZipCode(),
                    'phone_number' => $creditCard->getPhoneNumber(),
                    'email_address' => $creditCard->getEmailAddress(),
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
            '/payments',
            Enum\HttpMethod::POST,
            [
                'payment_method' => 'payment_profile',
                'order_number' => $transaction->getTransactionId(),
                'amount' => number_format($transaction->getAmount(), 2, '.', ''),
                'customer_ip' => isset($_SERVER) && isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
                'payment_profile' => [
                    'complete' => true,
                    'customer_code' => $transaction->getBillingProfile()
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
            sprintf('/profiles/%s', $billingProfile),
            Enum\HttpMethod::DELETE
        );
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function returnTransaction(Helper\Transaction $transaction)
    {
        $this->api(
            sprintf('/payments/%s/returns', $transaction->getParentTransaction()->getReferenceNumber()),
            Enum\HttpMethod::POST,
            [
                'order_number' => md5($transaction->getParentTransaction()->getTransactionId()),
                'amount' => number_format($transaction->getAmount(), 2, '.', '')
            ]
        );
    }

    /**
     * @param Helper\CreditCard $creditCard
     */
    public function saveCard(Helper\CreditCard $creditCard)
    {
        $this->api(
            '/profiles',
            Enum\HttpMethod::POST,
            [
                'card' => [
                    'complete' => true,
                    'name' => $creditCard->getName(),
                    'number' => $creditCard->getCardNumber(),
                    'expiry_month' => $creditCard->getCardExpiry()->format('m'),
                    'expiry_year' => $creditCard->getCardExpiry()->format('y'),
                    'cvd' => $creditCard->getCardCVV()
                ],
                'billing' => [
                    'name' => $creditCard->getName(),
                    'address_line1' => $creditCard->getAddress1(),
                    'address_line2' => $creditCard->getAddress2(),
                    'city' => $creditCard->getCity(),
                    'province' => $creditCard->getState(),
                    'country' => $creditCard->getCountry(),
                    'postal_code' => $creditCard->getZipCode(),
                    'phone_number' => $creditCard->getPhoneNumber(),
                    'email_address' => $creditCard->getEmailAddress(),
                ]
            ]
        );

        if ($this->getLastHttpStatusCode() === 200) {
            $this->getLastResponse()['expiry'] = $creditCard->getCardExpiry();
        }
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function transactionInfo(Helper\Transaction $transaction)
    {
        $this->api(
            sprintf('/payments/%s', $transaction->getReferenceNumber()),
            Enum\HttpMethod::GET
        );
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function voidTransaction(Helper\Transaction $transaction)
    {
        $this->api(
            sprintf('/payments/%s/returns', $transaction->getReferenceNumber()),
            Enum\HttpMethod::POST,
            [
                'amount' => number_format($transaction->getAmount(), 2, '.', '')
            ]
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
     * @param string $httpMethod
     * @param array $data
     */
    public function api($slug, $httpMethod = Enum\HttpMethod::GET, array $data = [])
    {
        $data = array_merge(
            [
                'merchant_id' => $this->merchantId
            ],
            $data
        );

        $headers = [
            sprintf('Content-Type: %s', 'application/json'),
            sprintf('Authorization: Passcode %s', base64_encode(sprintf('%s:%s', $this->merchantId, $this->passCode)))
        ];

        $endpoint = sprintf('%s%s', $this->endpoint, $slug);

        $this->curlExecute($endpoint, $httpMethod, $data, Enum\DataMode::JSON, Enum\DataMode::JSON, $headers);
    }

}
