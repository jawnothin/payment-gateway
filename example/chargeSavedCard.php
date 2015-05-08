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

$transaction = new PaymentGateway\Helper\Transaction;

$transaction->setTransactionId(time());
$transaction->setTransactionDate(new \DateTime);
$transaction->setCurrency('USD');
$transaction->setAmount('10.00');
$transaction->setComment('Test Transaction');
$transaction->setBillingProfile($savedCard->getToken());

$paymentGateway->createChargeSaved(
    $transaction,
    function (PaymentGateway\Response\CreateChargeSaved $response) {
        echo sprintf("\033[0;32mYour credit card was charged for \033[1;32m$%.2f\033[0;32m with reference number: \033[1;32m%s\033[0;0m", $response->getTransaction()->getAmount(), $response->getReferenceNumber()) . PHP_EOL;
    },
    function (PaymentGateway\Response\CreateChargeSaved $response) {
        echo sprintf("\033[0;31mOops, seems like there was a problem charging the billing profile: \033[1;31m%s\033[0;0m", $response->getApiError()) . PHP_EOL;
    }
);