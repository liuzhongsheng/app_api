<?php
 /**
    * 微信H5下单付款
    *   @order 付款信息
    *   @bodys 付款内容$order_number,$money
    **/
    public function wxmobielpay(){
        header("Content-type:text/html;charset=utf-8");
        require_once VENDOR_PATH.'/wxpay/WxPay.Config.php';
        $order = input('order');
        $money = input('money');
        $wen = input('wen')?input('wen'):'';
        //dump($order);dump($money);dump($wen);die();
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";//微信传参地址
        //1.获取调用统一下单接口所需必备参数
        $WxPayConfig = new \WxPayConfig();
        $appid = $WxPayConfig -> GetAppId();//微信公众号appid
        $mch_id = $WxPayConfig -> GetMerchantId();//微信支付商户号
        $key = $WxPayConfig -> GetKey();//自己设置的微信商家key
        $out_trade_no = $order;//平台内部订单号
        //$out_trade_no = '212112145';//平台内部订单号
        $nonce_str=MD5($out_trade_no);//随机字符串
        $body = '商品购买';//付款内容
        $total_fee = $money*100;//付款金额，单位为分
        //$total_fee = 1;//付款金额，单位为分
        $spbill_create_ip = $this -> get_client_ip(); //获得用户设备IP
        $attach = 'weixinh5';//附加数据（自定义，在支付通知中原样返回）
		//回调地址
        $notify_url = $WxPayConfig -> GetNotifyUrl();//异步回调地址，需外网可以直接访问
        $trade_type = 'MWEB';//交易类型，微信H5支付时固定为MWEB

        //2.将参数按照key=value的格式，并按照参数名ASCII字典序排序生成字符串
        $signA ="appid=$appid&attach=$attach&body=$body&mch_id=$mch_id&nonce_str=$nonce_str&notify_url=$notify_url&out_trade_no=$out_trade_no&spbill_create_ip=$spbill_create_ip&total_fee=$total_fee&trade_type=$trade_type";
        //3.拼接字符串
                $strSignTmp = $signA."&key=$key";
                //4.MD5加密后转换成大写
        $sign = strtoupper(MD5($strSignTmp));
                //5.拼接成所需XML格式
        $post_data = "<xml> 
                <appid>$appid</appid> 
                <attach>$attach</attach> 
                <body>$body</body> 
                <mch_id>$mch_id</mch_id> 
                <nonce_str>$nonce_str</nonce_str> 
                <notify_url>$notify_url</notify_url> 
                <out_trade_no>$out_trade_no</out_trade_no> 
                <spbill_create_ip>$spbill_create_ip</spbill_create_ip> 
                <total_fee>$total_fee</total_fee> 
                <trade_type>$trade_type</trade_type>
                <sign>$sign</sign> 
                </xml>";
                //6.以POST方式向微信传参，并取得微信返回的支付参数
        $dataxml = $this -> httpRequest($url,'POST',$post_data);
        $objectxml = (array)simplexml_load_string($dataxml, 'SimpleXMLElement', LIBXML_NOCDATA); //将微信返回的XML转换成数组
         //echo "1";die();
        if($objectxml['return_code'] == 'SUCCESS'){
            if($objectxml['result_code'] == 'SUCCESS')//如果这两个都为此状态则返回mweb_url，详情看‘统一下单’接口文档
	            $return_url = "回调地址";
                $urls = $objectxml['mweb_url'] . '&redirect_url=' . urlencode($return_url);
                //访问这个url  但是我在用下面的方法访问是 报错商家信息有误 所以我把url 放到视图中 跳转
                // header("Location:$urls");
                //return $urls;
                $this -> assign('url', $urls);
                return view('../../../template/mobile_pay');
                //mweb_url是微信返回的支付连接要把这个连接分配到前台
            if($objectxml['result_code'] == 'FAIL')
				return $err_code_des = $objectxml['err_code_des'];
        }

    }
    //这两个方法需要调用到  也要引入
   public function get_client_ip() {
	    if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
	        $ip = getenv('HTTP_CLIENT_IP');
	    } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
	        $ip = getenv('HTTP_X_FORWARDED_FOR');
	    } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
	        $ip = getenv('REMOTE_ADDR');
	    } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
	        $ip = $_SERVER['REMOTE_ADDR'];
	    }
	    return preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
	}
	public function httpRequest($url, $method, $postfields = null, $headers = array(), $debug = false) {
        $method = strtoupper($method);
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60); /* 在发起连接前等待的时间，如果设置为0，则无限等待 */
        curl_setopt($ci, CURLOPT_TIMEOUT, 7); /* 设置cURL允许执行的最长秒数 */
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        switch ($method) {
        case "POST":
            curl_setopt($ci, CURLOPT_POST, true);
            if (!empty($postfields)) {
            $tmpdatastr = is_array($postfields) ? http_build_query($postfields) : $postfields;
            curl_setopt($ci, CURLOPT_POSTFIELDS, $tmpdatastr);
            }
            break;
        default:
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method); /* //设置请求方式 */
            break;
        }
        $ssl = preg_match('/^https:\/\//i',$url) ? TRUE : FALSE;
        curl_setopt($ci, CURLOPT_URL, $url);
        if($ssl){
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE); // 不从证书中检查SSL加密算法是否存在
        }
        curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ci, CURLOPT_MAXREDIRS, 2);/*指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的*/
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, true);
        $response = curl_exec($ci);
        $requestinfo = curl_getinfo($ci);
        if ($debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);
            echo "=====info===== \r\n";
            print_r($requestinfo);
            echo "=====response=====\r\n";
            print_r($response);
        }
        curl_close($ci);
        return $response;
    }