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

    public function setAppKey($key)
    {
        $this->setParameter('app_key', $key);
    }

    public function getAppKey()
    {
        return $this->getParameter('app_key');
    }

    public function setPartner($id)
    {
        $this->setParameter('partner', $id);
    }

    public function getPartner()
    {
        return $this->getParameter('partner');
    }

    public function setPartnerKey($key)
    {
        $this->setParameter('partner_key', $key);
    }

    public function getPartnerKey()
    {
        return $this->getParameter('partner_key');
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

    protected function arrayExcept($array, $keys)
    {
        return array_diff_key($array, array_flip((array) $keys));
    }

    protected function arrayOnly($array, $keys)
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }

    protected function arrayKeyMap($array, $keymap)
    {
        $keys = array_keys($keymap);
        $arr = $this->arrayOnly($array, $keys);
        $array = $this->arrayExcept($array);
        foreach ($arr as $k => $v) {
            $array[$keymap[$k]] = $v;
        }
        return $array;
    }

    protected function arrayGet($array, $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return value($default);
            }

            $array = $array[$segment];
        }

        return $array;
    }

    protected function postStr($url, $string)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $string);

        $data = curl_exec($ch);

        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            return false;
        }
    }

    protected static function httpBuildQueryWithoutNull($params)
    {
        foreach ($params as $key => $value) {
            if (null == $value || 'null' == $value || 'sign' != $key) {
                unset($params[$key]);
            }
        }
        reset($params);
        ksort($params);

        return http_build_query($params);
    }

    protected static function httpBuildQuery($params, $urlencode = true)
    {
        ksort($params);

        $str = http_build_query($params);

        if ($urlencode == false) {
            $str = urldecode($str);
        }

        return $str;
    }

    protected function genSign($params)
    {
        foreach ($params as $k => $v) {
            $bizParameters[strtolower($k)] = $v;
        }
        $bizString = static::httpBuildQuery($bizParameters, false);
        $bizString .= '&key=' . $this->getAppKey();
        return strtoupper(md5($bizString));
    }
}
