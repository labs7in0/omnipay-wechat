<?php namespace Omnipay\WeChat\Message;

use Omnipay\Common\Message\RedirectResponseInterface;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class WechatPurchaseResponse extends BaseAbstractResponse implements RedirectResponseInterface
{

    protected $endpoint = 'weixin://wxpay/bizpayurl';

    protected $return_url = false;

    protected $cancel_url = false;

    protected $fail_url = false;

    protected function arrayExcept($array, $keys)
    {
        return array_diff_key($array, array_flip((array) $keys));
    }

    protected function arrayOnly($array, $keys)
    {
        return array_intersect_key($array, array_flip((array) $keys));
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

    protected static function httpBuildQuery($params, $urlencode = true)
    {
        ksort($params);

        $str = http_build_query($params);

        if ($urlencode == false) {
            $str = urldecode($str);
        }

        return $str;
    }

    protected function genSign($params, $key)
    {
        foreach ($params as $k => $v) {
            $bizParameters[strtolower($k)] = $v;
        }
        $bizString = static::httpBuildQuery($bizParameters, false);
        $bizString .= '&key='. $key;
        return strtoupper(md5($bizString));
    }

    public function isRedirect()
    {
        return true;
    }

    public function isSuccessful()
    {
        return false;
    }

    public function getRedirectUrl()
    {
        $params = $this->getData();
        $key = $params['appkey'];
        $params = $this->arrayExcept($params, 'appkey');
        if (!empty($params['productid'])) {
            $params['product_id'] = $params['productid'];
        }
        $params = $this->arrayOnly($params, ['appid', 'mch_id', 'product_id']);
        $params['time_stamp'] = time();
        $params['nonce_str'] = bin2hex(openssl_random_pseudo_bytes(8));
        $sign = $this->genSign($params, $key);
        ksort($params);
        $url = $this->endpoint . '?sign=' . $sign . '&' . http_build_query($params);
        return $url;
    }

    public function getRedirectMethod()
    {
        return 'GET';
    }

    public function getRedirectData()
    {
        $params = $this->arrayExcept($this->getData(), 'productid');

        $key = $params['appkey'];

        $params = [
            'appId' => $params['appid'],
            'package' => $params['package'],
            'timeStamp' => '' . $params['timestamp'],
            'nonceStr' => $params['noncestr'],
            'signType' => 'MD5',
        ];
        $params['paySign'] = $this->genSign($params, $key);

        return $params;
    }

    public function redirect($content_type = 'html')
    {
        $data = $this->getRedirectData();
        $return_url = $this->return_url ?: $this->arrayGet($_SERVER, 'HTTP_REFERER', false);
        $refered_url = $this->arrayGet($_SERVER, 'HTTP_REFERER', $return_url);
        $cancel_url = $this->cancel_url ?: $refered_url;
        $fail_url = $this->fail_url ?: $refered_url;

        switch ($content_type) {
            case 'html':
                $output = $this->redirectHtml();
                break;
            case 'js':
            case 'javascript':
                $output = $this->redirectJs();
                break;
            default:
                $output = $this->redirectHtml();
        }

        $output = sprintf($output, json_encode($data), $return_url, $cancel_url, $fail_url);

        return HttpResponse::create($output)->send();
    }

    protected function redirectHtml()
    {
        return '<!DOCTYPE html>
        <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <title>微信安全支付</title>
            </head>
            <body style="font-size: 3em;width: 320px;">
                <p>付款中...</p>
            </body>
            <script type="text/javascript">
            ' . $this->redirectJs() . '
            </script>
        </html>';
    }

    protected function redirectJs()
    {
        return '(function(){
        var data = %1$s;

        setTimeout(function(){
            if(typeof WeixinJSBridge == "undefined"){
                return setTimeout(arguments.callee, 200);
            }
            WeixinJSBridge.invoke(
                "getBrandWCPayRequest",data,function(res){
                if(res.err_msg == "get_brand_wcpay_request:ok"){
                    alert("支付成功");
                    window.location.href = "%2$s";
                }else if(res.err_msg == "get_brand_wcpay_request:cancel"){
                    alert("支付取消");
                    window.location.href = "%3$s";
                }else{
                    alert("支付失败(" + res["err_msg"] + ")");
                    window.location.href = "%4$s";
                }
            });
        }, 200);
        })();';
    }

    public function setReturnUrl($url)
    {
        $this->return_url = $url;
    }

    public function setCancelUrl($url)
    {
        $this->cancel_url = $url;
    }

    public function setFailUrl($url)
    {
        $this->fail_url = $url;
    }
}
