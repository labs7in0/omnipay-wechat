<?php

namespace Omnipay\WeChat\Message;

use Symfony\Component\HttpFoundation\ParameterBag;

class WechatPurchaseRequest extends BaseAbstractRequest
{

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
            'productid',
            'app_key'
        );

        $params = $this->parameters->all();
        $params['appid'] = $params['app_id'];
        $params['appkey'] = $params['app_key'];
        $params['mch_id'] = $params['partner'];
        $params = $this->arrayOnly($params, array(
            'appid', 'productid', 'appkey',
            'noncestr', 'timestamp', 'package', 'mch_id',
        ));

        return $params;
    }

    public function sendData($data)
    {
        $this->response = new WechatPurchaseResponse($this, $data);
        if ($this->parameters->has('return_url')) {
            $this->response->setReturnUrl($this->parameters->get('return_url'));
        }
        if ($this->parameters->has('return_url')) {
            $this->response->setCancelUrl($this->parameters->get('cancel_url'));
        }
        if ($this->parameters->has('fail_url')) {
            $this->response->setFailUrl($this->parameters->get('fail_url'));
        }
        return $this->response;
    }
}
