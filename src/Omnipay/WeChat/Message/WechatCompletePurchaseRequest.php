<?php

namespace Omnipay\WeChat\Message;

class WechatCompletePurchaseRequest extends BaseAbstractRequest
{
    public function getBody()
    {
        return $this->getParameter('body', array());
    }

    public function setBody($body)
    {
        $this->setParameter('body', $body);
    }

    public function getRequestParams()
    {
        $this->getParameter('request_params', array());
    }

    public function setRequestParams($rp)
    {
        $this->setParameter('request_params', $rp);
    }

    public function getData()
    {
        $this->validate('body');
        return $this->getParameters();
    }

    private function checkSign($data)
    {
        unset($data['sign']);
        $sign = $this->genSign($data);
        if ($data['sign'] == $sign) {
            return true;
        } else {
            return false;
        }
    }

    public function sendData($data)
    {
        $body = $data['body'];
        $status = $this->checkSign($body);
        if ($status == false) {
            $data = array(
                'return_code' => 'FAIL',
                'return_msg' => 'Signature invalid',
            );
        } else {
            $data = array('return_code' => 'SUCCESS');
        }

        $res_data['status'] = $status;
        $res_data['return_msg'] = $this->arrayToXml($data);
        $res_data['trade_status_ok'] = $status;

        return $this->response = new WechatCompletePurchaseResponse($this, $res_data);
    }

    protected function verifyBody($body, $signature)
    {
        return $body && $signature && $signature == $this->genSign($body);
    }

    protected function verifyParam($params)
    {
        $signStr = static::httpBuildQueryWithoutNull($params);
        $stringSignTemp = $signStr . '&key=' . $this->getPartnerKey();
        $signValue = strtoupper(md5($stringSignTemp));

        return !empty($params['sign']) && $signValue == $params['sign'];
    }
}
