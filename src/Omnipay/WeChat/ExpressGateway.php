<?php

namespace Omnipay\WeChat;

use Omnipay\Common\AbstractGateway;

class ExpressGateway extends AbstractGateway
{
    public function getName()
    {
        return 'Wechat_Express';
    }

    public function setAppId($appId)
    {
        $this->setParameter('app_id', $appId);
    }

    public function getAppId()
    {
        return $this->getParameter('app_id');
    }

    public function setAppKey($appKey)
    {
        $this->setParameter('app_key', $appKey);
    }

    public function getAppKey()
    {
        return $this->getParameter('app_key');
    }

    public function setMchId($mchId)
    {
        $this->setParameter('mch_id', $mchId);
    }

    public function getMchId()
    {
        return $this->getParameter('mch_id');
    }

    public function setNotifyUrl($url)
    {
        $this->setParameter('notify_url', $url);
    }

    public function getNotifyUrl()
    {
        return $this->getParameter('notify_url');
    }

    public function purchase($parameters = array())
    {
        if (empty($parameters['code_url'])) {
            $res = $this->prePurchase($parameters)->send();
        }

        return $this->createRequest('\Omnipay\WeChat\Message\WechatPurchaseRequest', $res);
    }

    public function prePurchase($parameters = array())
    {
        $params = array(
            'app_id' => $this->getAppId(),
            'mch_id' => $this->getMchId(),
            'device_info' => 'WEB',
            'noncestr' => bin2hex(openssl_random_pseudo_bytes(8)),
            'body' => $parameters['body'],
            'out_trade_no' => $parameters['out_trade_no'],
            'total_fee' => round($parameters['total_fee'] * 100),
            'fee_type' => $parameters['fee_type'],
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
            'notify_url' => $parameters['notify_url'],
            'trade_type' => 'NATIVE',
        );

        return $this->createRequest('\Omnipay\WeChat\Message\WechatPrePurchaseRequest', $params);
    }

    public function completePurchase($parameters = array())
    {
        return $this->createRequest('\Omnipay\WeChat\Message\WechatCompletePurchaseRequest', $parameters);
    }
}
