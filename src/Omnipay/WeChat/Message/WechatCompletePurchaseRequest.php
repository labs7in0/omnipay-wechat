<?php

namespace Omnipay\WeChat\Message;

class WechatCompletePurchaseRequest extends BaseAbstractRequest
{
    protected $endpoint = 'https://api.mch.weixin.qq.com/pay/orderquery';

    public function getTransactionId()
    {
        $this->getParameter('transaction_id');
    }

    public function setTransactionId($value)
    {
        $this->setParameter('transaction_id', $value);
    }

    public function getOutTradeNo()
    {
        $this->getParameter('out_trade_no');
    }

    public function setOutTradeNo($value)
    {
        $this->setParameter('out_trade_no', $value);
    }

    public function getData()
    {
        $this->validate(
            'app_id',
            'mch_id'
        );

        $params['appid'] = $this->parameters->get('app_id');
        $params['mch_id'] = $this->parameters->get('mch_id');
        $params['nonce_str'] = bin2hex(openssl_random_pseudo_bytes(8));
        $params['transaction_id'] = $this->parameters->get('transaction_id');
        $params['out_trade_no'] = $this->parameters->get('out_trade_no');
        return $params;
    }

    public function sendData($data)
    {
        $data = array(
            'appid' => $data['appid'],
            'mch_id' => $data['mch_id'],
            'nonce_str' => $data['nonce_str'],
            'transaction_id' => $data['transaction_id'],
            'out_trade_no' => $data['out_trade_no'],
        );

        $data['sign'] = $this->genSign($data);

        $data = $this->arrayToXml($data);

        $data = $this->xmlToArray($this->postStr($this->endpoint, $data));

        return new WechatCompletePurchaseResponse($this, $data);
    }
}
