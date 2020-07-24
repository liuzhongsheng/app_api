<?php
class BaiduAi
{
    protected $config;

    public function __construct($config = [])
    {
        $this->config = $config;
    }


    // 获取access_token
    public function getAccessToken()
    {
        $url                        = 'https://aip.baidubce.com/oauth/2.0/token';
        $post_data['grant_type']    = 'client_credentials';
        $post_data['client_id']     = $this->config['baidu_ai_apikey'];
        $post_data['client_secret'] = $this->config['baidu_ai_secret_key'];
        $o                          = "";
        foreach ($post_data as $k => $v) {
            $o .= "$k=" . urlencode($v) . "&";
        }
        $post_data = substr($o, 0, -1);

        return $this->request_post($url, $post_data);
    }

    //人体关键点识别
    public function getBodyAnalysis($token,$filePath)
    {
        $url = 'https://aip.baidubce.com/rest/2.0/image-classify/v1/body_analysis?access_token=' . $token;
        $img = file_get_contents($filePath);
        $img = base64_encode($img);
        $bodys = array(
            'image' => $img
        );
        $res = request_post($url, $bodys);
    }

    protected function request_post($url = '', $param = '')
    {
        if (empty($url) || empty($param)) {
            return false;
        }

        $postUrl  = $url;
        $curlPost = $param;
        $curl     = curl_init(); //初始化curl
        curl_setopt($curl, CURLOPT_URL, $postUrl); //抓取指定网页
        curl_setopt($curl, CURLOPT_HEADER, 0); //设置header
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($curl, CURLOPT_POST, 1); //post提交方式
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($curl); //运行curl
        curl_close($curl);

        return $data;
    }
}
