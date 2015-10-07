<?php

namespace Omnipay\WeChat\Message;

use Omnipay\Common\Message\AbstractRequest;

abstract class BaseAbstractRequest extends AbstractRequest
{
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

    protected function postStr($url, $data)
    {
        $ch = curl_init();

        $options = array(
            CURLOPT_HEADER => false,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_URL => $url,
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    protected function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";

            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }

        }
        $xml .= "</xml>";
        return $xml;
    }

    protected function xmlToArray($xml)
    {
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }

    protected static function httpBuildQueryWithoutNull($params)
    {
        foreach ($params as $key => $value) {
            if (null == $value || 'null' == $value || 'sign' == $key) {
                unset($params[$key]);
            }
        }
        reset($params);
        ksort($params);

        return http_build_query($params);
    }

    protected static function httpBuildQuery($params)
    {
        ksort($params);

        $str = http_build_query($params);

        return $str;
    }

    protected function genSign($params)
    {
        $bizParameters = array();
        foreach ($params as $k => $v) {
            $bizParameters[strtolower($k)] = $v;
        }
        $bizString = self::httpBuildQueryWithoutNull($bizParameters);
        $bizString .= '&key=' . $this->getAppKey();

        return strtoupper(md5(urldecode($bizString)));
    }
}
