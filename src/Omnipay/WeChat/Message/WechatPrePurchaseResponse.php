<?php

namespace Omnipay\WeChat\Message;

class WechatPrePurchaseResponse extends BaseAbstractResponse
{

    public function isRedirect()
    {
        return false;
    }

    public function isSuccessful()
    {
        return $this->data['return_code'] == 'SUCCESS' && $this->data['result_code'] == 'SUCCESS';
    }

    public function getTransactionReference()
    {
        return array_only($this->data, array(
            'prepay_id', 'trade_type', 'code_url',
            'appid', 'mch_id', 'device_info', 'nonce_str',
            'sign',
            'result_code', 'err_code', 'err_code_des', 'return_code', 'return_msg',
        ));
    }
}
