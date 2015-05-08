<?php

use Augwa\PaymentGateway;

/**
 * define $paymentGateway
 * define $creditCard
 */
include __DIR__ . '/config.php';

$transaction = new PaymentGateway\Helper\Transaction;

$transaction->setTransactionId('1' . time());
$transaction->setTransactionDate(new \DateTime);
$transaction->setCurrency('USD');
$transaction->setAmount('10.00');
$transaction->setComment('Test Transaction');

$paymentGateway->createCharge(
    $creditCard,
    $transaction,
    function (PaymentGateway\Response\CreateCharge $response) {
        /**
         * store the reference number for future use
         */
        $response->getTransaction()->setReferenceNumber($response->getReferenceNumber());
        echo sprintf("\033[0;32mYour credit card was charged for \033[1;32m$%.2f\033[0;32m with reference number: \033[1;32m%s\033[0;0m", $response->getTransaction()->getAmount(), $response->getReferenceNumber()) . PHP_EOL;
    },
    function (PaymentGateway\Response\CreateCharge $response) {
        echo sprintf("\033[0;31mOops, seems like there was a problem charging the credit card: \033[1;31m%s\033[0;0m", $response->getApiError()) . PHP_EOL;
    }
);

$refundTransaction = new PaymentGateway\Helper\Transaction;

$refundTransaction->setParentTransaction($transaction);
$refundTransaction->setTransactionId('2' . time());
$refundTransaction->setTransactionDate(new \DateTime);
$refundTransaction->setCurrency('USD');
$refundTransaction->setAmount('4.59');
$refundTransaction->setComment('Test Transaction');

$paymentGateway->returnTransaction(
    $refundTransaction,
    function(PaymentGateway\Response\ReturnTransaction $response) {
        echo sprintf("\033[0;32mSuccessfully refunded \033[1;32m$%.2f\033[0;32m with reference number \033[1;32m%s\033[0;32m linked to reference number \033[1;32m%s\033[0;0m", $response->getTransaction()->getAmount(), $response->getTransaction()->getReferenceNumber(), $response->getTransaction()->getParentTransaction()->getReferenceNumber()) . PHP_EOL;
    },
    function(PaymentGateway\Response\ReturnTransaction $response) {
        echo sprintf("\033[0;31mOops, seems like there was a problem refunding the transaction: \033[1;31m%s\033[0;0m", $response->getApiError()) . PHP_EOL;
    }
);