<?php

namespace Augwa\PaymentGateway\Integration\API\Core;

use Augwa\PaymentGateway\Helper;
use Augwa\PaymentGateway\Enum;
use Augwa\PaymentGateway\Exception;

/**
 * Class AbstractApi
 * @package Augwa\PaymentGateway\Integration\API\Core
 */
abstract class AbstractApi
{

    /** @var int */
    const DEFAULT_TIMEOUT = 15;

    /** @var array */
    protected $lastResponse = [];

    /** @var string|array */
    protected $lastRequest;

    /** @var string */
    protected $lastRawResponse;

    /** @var string */
    protected $lastRawRequest;

    /** @var string */
    protected $dynamicDescriptor = '';

    /** @var string */
    protected $version = '1.0.0';

    /** @var bool */
    protected $testMode = false;

    /** @var string */
    protected $lastError;

    /** @var int */
    protected $lastHttpStatusCode;

    /** @var string */
    protected $endpoint;

    /**
     * @return string
     */
    protected function getUserAgent() {
        return sprintf("Augwa PGM/v%s; Curl/v%s; PHP/v%s", $this->getVersion(), curl_version()['version'], phpversion());
    }

    /**
     * @return bool
     */
    public function getTestMode()
    {
        return $this->testMode;
    }

    /**
     * @param null $testMode
     *
     * @return $this
     */
    public function setTestMode($testMode = null)
    {
        $this->testMode = (bool)$testMode;
        $this->updateEndpoint();
        return $this;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return void
     */
    abstract protected function updateEndpoint();

    /**
     * @param Helper\Transaction $transaction
     *
     * @return void
     */
    abstract public function voidTransaction(Helper\Transaction $transaction);

    /**
     * @param Helper\Transaction $transaction
     *
     * @return void
     */
    abstract public function returnTransaction(Helper\Transaction $transaction);

    /**
     * @param Helper\Transaction $transaction
     *
     * @return void
     */
    abstract public function transactionInfo(Helper\Transaction $transaction);

    /**
     * @param Helper\CreditCard $creditCard
     * @param Helper\Transaction $transaction
     *
     * @return void
     */
    abstract public function createCharge(Helper\CreditCard $creditCard, Helper\Transaction $transaction);

    /**
     * @param Helper\Transaction $transaction
     *
     * @return void
     */
    abstract public function createChargeSaved(Helper\Transaction $transaction);

    /**
     * @param Helper\CreditCard $creditCard
     *
     * @return void
     */
    abstract public function validateCard(Helper\CreditCard $creditCard);

    /**
     * @param Helper\CreditCard $creditCard
     *
     * @return void
     */
    abstract public function saveCard(Helper\CreditCard $creditCard);


    /**
     * @param string $billingProfile
     *
     * @return void
     */
    abstract public function removeCard($billingProfile);

    /**
     * @return string|array
     */
    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    /**
     * @param string|array $value
     *
     * @return $this
     */
    protected function setLastRequest($value = null)
    {
        $this->lastRequest = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastRawRequest()
    {
        return $this->lastRawRequest;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    protected function setLastRawRequest($value = null)
    {
        $this->lastRawRequest = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * @param array $value
     *
     * @return $this
     */
    protected function setLastResponse($value = null)
    {
        $this->lastResponse = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastRawResponse()
    {
        return $this->lastRawResponse;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    protected function setLastRawResponse($value = null)
    {
        $this->lastRawResponse = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * @param string $error
     *
     * @return $this
     */
    protected function setLastError($error = null)
    {
        $this->lastError = $error;
        return $this;
    }

    /**
     * @return int
     */
    public function getLastHttpStatusCode()
    {
        return $this->lastHttpStatusCode;
    }

    /**
     * @param int $httpStatusCode
     *
     * @return $this
     */
    protected function setLastHttpStatusCode($httpStatusCode = null)
    {
        $this->lastHttpStatusCode = $httpStatusCode;
        return $this;
    }

    /**
     * @param $endpoint
     * @param string $httpMethod
     * @param null $data
     * @param int $RequestDataMode
     * @param int $ResponseDataMode
     * @param array $headers
     * @param array $options
     *
     * @throws Exception\UnknownHttpMethodException
     * @throws Exception\UnknownDataModeException
     * @throws Exception\CurlException
     */
    protected function curlExecute($endpoint, $httpMethod = Enum\HttpMethod::GET, $data = null, $RequestDataMode = Enum\DataMode::NONE, $ResponseDataMode = Enum\DataMode::NONE, $headers = [], $options = [])
    {
        $encodedData = null;
        $postData = null;

        switch ($RequestDataMode) {

            case Enum\DataMode::NONE:
                $encodedData = $data;
                break;

            case Enum\DataMode::STRING:
                $encodedData = http_build_query($data);
                break;

            case Enum\DataMode::JSON:
                $encodedData = json_encode($data);
                break;

            case Enum\DataMode::XML:
                $encodedData = $data;
                break;

            default:
                throw new Exception\UnknownDataModeException(sprintf('Request DataMode unknown: %s', $RequestDataMode));
                break;

        }

        switch ($httpMethod) {

            case Enum\HttpMethod::POST:
            case Enum\HttpMethod::PUT:
            case Enum\HttpMethod::DELETE:
                $postData = $encodedData;
                break;

            case Enum\HttpMethod::GET:
                $endpoint = sprintf('%s?%s', $endpoint, $encodedData);
                break;

            default:
                throw new Exception\UnknownHttpMethodException(sprintf('HttpMethod unknown: %s', $httpMethod));
                break;

        }

        $options = array_replace(
            [
                CURLOPT_URL => $endpoint,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_CUSTOMREQUEST => $httpMethod,
                CURLOPT_USERAGENT => $this->getUserAgent(),
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_SSL_VERIFYPEER => $this->getTestMode() === false,
                CURLOPT_FORBID_REUSE => true,
                CURLOPT_FRESH_CONNECT => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_TIMEOUT => self::DEFAULT_TIMEOUT,
                CURLOPT_FAILONERROR => false,
                CURLINFO_HEADER_OUT => true,
            ],
            $options
        );

        $ch = curl_init();

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);

        $this->setLastError(curl_error($ch) ?: null);
        $this->setLastHttpStatusCode((int)curl_getinfo($ch, CURLINFO_HTTP_CODE));

        $this->setLastRawRequest(curl_getinfo($ch, CURLINFO_HEADER_OUT));
        $this->setLastRawResponse($response);

        $this->setLastRequest($encodedData);

        $lastResponse = null;

        switch ($ResponseDataMode) {

            case Enum\DataMode::NONE:
                $lastResponse = $response;
                break;

            case Enum\DataMode::STRING:
                parse_str($response, $lastResponse);
                break;

            case Enum\DataMode::JSON:
                $lastResponse = json_decode($response, true);
                break;

            case Enum\DataMode::XML:
                $lastResponse = $this->array_flat(json_decode(json_encode((array) simplexml_load_string($response)), true), $arr = []);
                break;

            default:
                throw new Exception\UnknownDataModeException(sprintf('Response DataMode unknown: %s', $ResponseDataMode));
                break;

        }

        $this->setLastResponse($lastResponse);

        curl_close($ch);

        if ($this->getLastError()) {
            throw new Exception\CurlException($this->getLastError());
        }
    }

    protected function mockExecute($data = null, $RequestDataMode = Enum\DataMode::NONE, $ResponseDataMode = Enum\DataMode::NONE, $httpStatusCode = 200)
    {

        switch ($RequestDataMode) {

            case Enum\DataMode::NONE:
                $encodedData = $data;
                break;

            case Enum\DataMode::STRING:
                $encodedData = http_build_query($data);
                break;

            case Enum\DataMode::JSON:
                $encodedData = json_encode($data);
                break;

            case Enum\DataMode::XML:
                $encodedData = $data;
                break;

            default:
                throw new Exception\UnknownDataModeException(sprintf('Request DataMode unknown: %s', $RequestDataMode));
                break;

        }

        $this->setLastError(null);
        $this->setLastHttpStatusCode($httpStatusCode);

        $this->setLastRawRequest(null);
        $this->setLastRawResponse(null);

        $this->setLastRequest($encodedData);
        $this->setLastRequest($data);

        $lastResponse = null;

        switch ($ResponseDataMode) {

            case Enum\DataMode::NONE:
                $lastResponse = $data;
                break;

            case Enum\DataMode::JSON:
                $lastResponse = json_decode($data, true);
                break;

            case Enum\DataMode::XML:
                $lastResponse = $this->array_flat(json_decode(json_encode((array) simplexml_load_string($data)), true), $arr = []);
                break;

            default:
                throw new Exception\UnknownDataModeException(sprintf('Response DataMode unknown: %s', $ResponseDataMode));
                break;

        }

        $this->setLastResponse($lastResponse);
    }

    /**
     * @param array $arr
     * @param \SimpleXMLElement $xml
     *
     * @return mixed
     */
    protected function array_to_xml(array $arr, \SimpleXMLElement &$xml)
    {
        foreach ($arr as $k => $v)
        {
            if (is_array($v)) {
                $node = $xml->addChild($k, null);
                $this->array_to_xml($v, $node);
            } else {
                $xml->addChild($k, $v);
            }
        }

        return $xml->asXML();
    }

    /**
     * @param array $complexArray
     * @param array $flatArray
     *
     * @return array
     */
    protected function array_flat(array $complexArray, array &$flatArray)
    {
        foreach ($complexArray as $key => $value)
        {
            if (is_array($value))
            {
                $flatArray = array_merge($flatArray, $this->array_flat($value, $flatArray));
            }
            else
            {
                $flatArray[$key] = trim($value);
            }
        }

        return $flatArray;
    }

}
