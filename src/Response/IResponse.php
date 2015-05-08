<?php

namespace Augwa\PaymentGateway\Response;

/**
 * Interface IResponse
 * @package Augwa\PaymentGateway\Response
 */
interface IResponse
{

    /**
     * @param callable $callback
     * @param callable $success
     * @param callable $failure
     *
     * @return mixed
     */
    public function execute(callable $callback, callable $success, callable $failure);

    /**
     * @param bool $success
     */
    public function setSuccess($success);

    /**
     * @return bool
     */
    public function getSuccess();

    /**
     * @return string
     */
    public function getMessage();

}