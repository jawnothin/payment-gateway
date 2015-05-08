<?php

namespace Augwa\PaymentGateway\Integration\API;

use Augwa\PaymentGateway\Exception;
use Augwa\PaymentGateway\Helper;
use Augwa\PaymentGateway\Enum;

/**
 * Class GlobalPayments
 * @package Augwa\PaymentGateway\Integration\API
 */
class GlobalPayments extends Core\SoapApi
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
        $this->initSoapClient();
    }

    protected function updateEndpoint()
    {
        if (true === $this->getTestMode()) {
            $this->endpoint = 'https://certapia.globalpay.com/GlobalPay/transact.asmx?WSDL';
        } else {
            $this->endpoint = 'https://pia.globalpay.com/GlobalPay/transact.asmx?WSDL'; // TODO: this needs to be updated, no idea what the endpoint will actually be
        }
    }

    /**
     * @param Helper\CreditCard $creditCard
     * @param Helper\Transaction $transaction
     */
    public function createCharge(Helper\CreditCard $creditCard, Helper\Transaction $transaction)
    {
         $this->processCreditCard(
            'Sale',
            [
                'NameOnCard' => $creditCard->getName(),
                'CardNum' => $creditCard->getCardNumber(),
                'ExpDate' => $creditCard->getCardExpiry()->format('my'),
                'CVNum' => $creditCard->getCardCVV(),
                'Amount' => number_format($transaction->getAmount(), 2, '.', ''),
                'InvNum' => $transaction->getTransactionId(),
                'Zip' => $creditCard->getZipCode(),
                'Street' => $creditCard->getAddress1()
            ],
            true
        );
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function createChargeSaved(Helper\Transaction $transaction)
    {
        $this->processCreditCard(
            'TokenSale',
            [
                'Amount' => number_format($transaction->getAmount(), 2, '.', ''),
                'InvNum' => $transaction->getTransactionId(),
                'PNRef' => $transaction->getBillingProfile()
            ],
            true
        );
    }

    /**
     * @param string $billingProfile
     */
    public function removeCard($billingProfile) {
        /**
         * There is no way to invalidate a token on Global Payments, so we just return a success message
         */
        $this->mockExecute(
            [
                'Result' => '0',
                'Message' => 'AP',
                'RespMSG' => 'Approved'
            ]
        );
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function returnTransaction(Helper\Transaction $transaction)
    {
        $this->processCreditCard(
            'Return',
            [
                'Amount' => number_format($transaction->getAmount(), 2, '.', ''),
                'PNRef' => $transaction->getParentTransaction()->getReferenceNumber()
            ]
        );
    }

    /**
     * @param Helper\CreditCard $creditCard
     */
    public function saveCard(Helper\CreditCard $creditCard)
    {
        $this->validateCard($creditCard);
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function transactionInfo(Helper\Transaction $transaction)
    {
        $this->api(
            'GetTransaction',
            [
                'PNRef' => $transaction->getReferenceNumber()
            ]
        );
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function voidTransaction(Helper\Transaction $transaction)
    {
        $this->processCreditCard(
            'Void',
            [
                'PNRef' => $transaction->getReferenceNumber()
            ]
        );
    }


    /**
     * @param Helper\CreditCard $creditCard
     */
    public function validateCard(Helper\CreditCard $creditCard)
    {
        $this->processCreditCard(
            'CardVerify',
            [
                'CardNum' => $creditCard->getCardNumber(),
                'ExpDate' => $creditCard->getCardExpiry()->format('my'),
                'NameOnCard' => $creditCard->getName(),
                'InvNum' => substr(md5($creditCard->getCardNumber() . time()), 0, 16),
                'Zip' => $creditCard->getZipCode(),
                'Street' => $creditCard->getAddress1(),
                'CVNum' => $creditCard->getCardCVV()
            ],
            true
        );
    }

    /**
     * @param string $method
     * @param array $data
     * @param bool $includeCardExtData
     */
    private function processCreditCard($method, array $data, $includeCardExtData = false)
    {
        $this->api(
            'ProcessCreditCard',
            array_merge(
                [
                    'TransType' => $method,
                    'CardNum' => '',
                    'ExpDate' => '',
                    'MagData' => '',
                    'NameOnCard' => '',
                    'Amount' => '',
                    'InvNum' => '',
                    'PNRef' => '',
                    'Zip' => '',
                    'Street' => '',
                    'CVNum' => ''
                ],
                $data
            ),
            $includeCardExtData
        );
    }

    /**
     * @param $method
     * @param array $data
     * @param bool $includeCardExtData
     */
    public function api($method, array $data = [], $includeCardExtData = false)
    {
        $ExtData = [
            'DynamicDescriptor' => $this->dynamicDescriptor,
            'TimeOut' => self::DEFAULT_TIMEOUT,
            'TermType' => '1BK'
        ];

        if ($includeCardExtData) {
            $ExtData = array_merge(
                $ExtData,
                [
                    'CVPresence' => 'SUBMITTED',
                    'EntryMode' => 'MANUAL',
                    'Presentation' => 'False'
                ]
            );
        }

        $data = array_merge(
            [
                'GlobalUserName' => $this->username,
                'GlobalPassword' => $this->password,
                'ExtData' => $this->ExtDataXML($ExtData)
            ],
            $data
        );

        $this->soapExecute($method, $data, sprintf('%sResult', $method));
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    private function ExtDataXML(array $data)
    {
        $ExtData = new \SimpleXMLElement('<ExtData/>');
        return $this->array_to_xml($data, $ExtData);
    }

}
