<?php

namespace Augwa\PaymentGateway\Integration\API;

use Augwa\PaymentGateway\Exception\CurlException;
use Augwa\PaymentGateway\Helper;
use Augwa\PaymentGateway\Enum;

/**
 * Class ChasePaymentech
 * @package Augwa\PaymentGateway\Integration\API
 */
class ChasePaymentech extends Core\XmlApi
{

    /** @var string */
    protected $username;

    /** @var string */
    protected $password;

    /** @var string */
    protected $bin;

    /** @var string */
    protected $merchantId;

    /** @var string */
    protected $terminalId;

    /** @var string */
    protected $currencyCode;

    /** @var string */
    protected $endpoint_alt;

    /**
     * @param string $username
     * @param string $password
     * @param string $bin
     * @param string $merchantId
     * @param string $terminalId
     * @param $currencyCode
     * @param bool $testMode
     */
    public function __construct($username, $password, $bin, $merchantId, $terminalId, $currencyCode, $testMode = false)
    {
        $this->username = $username;
        $this->password = $password;
        $this->bin = $bin;
        $this->merchantId = $merchantId;
        $this->terminalId = $terminalId;
        $this->setCurrencyCode($currencyCode);
        $this->setTestMode($testMode);
    }

    protected function updateEndpoint()
    {
        if (true === $this->getTestMode()) {
            $this->endpoint = 'https://orbitalvar1.paymentech.net/authorize';
            $this->endpoint_alt = 'https://orbitalvar2.paymentech.net/authorize';
        } else {
            $this->endpoint = 'https://orbital1.paymentech.net/authorize';
            $this->endpoint_alt = 'https://orbital2.paymentech.net/authorize';
        }
    }

    /**
     * @param string $currencyCode
     *
     * @return $this
     */
    private function setCurrencyCode($currencyCode)
    {
        $this->currencyCode = str_pad($currencyCode, 3, 0, STR_PAD_LEFT);
        return $this;
    }

    /**
     * @return int
     */
    private function getCurrencyExponent()
    {
        $exponent = 2;

        switch ($this->currencyCode) {

            case Enum\ChasePaymentech::CURRENCY_BELARUSSIAN_RUBLE:
            case Enum\ChasePaymentech::CURRENCY_BURUNDI_FRANC:
            case Enum\ChasePaymentech::CURRENCY_CFA_FRANC_BCEAO:
            case Enum\ChasePaymentech::CURRENCY_CFA_FRANC_BEAC:
            case Enum\ChasePaymentech::CURRENCY_CFP_FRANC:
            case Enum\ChasePaymentech::CURRENCY_COMORO_FRANC:
            case Enum\ChasePaymentech::CURRENCY_DJIBOUTI_FRANC:
            case Enum\ChasePaymentech::CURRENCY_JAPANESE_YEN:
            case Enum\ChasePaymentech::CURRENCY_LAOS_KIP:
            case Enum\ChasePaymentech::CURRENCY_MALAGASY_FRANC:
            case Enum\ChasePaymentech::CURRENCY_PARAGUAY_GUARANI:
            case Enum\ChasePaymentech::CURRENCY_RWANDA_FRANC:
            case Enum\ChasePaymentech::CURRENCY_SOUTH_KOREAN_WON:
            case Enum\ChasePaymentech::CURRENCY_UGANDA_SHILLING:
            case Enum\ChasePaymentech::CURRENCY_VANUATU_VATU:
                $exponent = 0;
                break;

        }

        return $exponent;
    }

    /**
     * @param Helper\CreditCard $creditCard
     * @param Helper\Transaction $transaction
     */
    public function createCharge(Helper\CreditCard $creditCard, Helper\Transaction $transaction)
    {
         $this->api(
            'NewOrder',
            [
                'MessageType' => Enum\ChasePaymentech::MESSAGE_TYPE_AUTHORIZATION_CAPTURE_REQUEST,
                'AccountNum' => $creditCard->getCardNumber(),
                'Exp' => $creditCard->getCardExpiry()->format('my'),
                'CurrencyCode' => $this->currencyCode,
                'CurrencyExponent' => $this->getCurrencyExponent(),
                'CardSecValInd' => $creditCard->getCardCVV() ? Enum\ChasePaymentech::CARD_CCV_PRESENT : Enum\ChasePaymentech::CARD_CCV_NOT_PRESENT,
                'CardSecVal' => $creditCard->getCardCVV(),
                'AVSzip' => $creditCard->getZipCode(),
                'AVSaddress1' => $creditCard->getAddress1(),
                'AVSaddress2' => $creditCard->getAddress2(),
                'AVScity' => $creditCard->getCity(),
                'AVSstate' => $creditCard->getState(),
                'AVSphoneNum' => $creditCard->getPhoneNumber(),
                'AVSname' => $creditCard->getName(),
                'AVScountryCode' => $creditCard->getCountryCode(),
                'OrderID' => $transaction->getTransactionId(),
                'Amount' => $transaction->getAmount() * pow(10, $this->getCurrencyExponent()),
                'CustomerIpAddress' => isset($_SERVER) && array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : '',
                'CustomerBrowserName' => isset($_SERVER) && array_key_exists('USER_AGENT', $_SERVER) ? $_SERVER['USER_AGENT'] : ''
            ]
        );
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function createChargeSaved(Helper\Transaction $transaction)
    {
         $this->api(
            'NewOrder',
            [
                'MessageType' => Enum\ChasePaymentech::MESSAGE_TYPE_AUTHORIZATION_CAPTURE_REQUEST,
                'CurrencyCode' => $this->currencyCode,
                'CurrencyExponent' => $this->getCurrencyExponent(),
                'OrderID' => $transaction->getTransactionId(),
                'CustomerRefNum' => $transaction->getBillingProfile(),
                'Amount' => $transaction->getAmount() * pow(10, $this->getCurrencyExponent()),
                'CustomerIpAddress' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
                'CustomerBrowserName' => isset($_SERVER['USER_AGENT']) ? $_SERVER['USER_AGENT'] : ''
            ]
        );
    }

    /**
     * @param string $billingProfile
     */
    public function removeCard($billingProfile)
    {
         $this->api(
            'Profile',
            [
                'CustomerProfileAction' => Enum\ChasePaymentech::CUSTOMER_PROFILE_ACTION_DELETE,
                'CustomerRefNum' => $billingProfile
            ]
        );
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function returnTransaction(Helper\Transaction $transaction)
    {
        $this->api(
            'Reversal',
            [
                'TxRefNum' => $transaction->getParentTransaction()->getReferenceNumber(),
                'AdjustedAmt' => $transaction->getAmount() * pow(10, $this->getCurrencyExponent()),
                'OrderID' => $transaction->getParentTransaction()->getTransactionId()
            ]
        );
    }

    /**
     * @param Helper\CreditCard $creditCard
     */
    public function saveCard(Helper\CreditCard $creditCard)
    {
         $this->api(
            'Profile',
            [
                'CustomerName' => $creditCard->getName(),
                'CustomerAddress1' => $creditCard->getAddress1(),
                'CustomerAddress2' => $creditCard->getAddress2(),
                'CustomerCity' => $creditCard->getCity(),
                'CustomerState' => $creditCard->getState(),
                'CustomerZIP' => $creditCard->getZipCode(),
                'CustomerPhone' => $creditCard->getPhoneNumber(),
                'CustomerCountryCode' => $creditCard->getCountryCode(),
                'CCAccountNum' => $creditCard->getCardNumber(),
                'CCExpireDate' => $creditCard->getCardExpiry()->format('my'),
                'CustomerProfileAction' => Enum\ChasePaymentech::CUSTOMER_PROFILE_ACTION_CREATE,
                'CustomerAccountType' => Enum\ChasePaymentech::CUSTOMER_ACCOUNT_TYPE_CREDIT_CARD,
                'CustomerProfileFromOrderInd' => Enum\ChasePaymentech::CUSTOMER_PROFILE_GENERATION_AUTO,
                'CustomerProfileOrderOverrideInd' => Enum\ChasePaymentech::CUSTOMER_PROFILE_ORDER_OVERRIDE_MAPPING_NONE
            ]
        );
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function transactionInfo(Helper\Transaction $transaction)
    {
        $this->api(
            'Inquiry',
            [
                'OrderID' => $transaction->getTransactionId(),
                'InquiryRetryNumber' => $transaction->getTransactionId()
            ]
        );
    }

    /**
     * @param Helper\Transaction $transaction
     */
    public function voidTransaction(Helper\Transaction $transaction)
    {
        $this->api(
            'Reversal',
            [
                'TxRefNum' => $transaction->getReferenceNumber(),
                'AdjustedAmt' => $transaction->getAmount() * pow(10, $this->getCurrencyExponent()),
                'OrderID' => $transaction->getTransactionId()
            ]
        );

    }

    /**
     * @param Helper\CreditCard $creditCard
     */
    public function validateCard(Helper\CreditCard $creditCard)
    {
        $this->mockExecute(
            [
                'ProcStatus' => '0',
                'ApprovalStatus' => $creditCard->validate() ? '1' : '0'
            ]
        );
    }

    /**
     * @param $method
     * @param array $data
     * @param bool $using_alt_endpoint
     */
    public function api($method, array $data = [], $using_alt_endpoint = false)
    {
        $requestXML = $this->requestXML($method, $data);

        $headers = [
            sprintf('MIME-Version: %s', 'HTTP/1.1'),
            sprintf('Content-Type: %s', 'application/PTI62'),
            sprintf('Content-length: %d', strlen($requestXML)),
            sprintf('Content-transfer-encoding: %s', 'text'),
            sprintf('Request-number: %d', 1),
            sprintf('Document-type: %s', 'Request'),
            sprintf('Interface-Version: %s', $this->getUserAgent())
        ];

        if ($method === 'NewOrder' && isset($data['OrderID'])) {
            $headers[] = sprintf('Trace-number: %d', $data['OrderID']);
        }

        $endpoint = $using_alt_endpoint ? $this->endpoint_alt : $this->endpoint;

        try {
            $this->curlExecute($endpoint, Enum\HttpMethod::POST, $requestXML, Enum\DataMode::NONE, Enum\DataMode::XML, $headers);
        }
        catch (CurlException $e) {
            $this->api($method, $data, true);
            return;
        }
    }

    /**
     * @param string $method
     * @param array $data
     *
     * @return \SimpleXMLElement
     */
    private function requestXML($method, array $data)
    {

        /**
         * This includes additional fields that may or may not be used on every request.
         * The reOrderData() function will strip out un-needed fields.
         */
        $data = array_merge(
            [
                'IndustryType' => Enum\ChasePaymentech::INDUSTRY_TYPE_ECOMMERCE,
                'OrbitalConnectionUsername' => $this->username,
                'OrbitalConnectionPassword' => $this->password,
                'BIN' => $this->bin,
                'CustomerBin' => $this->bin,
                'MerchantID' => $this->merchantId,
                'CustomerMerchantID' => $this->merchantId,
                'TerminalID' => $this->terminalId,
                'TxRefIdx' => ''
            ],
            $data
        );

        return $this->array_to_xml([ $method => $this->reOrderData($method, $data) ], new \SimpleXMLElement('<Request/>'));
    }

    /**
     * Chase expects the XML elements to be in a specific order.
     * As such the Request XSD is read and re-orders elements based on this.
     *
     * This will also strip out fields that are not used by this particular request type
     *
     * @param string $method
     * @param array $data
     * @return array
     */
    private function reOrderData($method, array $data)
    {
        $RequestXSD = __DIR__ . '/../../Resources/ChasePaymentech/Request_PTI62.xsd';
        $xml = new \DOMDocument();

        $xml->load($RequestXSD);
        $xpath = new \DOMXPath($xml);
        $result = $xpath->query(sprintf('/xs:schema/xs:element[@name="Request"]/xs:complexType/xs:choice/xs:element[@name="%s"]', $method));

        $type = null;

        /** @var $element_node \DOMElement  */
        foreach($result as $element_node) {
            if ($element_node->getAttribute('name') === $method) {
                $type = $element_node->getAttribute('type');
                break;
            }
        }

        $elements = [];

        $result = $xpath->query(sprintf('/xs:schema/xs:complexType[@name="%s"]/xs:sequence/xs:element', $type));
        foreach($result as $element_node) {
            $elements[] = $element_node->getAttribute('name');
        }

        $newData = [];

        foreach($elements as $element) {
            if (is_array($data) && array_key_exists($element, $data)) {
                $newData[$element] = $data[$element];
            }
        }

        return $newData;
    }

}