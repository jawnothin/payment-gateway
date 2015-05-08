<?php

namespace Augwa\PaymentGateway\Integration\API;

use Augwa\PaymentGateway\Exception;
use Augwa\PaymentGateway\Helper;

/**
 * Class Mercury
 * @package Augwa\PaymentGateway\Integration\API
 */
class Mercury extends Core\SoapApi
{

    /** @var string */
    protected $merchantId;

    /** @var string */
    protected $password;

    /** @var string */
    protected $endpoint_alt;

    /**
     * @param $merchantId
     * @param $password
     * @param bool $testMode
     */
    public function __construct($merchantId, $password, $testMode = false)
    {
        $this->merchantId = $merchantId;
        $this->password = $password;
        $this->setTestMode($testMode);

        try {
            $this->initSoapClient();
        }
        catch (\Exception $e) {
            /**
             * Connect to the alternate endpoint on any error
             */
            $this->initAltSoapClient();
        }
    }

    protected function initAltSoapClient()
    {
        $this->wsClient = new \SoapClient($this->endpoint_alt, $this->wsOptions);
    }

    protected function updateEndpoint()
    {
        if (true === $this->getTestMode()) {
            $this->endpoint = 'https://w1.mercurydev.net/ws/ws.asmx?WSDL';
            $this->endpoint_alt = 'https://ws1.mercurydev.net/ws/ws.asmx?WSDL';
        } else {
            $this->endpoint = 'https://w1.mercurypay.com/ws/ws.asmx?WSDL';
            $this->endpoint_alt = 'https://w2.backuppay.com/ws/ws.asmx?WSDL';
        }
    }

    /**
     * @param Helper\CreditCard $creditCard
     * @param Helper\Transaction $transaction
     */
    public function createCharge(Helper\CreditCard $creditCard, Helper\Transaction $transaction)
    {
         $this->api(
            'CreditTransaction',
            [
                'tran' => $this->tStreamXML([
                    'TranCode' => 'Sale',
                    'InvoiceNo' => $transaction->getTransactionId(),
                    'RefNo' => $transaction->getTransactionId(),
                    'Amount' => [
                        'Purchase' => number_format($transaction->getAmount(), 2, '.', '')
                    ],
                    'Account' => [
                        'AcctNo' => $creditCard->getCardNumber(),
                        'ExpDate' => $creditCard->getCardExpiry()->format('my')
                    ],
                    'CVVData' => $creditCard->getCardCVV(),
                    'RecordNo' => 'RecordNumberRequested',
                    'AVS' => [
                        'Address' => $creditCard->getAddress1(),
                        'Zip' => $creditCard->getZipCode()
                    ],
                    'Frequency' => 'OneTime'
                ])
            ]
        );
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function createChargeSaved(Helper\Transaction $transaction)
    {
        $this->api(
            'CreditTransaction',
            [
                'tran' => $this->tStreamXML([
                    'TranCode' => 'SaleByRecordNo',
                    'InvoiceNo' => $transaction->getTransactionId(),
                    'Amount' => [
                        'Purchase' => number_format($transaction->getAmount(), 2, '.', '')
                    ],
                    'RecordNo' => $transaction->getBillingProfile(),
                    'Frequency' => 'OneTime'
                ])
            ]
        );
    }

    /**
     * @param string $billingProfile
     *
     * @return array
     */
    public function removeCard($billingProfile) {
        // There is no way to invalidate a token on Mercury, so we just return a success message
        return [ 'CmdStatus' => 'Approved' ];
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function returnTransaction(Helper\Transaction $transaction)
    {
        $this->api(
            'CreditTransaction',
            [
                'tran' => $this->tStreamXML([
                    'TranCode' => 'ReturnByRecordNo',
                    'InvoiceNo' => $transaction->getTransactionId(),
                    'RefNo' => $transaction->getTransactionId(),
                    'Amount' => [
                        'Purchase' => number_format($transaction->getAmount(), 2, '.', '')
                    ],
                    'RecordNo' => $transaction->getParentTransaction()->getReferenceNumber(),
                    'Frequency' => 'OneTime'
                ])
            ]
        );
    }

    /**
     * @param Helper\CreditCard $creditCard
     */
    public function saveCard(Helper\CreditCard $creditCard)
    {
        $this->api(
            'CreditTransaction',
            [
                'tran' => $this->tStreamXML([
                    'Account' => [
                       'AcctNo' => $creditCard->getCardNumber(),
                       'ExpDate' => $creditCard->getCardExpiry()->format('my'),
                    ],
                    'RecordNo' => 'RecordNumberRequested',
                    'Frequency' => 'OneTime'
                ], 'CardLookup')
            ]
        );
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function transactionInfo(Helper\Transaction $transaction)
    {
        $this->api(
            'CTranDetail',
            [
                'invoice' => $transaction->getTransactionId()
            ]
        );
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function voidTransaction(Helper\Transaction $transaction)
    {
        $this->api(
            'CreditTransaction',
            [
                'tran' => $this->tStreamXML([
                    'TranCode' => 'VoidSaleByRecordNo',
                    'InvoiceNo' => $transaction->getTransactionId(),
                    'RefNo' => $transaction->getApiResponse('RefNo'),
                    'Amount' => [
                        'Purchase' => number_format($transaction->getAmount(), 2, '.', '')
                    ],
                    'TranInfo' => [
                        'AuthCode' => $transaction->getApiResponse('AuthCode'),
                        'AcqRefData' => $transaction->getApiResponse('AcqRefData'),
                        'ProcessData' => $transaction->getApiResponse('ProcessData'),
                    ],
                    'RecordNo' => $transaction->getReferenceNumber(),
                    'Frequency' => 'OneTime'
                ])
            ]
        );

    }


    /**
     * @param Helper\CreditCard $creditCard
     */
    public function validateCard(Helper\CreditCard $creditCard)
    {
        $this->api(
            'CreditTransaction',
            [
                'tran' => $this->tStreamXML([
                    'Account' => [
                       'AcctNo' => $creditCard->getCardNumber(),
                       'ExpDate' => $creditCard->getCardExpiry()->format('my'),
                    ],
                    'RecordNo' => 'RecordNumberRequested',
                    'Frequency' => 'OneTime'
                ], 'CardLookup')
            ]
        );
    }

    /**
     * @param $method
     * @param array $data
     * @param bool $using_alt_endpoint
     *
     * @return array
     * @throws \Exception
     */
    public function api($method, array $data = [], $using_alt_endpoint = false)
    {
        $data = array_merge(
            $data,
            [
                'pw' => $this->password
            ]
        );

        $responseNode = sprintf('%sResult', $method);

        switch ($method) {

            case 'CTranDetail': {

                $data = array_merge(
                    $data,
                    [
                        'merchant' => $this->merchantId
                    ]
                );
            }
            break;

        }

        try {
            $this->soapExecute($method, $data, $responseNode);
        } catch (\Exception $e) {

            /**
             * If the alternate endpoint isn't being used
             * reset to the alternate endpoint and retry
             */
            if (false === $using_alt_endpoint) {
                $this->initAltSoapClient();
                $this->api($method, $data, $using_alt_endpoint);
                return;
            }

            throw $e;
        }
    }

    /**
     * @param array  $data
     * @param string $tranType
     *
     * @return mixed
     */
    private function tStreamXML(array $data, $tranType = 'Credit') {

        $data = array_merge(
            $data,
            [
                'TranType' => $tranType,
                'MerchantID' => $this->merchantId,
                'Memo' => $this->getUserAgent()
            ]
        );

        $tStream = new \SimpleXMLElement('<TStream/>');
        return $this->array_to_xml([ 'Transaction' => $data ], $tStream);

    }



}
