<?php

namespace Omnipay\WeChat\Message;

use Omnipay\Common\Message\CompletePurchaseResponseInterface;

class WechatCompletePurchaseResponse extends BaseAbstractResponse implements CompletePurchaseResponseInterface
{

    public function isSuccessful()
    {
        return $this->data['status'];
    }

    public function isRedirect()
    {
        return false;
    }

    public function isTradeStatusOk()
    {
        return $this->data['trade_status_ok'];
    }

    public function getMessage()
    {
        return $this->data['return_msg'];
    }

}
