<?php

namespace Omnipay\WeChat\Message;

use Symfony\Component\HttpFoundation\ParameterBag;

class WechatPurchaseRequest extends BaseAbstractRequest
{
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
        $this->validate('code_url');

        $params['code_url'] = $this->parameters->get('code_url');

        return $params;
    }

    public function sendData($data)
    {
        return new WechatPurchaseResponse($this, $data);
    }
}
