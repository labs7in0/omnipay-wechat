<?php

namespace Omnipay\WeChat\Message;

use Omnipay\Common\Message\AbstractResponse;

class WechatCompletePurchaseResponse extends AbstractResponse
{
    public function isSuccessful()
    {
        $result = $this->getData();
        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            return true;
        } else {
            return false;
        }
    }

    public function isRedirect()
    {
        return false;
    }

    public function isTradeStatusOk()
    {
        $result = $this->getData();
        return  $result['trade_state'] == 'SUCCESS';
    }

    public function getMessage()
    {
        $result = $this->getData();
        return $result['return_msg'];
    }
}
