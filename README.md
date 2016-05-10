Omnipay-WeChat
===

**WeChat Payment driver for the Omnipay PHP payment processing library**

[![Build Status](https://img.shields.io/badge/project-deprecated-red.svg)](https://github.com/labs7in0/omnipay-wechat)
[![Build Status](https://img.shields.io/travis/labs7in0/omnipay-wechat.svg)](https://travis-ci.org/labs7in0/omnipay-wechat)
[![Coverage Status](https://img.shields.io/codecov/c/github/labs7in0/omnipay-wechat.svg)](https://codecov.io/github/labs7in0/omnipay-wechat)
[![Packagist Status](https://img.shields.io/packagist/v/labs7in0/omnipay-wechat.svg)](https://packagist.org/packages/labs7in0/omnipay-wechat)
[![Packagist Downloads](https://img.shields.io/packagist/dt/labs7in0/omnipay-wechat.svg)](https://packagist.org/packages/labs7in0/omnipay-wechat)

**Deprecated** We suggest you to use [@lokielse](https://github.com/lokielse)'s implementation of WeChatPay for Omnipay at [lokielse/omnipay-wechatpay](https://github.com/lokielse/omnipay-wechatpay).

There's a pre-built Payment Gateway based on Omnipay at [labs7in0/E-cash](https://github.com/labs7in0/E-cash).

## Installation

Omnipay is installed via [Composer](http://getcomposer.org/). To install, simply add it
to your `composer.json` file:

```json
{
    "require": {
        "labs7in0/omnipay-wechat": "dev-master"
    }
}
```

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

## Basic Usage

The following gateways are provided by this package:

* WeChat Express (WeChat NATIVE)

For general usage instructions, please see the main [Omnipay](https://github.com/thephpleague/omnipay)
repository.

## Example

### Make a payment

The WeChat NATIVE payment gateway return a URI which can be opened within WeChat In-App broswer, you can generate a QR code with the URI.

```php
$omnipay = Omnipay::create('WeChat_Express');

$omnipay->setAppId('app_id'); // App ID of your WeChat MP account
$omnipay->setAppKey('app_key'); // App Key of your WeChat MP account
$omnipay->setMchId('partner_id'); // Partner ID of your WeChat merchandiser (WeChat Pay) account

$params = array(
    'out_trade_no' => time() . rand(100, 999), // billing id in your system
    'notify_url' => $notify_url, // URL for asynchronous notify
    'body' => $billing_desc, // A simple description
    'total_fee' => 0.01, // Amount with less than 2 decimals places
    'fee_type' => 'CNY', // Currency name from ISO4217, Optional, default as CNY
);

$response = $omnipay->purchase($params)->send();

$qrCode = new Endroid\QrCode\QrCode(); // Use Endroid\QrCode to generate the QR code
$qrCode
    ->setText($response->getRedirectUrl())
    ->setSize(120)
    ->setPadding(0)
    ->render();
```

### Verify a payment (especially for asynchronous notify)

`completePurchase` for Omnipay-WeChat does not require the same arguments as when you made the initial `purchase` call. The only required parameter is `out_trade_no` (the billing id in your system) or `transaction_id` (the trade number from WeChat).

```php
$omnipay = Omnipay::create('WeChat_Express');

$omnipay->setAppId('app_id'); // App ID of your WeChat MP account
$omnipay->setAppKey('app_key'); // App Key of your WeChat MP account
$omnipay->setMchId('partner_id'); // Partner ID of your WeChat merchandiser (WeChat Pay) account

$params = array(
    'out_trade_no' => $billing_id, // billing id in your system
    //or you can use 'transaction_id', the trade number from WeChat
);

$response = $omnipay->completePurchase($params)->send();

if ($response->isSuccessful() && $response->isTradeStatusOk()) {
    $responseData = $response->getData();

    // Do something here
}

```

## Donate us

[Donate us](https://7in0.me/#donate)

## License
 The MIT License (MIT)

 More info see [LICENSE](LICENSE)
