<?php

namespace Augwa\PaymentGateway\Integration\API\Core;

use Augwa\PaymentGateway\Helper;
use Augwa\PaymentGateway\Enum;
use Augwa\PaymentGateway\Exception;

/**
 * Class SoapApi
 * @package Augwa\PaymentGateway\Integration\API\Core
 */
abstract class SoapApi extends XmlApi
{

    /** @var \SoapClient */
    protected $wsClient;

    /** @var array */
    protected $wsOptions = [
        'connection_timeout' => self::DEFAULT_TIMEOUT,
        'trace' => true
    ];

    protected function soapExecute($method, $data, $responseNode)
    {
        $xmlResponse = $this->wsClient->$method($data)->$responseNode;
        $xmlResponse = $this->array_flat(json_decode(json_encode((array) simplexml_load_string($xmlResponse)), true), $arr = []);

        $this->setLastError(null);
        $this->setLastHttpStatusCode(0);

        $this->setLastRawRequest($this->wsClient->__getLastRequest());
        $this->setLastRawResponse($this->wsClient->__getLastResponse());

        $this->setLastRequest($data);
        $this->setLastResponse($xmlResponse);

        if ($this->getLastError()) {
            throw new Exception\SoapException($this->getLastError());
        }
    }

    protected function initSoapClient()
    {
        $this->wsOptions['user_agent'] = $this->getUserAgent();
        $this->wsClient = new \SoapClient($this->endpoint, $this->wsOptions);
    }

}