<?php

namespace Omnipay\WeChat\Message;

use Symfony\Component\HttpFoundation\ParameterBag;

class WechatPrePurchaseRequest extends BaseAbstractRequest
{
    protected $endpoint = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

    public function initialize(array $parameters = array())
    {
        if (null !== $this->response) {
            throw new \RuntimeException('Request cannot be modified after it has been sent!');
        }

        $this->parameters = new ParameterBag;
        foreach ($parameters as $k => $v) {
            $this->parameters->set($k, $v);
        }
        return $this;
    }

    public function getData()
    {
        $this->validate(
            'app_id',
            'mch_id',
            'body',
            'out_trade_no',
            'total_fee',
            'spbill_create_ip',
            'notify_url',
            'trade_type'
        );

        $params['appid'] = $this->parameters->get('app_id');
        $params['mch_id'] = $this->parameters->get('mch_id');
        $params['nonce_str'] = bin2hex(openssl_random_pseudo_bytes(8));
        $params['body'] = $this->parameters->get('body');
        $params['out_trade_no'] = $this->parameters->get('out_trade_no');
        $params['total_fee'] = $this->parameters->get('total_fee');
        $params['spbill_create_ip'] = $this->parameters->get('spbill_create_ip');
        $params['time_start'] = date('YmdHis');
        $params['notify_url'] = $this->parameters->get('notify_url');
        $params['trade_type'] = $this->parameters->get('trade_type');
        $params['product_id'] = $this->parameters->get('out_trade_no');
        return $params;
    }

    public function sendData($data)
    {
        $data = array(
            'appid' => $data['appid'],
            'mch_id' => $data['mch_id'],
            'device_info' => 'WEB',
            'nonce_str' => $data['nonce_str'],
            'body' => $data['body'],
            'out_trade_no' => $data['out_trade_no'],
            'total_fee' => $data['total_fee'],
            'spbill_create_ip' => $data['spbill_create_ip'],
            'time_start' => $data['time_start'],
            'notify_url' => $data['notify_url'],
            'trade_type' => $data['trade_type'],
            'product_id' => $data['product_id'],
        );

        $data['sign'] = $this->genSign($data);

        $data = $this->arrayToXml($data);

        return $this->xmlToArray($this->postStr($this->endpoint, $data));
    }
}
