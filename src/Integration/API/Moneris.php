<?php

namespace Augwa\PaymentGateway\Integration\API;

use Augwa\PaymentGateway\Exception;
use Augwa\PaymentGateway\Helper;
use Augwa\PaymentGateway\Enum;

/**
 * Class Moneris
 * @package Augwa\PaymentGateway\Integration\API
 */
class Moneris extends Core\XmlApi
{

    /** @var string */
    protected $storeId;

    /** @var string */
    protected $apiToken;

    /**
     * @param $storeId
     * @param $apiToken
     * @param bool $testMode
     */
    public function __construct($storeId, $apiToken, $testMode = false)
    {
        $this->storeId = $storeId;
        $this->apiToken = $apiToken;
        $this->setTestMode($testMode);
    }

    protected function updateEndpoint()
    {
        if (true === $this->getTestMode()) {
            $this->endpoint = 'https://esqa.moneris.com/gateway2/servlet/MpgRequest';
        } else {
            $this->endpoint = 'https://www3.moneris.com/gateway2/servlet/MpgRequest';
        }
    }

    /**
     * @param Helper\CreditCard $creditCard
     * @param Helper\Transaction $transaction
     */
    public function createCharge(Helper\CreditCard $creditCard, Helper\Transaction $transaction)
    {
         $this->api(
            'purchase',
            [
                'order_id' => md5($transaction->getTransactionId()),
                'amount' => number_format($transaction->getAmount(), 2, '.', ''),
                'pan' => $creditCard->getCardNumber(),
                'expdate' => $creditCard->getCardExpiry()->format('ym'),
                'crypt_type' => 7,
                'dynamic_descriptor' => $this->dynamicDescriptor
            ]
        );
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function createChargeSaved(Helper\Transaction $transaction)
    {
        $this->api(
            'res_purchase_cc',
            [
                'data_key' => $transaction->getBillingProfile(),
                'order_id' => md5($transaction->getTransactionId()),
                'amount' => number_format($transaction->getAmount(), 2, '.', ''),
                'crypt_type' => 1,
                'dynamic_descriptor' => $this->dynamicDescriptor
            ]
        );
    }

    /**
     * @param string $billingProfile
     */
    public function removeCard($billingProfile) {
         $this->api(
            'res_delete',
            [
                'data_key' => $billingProfile
            ]
        );
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function returnTransaction(Helper\Transaction $transaction)
    {
        $this->api(
            'refund',
            [
                'order_id' => md5($transaction->getParentTransaction()->getTransactionId()),
                'amount' => number_format($transaction->getAmount(), 2, '.', ''),
                'txn_number' => $transaction->getParentTransaction()->getApiResponse('TransID'),
                'crypt_type' => 7,
                'dynamic_descriptor' => $this->dynamicDescriptor
            ]
        );
    }

    /**
     * @param Helper\CreditCard $creditCard
     */
    public function saveCard(Helper\CreditCard $creditCard)
    {
        $this->api(
            'res_add_cc',
            [
                'pan' => $creditCard->getCardNumber(),
                'expdate' => $creditCard->getCardExpiry()->format('ym'),
                'crypt_type' => 2
            ]
        );
    }

    /**
     * @param Helper\Transaction $transaction
     *
     * @throws Exception\MethodNotSupportedException
     * @return array
     */
    public function transactionInfo(Helper\Transaction $transaction)
    {
        throw new Exception\MethodNotSupportedException('Transaction Info not supported');
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function voidTransaction(Helper\Transaction $transaction)
    {
        $this->api(
            'purchasecorrection',
            [
                'order_id' => md5($transaction->getTransactionId()),
                'txn_number' => $transaction->getApiResponse('TransID'),
                'crypt_type' => 7,
                'dynamic_descriptor' => $this->dynamicDescriptor
            ]
        );

    }

    /**
     * @param Helper\CreditCard $creditCard
     */
    public function validateCard(Helper\CreditCard $creditCard)
    {
        $this->api(
            'card_verification',
            [
                'order_id' => 'vc-' . md5(time() . $creditCard->getCardNumber()),
                'pan' => $creditCard->getCardNumber(),
                'expdate' => $creditCard->getCardExpiry()->format('ym'),
                'crypt_type' => 7,
            ]
        );
    }

    /**
     * @param string $method
     * @param array $data
     */
    public function api($method, array $data = [])
    {
        if (array_key_exists('dynamic_descriptor', $data) && $data['dynamic_descriptor'] == null) {
            unset($data['dynamic_descriptor']);
        }

        $requestXML = $this->requestXML($method, $data);
        $this->curlExecute($this->endpoint, Enum\HttpMethod::POST, $requestXML, Enum\DataMode::NONE, Enum\DataMode::XML);
    }

    /**
     * @param string $method
     * @param array $data
     *
     * @return \SimpleXMLElement
     */
    private function requestXML($method, array $data) {

        $data = [
            'store_id' => $this->storeId,
            'api_token' => $this->apiToken,
            $method => $data
        ];

        return $this->array_to_xml($data, new \SimpleXMLElement('<request/>'));

    }

}
