<?php

include_once __DIR__ . '/../autoload.php';

define('STRIPE_SECRET_KEY', '');

define('BEANSTREAM_MERCHANT_ID', '');
define('BEANSTREAM_PASSCODE', '');

define('CHASEPAYMENTECH_USERNAME', '');
define('CHASEPAYMENTECH_PASSWORD', '');
define('CHASEPAYMENTECH_BIN', '');
define('CHASEPAYMENTECH_MERCHANT_ID', '');
define('CHASEPAYMENTECH_TERMINAL_ID', '');

define('GLOBALPAYMENTS_USERNAME', '');
define('GLOBALPAYMENTS_PASSWORD', '');

define('MERCURY_MERCHANT_ID', '');
define('MERCURY_PASSWORD', '');

define('MONERIS_STORE_ID', '');
define('MONERIS_API_TOKEN', '');

define('PLUGNPAY_USERNAME', '');
define('PLUGNPAY_PASSWORD', '');

/*
$paymentGateway = new Augwa\PaymentGateway\Integration\BeanStream(true);
$paymentGateway->setCredentials(
    BEANSTREAM_MERCHANT_ID,
    BEANSTREAM_PASSCODE
);
*/

/*
$paymentGateway = new Augwa\PaymentGateway\Integration\ChasePaymentech(true);
$paymentGateway->setCredentials(
    CHASEPAYMENTECH_USERNAME,
    CHASEPAYMENTECH_PASSWORD,
    CHASEPAYMENTECH_BIN,
    CHASEPAYMENTECH_MERCHANT_ID,
    CHASEPAYMENTECH_TERMINAL_ID,
    Augwa\PaymentGateway\Enum\ChasePaymentech::CURRENCY_US_DOLLAR
);
*/

/*
$paymentGateway = new Augwa\PaymentGateway\Integration\GlobalPayments(true);
$paymentGateway->setCredentials(
    GLOBALPAYMENTS_USERNAME,
    GLOBALPAYMENTS_PASSWORD
);
*/

/*
$paymentGateway = new Augwa\PaymentGateway\Integration\Mercury(true);
$paymentGateway->setCredentials(
    MERCURY_MERCHANT_ID,
    MERCURY_PASSWORD
);
*/

/*
$paymentGateway = new Augwa\PaymentGateway\Integration\Moneris(true);
$paymentGateway->setCredentials(
    MONERIS_STORE_ID,
    MONERIS_API_TOKEN
);
*/

/*
$paymentGateway = new Augwa\PaymentGateway\Integration\PlugNPay(true);
$paymentGateway->setCredentials(
    PLUGNPAY_USERNAME,
    PLUGNPAY_PASSWORD
);
*/


$paymentGateway = new Augwa\PaymentGateway\Integration\Stripe(true);
$paymentGateway->setCredentials(
    STRIPE_SECRET_KEY
);

$creditCard = new Augwa\PaymentGateway\Helper\CreditCard;

$creditCard->setName('Jonathan Dahan');
/**
 * this can include spaces and dashes, or any other non-numerical separator
 * or simply just have all the numbers without spaces
 */
$creditCard->setCardNumber('4242 4242 4242 4242');
$creditCard->setCardExpiry(7, 2020);
$creditCard->setCardCVV(123);

if (false === $creditCard->validate()) {
    throw new \Exception('Credit Card Not Valid');
}