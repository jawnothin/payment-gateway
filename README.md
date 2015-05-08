# Payment Gateway

An easy to use class for integrating with one or more payment gateways

## Payment Gateway Support

+ BeanStream
+ Chase Paymentech
+ Global Payments
+ Mercury Payments
+ Moneris
+ Plug N' Pay
+ Stripe

## Supported Functions

+ Credit Card Tokenization
+ Removal of Credit Card Tokens
+ Credit Card Charge (with & without tokenization)
+ Void Transaction
+ Partial Return Transaction
+ Transaction Information

## Examples

```php
use Augwa\PaymentGateway;

$paymentGateway = new PaymentGateway\Integration\Stripe(true);
$paymentGateway->setCredentials(
    STRIPE_SECRET_KEY
);

$transaction = new PaymentGateway\Helper\Transaction;

$transaction->setTransactionId(time());
$transaction->setTransactionDate(new \DateTime);
$transaction->setCurrency('USD');
$transaction->setAmount('10.00');
$transaction->setComment('Test Transaction');

$paymentGateway->createCharge(
    $creditCard,
    $transaction,
    function (PaymentGateway\Response\CreateCharge $response) {
        echo sprintf("\033[0;32mYour credit card was charged for \033[1;32m$%.2f\033[0;32m with reference number: \033[1;32m%s\033[0;0m", $response->getTransaction()->getAmount(), $response->getReferenceNumber()) . PHP_EOL;
    },
    function (PaymentGateway\Response\CreateCharge $response) {
        echo sprintf("\033[0;31mOops, seems like there was a problem charging the credit card: \033[1;31m%s\033[0;0m", $response->getApiError()) . PHP_EOL;
    }
);
```

see https://github.com/augwa/payment-gateway/example for more code examples