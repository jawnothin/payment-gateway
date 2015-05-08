<?php

use Augwa\PaymentGateway;

/**
 * define $paymentGateway
 * define $creditCard
 */
include __DIR__ . '/config.php';

$transaction = new PaymentGateway\Helper\Transaction;

$transaction->setTransactionId(time());
$transaction->setTransactionDate(new \DateTime);
$transaction->setCurrency('USD');
$transaction->setAmount('5.00');
$transaction->setComment('Test Transaction');

$paymentGateway->createCharge(
    $creditCard,
    $transaction,
    function (PaymentGateway\Response\CreateCharge $response) {
        echo sprintf("\033[0;32mYour credit card was charged for \033[1;32m$%.2f\033[0;32m with reference number: \033[1;32m%s\033[0;0m", $response->getTransaction()->getAmount(), $response->getTransaction()->getReferenceNumber()) . PHP_EOL;
    },
    function (PaymentGateway\Response\CreateCharge $response) {
        echo sprintf("\033[0;31mOops, seems like there was a problem charging the billing profile: \033[1;31m%s\033[0;0m", $response->getApiError()) . PHP_EOL;
    }
);

$paymentGateway->transactionInfo(
    $transaction,
    function (PaymentGateway\Response\TransactionInfo $response) {
        echo sprintf("\033[0;32mSuccessfully retrieved transaction information\033[0;0m") . PHP_EOL;
    },
    function (PaymentGateway\Response\TransactionInfo $response) {
        echo sprintf("\033[0;31mOops, seems like there was a problem getting the transaction information: \033[1;31m%s\033[0;0m", $response->getApiError()) . PHP_EOL;
    }
);