<?php
namespace Pay;

class WechatApp extends PayAbstract
{
    public $data;
    public function commit($data, $event)
    {
        $this->data = $data;
        return $this->$event();
    }


    //统一下单接口
    private function unifiedorder()
    {
        $url        = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $parameters = [
            'appid'            => $this->data['appid'], //小程序 ID
            'mch_id'           => $this->data['mch_id'], //商户号
            'nonce_str'        => $this->createNoncestr(), //随机字符串
            'body'             => $this->data['body'],
            'out_trade_no'     => $this->data['out_trade_no'],
            'total_fee'        => $this->data['total_fee'] * 100,
            'spbill_create_ip' => $this->data['spbill_create_ip'], //终端 IP
            'notify_url'       => $this->data['notify_url'], //通知地址  确保外网能正常访问
            'openid'           => $this->data['openid'], //用户 id
            'trade_type'       => 'JSAPI', //交易类型
        ];
        $parameters['sign'] = $this->getSign($parameters); //统一下单签名
        $xmlData            = $this->arrayToXml($parameters);
        $return             = $this->xmlToArray($this->postXmlCurl($xmlData, $url, 60));
        return array_merge($return, $parameters);
    }

    //订单查询
    public function find()
    {

        $url        = 'https://api.mch.weixin.qq.com/pay/orderquery';
        $parameters = [
            'appid'        => $this->data['appid'], //小程序 ID
            'mch_id'       => $this->data['mch_id'], //商户号
            'nonce_str'    => $this->createNoncestr(), //随机字符串
            'out_trade_no' => $this->data['out_trade_no'],
        ];
        $parameters['sign'] = $this->getSign($parameters); //统一下单签名
        $xmlData            = $this->arrayToXml($parameters);
        $return             = $this->xmlToArray($this->postXmlCurl($xmlData, $url, 60));
        return $return;
    }
    private static function postXmlCurl($xml, $url, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //严格校验
        //设置 header
        curl_setopt($ch, CURLOPT_HEADER, false);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //post 提交方式
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 40);
        set_time_limit(0);
        //运行 curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new WxPayException("curl 出错，错误码:$error");
        }
    }

    //数组转换成 xml
    private function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $xml .= "<" . $key . ">" . arrayToXml($val) . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    //xml 转换成数组
    private function xmlToArray($xml)
    {
        //禁止引用外部 xml 实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val       = json_decode(json_encode($xmlstring), true);
        return $val;
    }

    //微信小程序接口
    private function pay()
    {
        //统一下单接口
        $unifiedorder = $this->unifiedorder();
        $parameters   = array(
            'appId'     => $this->data['appid'], //小程序 ID
            'timeStamp' => '' . time() . '', //时间戳
            'nonceStr'  => $this->createNoncestr(), //随机串
            'package'   => 'prepay_id=' . $unifiedorder['prepay_id'], //数据包
            'signType'  => 'MD5', //签名方式
        );
        //签名
        $parameters['paySign']  = $this->getSign($parameters);
        $parameters['pay_info'] = $unifiedorder;
        return $parameters;

    }

    //作用：产生随机字符串，不长于 32 位
    private function createNoncestr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str   = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    ///作用：格式化参数，签名过程需要使用
    private function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }
    //作用：生成签名
    private function getSign($Obj)
    {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //签名步骤二：在 string 后加入 KEY
        $String = $String . "&key=" . $this->data['pay_key'];
        //签名步骤三：MD5 加密
        $String = md5($String);
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        return $result_;
    }

}
