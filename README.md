Omnipay-WeChat
===

**WeChat Payment driver for the Omnipay PHP payment processing library**

[![Build Status](https://img.shields.io/travis/labs7in0/omnipay-wechat.svg)](https://travis-ci.org/labs7in0/omnipay-wechat)
[![Packagist Status](https://img.shields.io/packagist/v/labs7in0/omnipay-wechat.svg)](https://packagist.org/packages/labs7in0/omnipay-wechat)
[![Packagist Downloads](https://img.shields.io/packagist/dt/labs7in0/omnipay-wechat.svg)](https://packagist.org/packages/labs7in0/omnipay-wechat)

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

* WeChat Express (WeChat JSAPI)

For general usage instructions, please see the main [Omnipay](https://github.com/thephpleague/omnipay)
repository.

WeChat JSAPI require OAuth openid to submit a new order, use `$WeChat_Express->getAuthCode($callback)` to get an url for WeChat OAuth and `$WeChat_Express-->getOpenid($code)` in callback page to get openid.

p.s. the url for WeChat OAuth must be opened in WeChat In-App broswer, you can use `strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false` to check if the page were not opened in it, and generate a QR code for user.

All methods for WeChat OAuth will be removed in next stable version and I'll publish a WeChat MP library package for composer.

## Donate us

[Donate us](https://7in0.me/#donate)

## License
 The MIT License (MIT)

 More info see [LICENSE](LICENSE)
