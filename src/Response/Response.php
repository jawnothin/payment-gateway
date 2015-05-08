<?php

namespace Augwa\PaymentGateway\Response;

use Augwa\PaymentGateway\Integration\API\Core\AbstractApi;

/**
 * Class Response
 * @package Augwa\PaymentGateway\Response
 */
abstract class Response implements IResponse
{

    /** @var AbstractApi */
    protected $api;

    /** @var array */
    protected $apiResponse = [];

    /** @var array */
    protected $apiRequest = [];

    /** @var string */
    protected $apiError;

    /** @var int */
    protected $httpStatusCode = 0;

    /** @var bool */
    protected $success = false;

    abstract protected function fetch();

    abstract protected function postResponseAction();

    /**
     * @param AbstractApi $api
     */
    public function __construct(AbstractApi $api)
    {
        $this->api = $api;
    }

    /**
     * @param string $key
     *
     * @return string|array
     */
    public function getApiResponse($key = null)
    {
        if ($key === null) {
            return $this->apiResponse;
        } else {
            return is_array($this->apiResponse) && array_key_exists($key, $this->apiResponse) ? $this->apiResponse[$key] : null;
        }
    }

    /**
     * @param array $apiResponse
     *
     * @return $this
     */
    protected function setApiResponse(array $apiResponse = null)
    {
        $this->apiResponse = $apiResponse;
        return $this;
    }

    /**
     * @return string|array
     */
    public function getApiRequest()
    {
        return $this->apiRequest;
    }

    /**
     * @param string|array $apiRequest
     *
     * @return $this
     */
    protected function setApiRequest($apiRequest = null)
    {
        $this->apiRequest = $apiRequest;
        return $this;
    }

    /**
     * @param string $apiError
     *
     * @return $this
     */
    public function setApiError($apiError = null)
    {
        $this->apiError = $apiError;
        return $this;
    }

    /**
     * @return string
     */
    public function getApiError()
    {
        return $this->apiError;
    }

    /**
     * @param int $httpStatusCode
     *
     * @return $this
     */
    protected function setHttpStatusCode($httpStatusCode = null)
    {
        $this->httpStatusCode = (int)$httpStatusCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return json_encode($this->getApiResponse());
    }

    /**
     * @param bool $success
     *
     * @return $this
     */
    public function setSuccess($success = null)
    {
        $this->success = (bool)$success;
        return $this;
    }

    /**
     * @return bool
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @param callable $callback
     * @param callable $success
     * @param callable $failure
     *
     * @return mixed
     */
    public function execute(callable $callback, callable $success, callable $failure)
    {
        $this->fetch();

        $this->setApiError($this->api->getLastError());
        $this->setApiResponse($this->api->getLastResponse());
        $this->setApiRequest($this->api->getLastRequest());
        $this->setHttpStatusCode($this->api->getLastHttpStatusCode());

        $callback($this);
        $this->postResponseAction();

        if ($this->getSuccess()) {
            return $success($this);
        } else {
            return $failure($this);
        }
    }

}