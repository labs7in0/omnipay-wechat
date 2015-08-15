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

    public function setKey($key)
    {
        $this->setParameter('app_key', $key);
    }

    public function getKey()
    {
        return $this->getParameter('app_key');
    }

    public function getPartner()
    {
        return $this->getParameter('partner');
    }

    public function setPartner($id)
    {
        $this->setParameter('partner', $id);
    }

    public function getPartnerKey()
    {
        return $this->getParameter('partner_key');
    }

    public function setPartnerKey($key)
    {
        $this->setParameter('partner_key', $key);
    }

    public function setCertPath($path)
    {
        $this->setParameter('cert_path', $path);
    }

    public function getCertPath()
    {
        $this->getParameter('cert_path');
    }

    public function setCertKeyPath($path)
    {
        $this->setParameter('cert_key_path', $path);
    }

    public function getCertKeyPath()
    {
        $this->getParameter('cert_key_path');
    }

    public function getNotifyUrl()
    {
        return $this->getParameter('notify_url');
    }

    public function setNotifyUrl($url)
    {
        $this->setParameter('notify_url', $url);
    }

    public function setReturnUrl($url)
    {
        $this->setParameter('return_url', $url);
    }

    public function getReturnUrl($url)
    {
        return $this->getParameter('return_url');
    }

    public function setCancelUrl($url)
    {
        $this->setParameter('cancel_url', $url);
    }

    public function getCancelUrl($url)
    {
        return $this->getParameter('cancel_url', $url);
    }

    public function setFailUrl($url)
    {
        $this->setParameter('cancel_url', $url);
    }

    public function getFailUrl($url)
    {
        return $this->getParameter('fail_url', $url);
    }

    public function getDefaultParameters()
    {
        return array(
            'timestamp' => time(),
            'noncestr' => bin2hex(openssl_random_pseudo_bytes(8)),
        );
    }

    public static function xml2arrayByWechatNotifyBody($xml_str)
    {
        $postObj = simplexml_load_string($xml_str, 'SimpleXMLElement', LIBXML_NOCDATA);
        return array(
            'AppId' => (string) $postObj->AppId,
            'TimeStamp' => (string) $postObj->TimeStamp,
            'NonceStr' => (string) $postObj->NonceStr,
            'OpenId' => (string) $postObj->OpenId,
            'IsSubscribe' => (string) $postObj->IsSubscribe,
            'AppSignature' => (string) $postObj->AppSignature,
        );
    }

    public function createPackageStr($params)
    {
        $out_trade_no = $params['productid'];
        $fee = $params['money_paid'];
        $opts = array(
            'bank_type' => 'WX',
            'body' => $params['subject'],
            'partner' => $this->getPartner(),
            'out_trade_no' => $out_trade_no,
            'total_fee' => round($fee),
            'fee_type' => 1,
            'notify_url' => isset($params['notify_url']) ? $params['notify_url'] : $this->getNotifyUrl(),
            'spbill_create_ip' => '127.0.0.1',
            'input_charset' => 'UTF-8',
        );
        ksort($opts);
        $qstr = http_build_query($opts);
        $sign = strtoupper(md5(urldecode($qstr) . '&key=' . $this->getPartnerKey()));

        return $qstr . '&sign=' . $sign;
    }

    public function getAuthCode($redirect_url)
    {
        $params['appid'] = $this->getAppId();
        $params['redirect_uri'] = $redirect_url;
        $params['response_type'] = 'code';
        $params['scope'] = 'snsapi_base';
        $params['state'] = 'STATE#wechat_redirect';

        $params = http_build_query($params);

        return 'https://open.weixin.qq.com/connect/oauth2/authorize?' . $params;
    }

    public function getOpenid($code)
    {
        $params['appid'] = $this->getAppId();
        $params['secret'] = $this->getPartnerKey();
        $params['code'] = $code;
        $params['grant_type'] = 'authorization_code';

        ksort($params);

        $params = http_build_query($params);

        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?' . $params;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $res = curl_exec($ch);

        curl_close($ch);

        $data = json_decode($res, true);

        return $data['openid'];
    }

    private function arrayOnly($array, $keys)
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }

    private function arrayKeyMap($array, $keymap)
    {
        $keys = array_keys($keymap);
        $arr = $this->arrayOnly($array, $keys);
        $array = array_diff_key($array, array_flip((array) $keys));
        foreach ($arr as $k => $v) {
            $array[$keymap[$k]] = $v;
        }
        return $array;
    }

    public function purchase(array $parameters = array())
    {
        if (empty($parameters['prepay_id'])) {
            if (!isset($parameters['open_id'])) {
                throw new \RuntimeException('lack open id');
            }
            $res = $this->prePurchase($parameters)->send();
            if (empty($res['prepay_id'])) {
                throw new \RuntimeException('get prepay_id failed');
            } else {
                $parameters['prepay_id'] = $res['prepay_id'];
            }
        }

        $parameters['package'] = 'prepay_id=' . $parameters['prepay_id'];
        $parameters = $this->arrayKeyMap($parameters, ['out_trade_no' => 'productid']);
        $params = $this->arrayOnly($parameters, ['appid', 'timestamp', 'noncestr', 'productid', 'package', 'open_id']);
        return $this->createRequest('\Omnipay\WeChat\Message\WechatPurchaseRequest', $params);
    }

    public function prePurchase(array $parameters = array())
    {
        $parameters = $this->arrayKeyMap($parameters, ['subject' => 'body']);

        $params = $this->arrayOnly($parameters, ['out_trade_no', 'total_fee', 'body', 'open_id']);
        $params['total_fee'] = round($params['total_fee']);
        $params['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];
        $params['trade_type'] = 'JSAPI';

        return $this->createRequest('\Omnipay\WeChat\Message\WechatPrePurchaseRequest', $params);
    }

    public function completePurchase(array $parameters = array())
    {
        // $parameters['body'] = static::xml2array_by_wechat_notify_body($parameters['body']);
        return $this->createRequest('\Omnipay\WeChat\Message\WechatCompletePurchaseRequest', $parameters);
    }
}
