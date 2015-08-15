<?php

namespace Omnipay\WeChat\Message;

use Symfony\Component\HttpFoundation\ParameterBag;

class WechatPrePurchaseRequest extends BaseAbstractRequest
{
    protected $endpoint = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

    protected function getParameter($key)
    {
        return $this->parameters->get($key);
    }

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
            'app_key',
            'partner',
            'body',
            'out_trade_no',
            'total_fee',
            'spbill_create_ip',
            'notify_url',
            'open_id',
            'cert_path',
            'cert_key_path'
        );

        $params = $this->arrayOnly($this->parameters->all(), array(
            'app_id', 'app_key', 'partner',
            'body', 'out_trade_no', 'total_fee',
            'spbill_create_ip', 'notify_url', 'trade_type',
            'open_id', 'product_id',
            'device_info', 'attach',
            'time_start', 'time_expire', 'goods_tag', 'cert_path', 'cert_key_path',
        ));
        $params['appid'] = $params['app_id'];
        $params['openid'] = $params['open_id'];
        $params['mch_id'] = $params['partner'];
        $params['nonstr'] = bin2hex(openssl_random_pseudo_bytes(8));
        $params['time_start'] = date('YmdHis');
        $params['trade_type'] = $this->arrayGet($params, 'trade_type', 'JSAPI');
        return $this->arrayExcept($params, ['app_id', 'open_id', 'partner']);
    }

    public function sendData($data)
    {
        $data = array(
            'appid' => $data['appid'],
            'mch_id' => $data['mch_id'],
            'device_info' => 'WEB',
            'nonce_str' => bin2hex(openssl_random_pseudo_bytes(8)),
            'openid' => $data['openid'],
            'body' => $data['body'],
            'out_trade_no' => $data['out_trade_no'],
            'total_fee' => $data['total_fee'],
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
            'notify_url' => $data['notify_url'],
            'trade_type' => $data['trade_type'],
        );

        $data['sign'] = $this->genSign($data);

        $data = $this->arrayToXml($data);

        return $this->xmlToArray($this->postStr($this->endpoint, $data));
    }
}
