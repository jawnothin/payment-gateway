<?php

use Augwa\PaymentGateway;

/**
 * define $paymentGateway
 * define $creditCard
 */
include __DIR__ . '/config.php';

$savedCard = $paymentGateway->saveCard(
    $creditCard,
    function(PaymentGateway\Response\SaveCard $response) {
        echo sprintf("\033[0;32mBilling profile successfully created, your token is \033[1;32m%s\033[0;32m and will expire on \033[1;32m%s\033[0;32m\033[0;0m", $response->getToken(), $response->getTokenExpiryDate()->format('F jS, Y')) . PHP_EOL;
    },
    function(PaymentGateway\Response\SaveCard $response) {
        echo sprintf("\033[0;31mOops, seems like there was a problem saving the credit card: \033[1;31m%s\033[0;0m", $response->getApiError()) . PHP_EOL;
    }
);

$paymentGateway->removeCard(
    $savedCard->getToken(),
    function(PaymentGateway\Response\RemoveCard $response) {
        echo sprintf("\033[0;32mBilling profile with token \033[1;32m%s\033[0;32m has been successfully deleted\033[0;0m", $response->getBillingProfile()) . PHP_EOL;
    },
    function(PaymentGateway\Response\RemoveCard $response) {
        echo sprintf("\033[0;31mOops, seems like there was a problem saving the credit card: \033[1;31m%s\033[0;0m", $response->getApiError()) . PHP_EOL;
    }
);