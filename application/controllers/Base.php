<?php
use \Firebase\JWT\JWT;

class BaseController extends Yaf_Controller_Abstract
{
    # 通用数据库连接
    public $db = null;
    public $session;
    public function init()
    {
        $config    = Yaf_Registry::get('config');
        $this->key = $config->auth->aeskey;
        $this->iv  = $config->auth->aesiv;
    }

    // 连接新mysql数据库
    public function connectMysql()
    {
        $arrConfig = Yaf_Registry::get('config');
        $option    = [
            'database_type' => $arrConfig->mysql->db->type,
            'database_name' => $arrConfig->mysql->db->database,
            'server'        => $arrConfig->mysql->db->hostname,
            'username'      => $arrConfig->mysql->db->username,
            'password'      => $arrConfig->mysql->db->password,
            'prefix'        => $arrConfig->mysql->db->prefix,
            'logging'       => $arrConfig->mysql->db->log,
            'charset'       => 'utf8',
        ];
        return new \Medoo\Medoo($option);
    }

    
    /**
     * 用于操作成功后返回执行结果
     *
     * @access protected
     * @param  int $code code码
     * @param mixed $data 要返回的结果
     * @return json 返回类型
     */
    public function success($code = 200, $data = null, $message = '操作成功')
    {
        die(json_encode(['code' => $code, 'success' => true, 'message' => $message, 'data' => $data]));
    }

    /**
     * 用于操作失败后返回执行结果
     *
     * @access protected
     * @param  int $code code码
     * @param mixed $data 要返回的结果
     * @return json 返回类型
     */
    public function error($code = 300, $message = '')
    {
        if ($code == 300 || $code == 412 || $message != '') {
            die(json_encode(['code' => $code, 'success' => false, 'message' => $message]));
        }
        $mssageData = ['success' => false];
        $result     = array_merge($mssageData, loadLang($code));
        die(json_encode($result));

    }

    /*
     * 检测登录状态
     **/
    public function checkLoginStatus($required = true)
    {

        if(!isset($_SERVER['HTTP_AUTHORIZATION'])){
            if($required != false){
                header('HTTP/1.1 401 Unauthorized');
                die();
            }
            return false;
        }
        $authorization = $_SERVER['HTTP_AUTHORIZATION'];
        $authorization = ltrim($authorization,'Basic ');
        if(empty($authorization)){
            header('HTTP/1.1 401 Unauthorized');
            die();
        }
        $authorization = base64_decode($authorization);
        $authorization = explode('|', $authorization);
        $db = $this->connectMysql();
        $userInfo = $db->get('wechat_user','*',['user_number'=>$authorization[1]]);
        if(is_null($userInfo)){
            header('HTTP/1.1 401 Unauthorized');
            die();
        }
        $token = md5(md5($userInfo['user_number'].$userInfo['openid']));
        if($token !== $authorization[0]){
            header('HTTP/1.1 401 Unauthorized');
            die();
        }
        return $userInfo;
    }

    /**
     * 参数校验
     * @param array ;
    [
    '字段名称'    => ['required','','该字段必须填写'],
    ]
    参数格式如下
    规则支持：required,和空("")
    默认值
    提示信息
    @param $http 请求方式：支持：post,query 默认post
     **/
    public function verificationParam($checkRule = [], $http = 'post')
    {
        $result = [];
        foreach ($checkRule as $key => $value) {
            switch ($http) {
                case 'query':
                    $param = $this->getRequest()->getQuery($key, $value[1]);
                    break;
                default:
                    $param = $this->getRequest()->getPost($key, $value[1]);

            }
            if ('' != $value[0]) {
                $rule = explode('|', $value[0]);
                foreach ($rule as $v) {
                    switch ($v) {
                        case 'required':
                            if ('' == $param) {
                                return $value[2];
                            }
                            break;
                        case 'mobile':
                            if (!isPhone($param)) {
                                return $value[2];
                            }
                            break;
                        case 'tel':
                            if (!isTel($param)) {
                                return $value[2];
                            }
                            break;
                    }
                }
            }
            $result[$key] = $param;
        }
        return $result;
    }

    // 微信access_token获取
    public function getAccessToken($param)
    {
        try {
            // 获取token
            $redisConfig = Yaf_Registry::get('redis');
            $redis       = RedisDb::getInstance($redisConfig);
            $redis->select(13);
            $key = 'access_token_' . $param['appid'];
            // $token = $redis->getKey($key);
            // if (!$token) {
            $url = sprintf("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s", $param['appid'], $param['app_secret']);
            // var_dump($url);
            $info = file_get_contents($url); //发送HTTPs请求并获取返回的数据，推荐使用curl
            $json = json_decode($info); //对json数据解码
            $arr  = get_object_vars($json);
            if (!array_key_exists('errcode', $arr)) {
                $redis->setKey($key, $arr['access_token']);
                $redis->expire($key, 7100);
                return $arr['access_token'];
            }
            throw new Exception($arr['errcode']);
            // }
            // return $token;
        } catch (Exception $e) {
            $this->getController()->error($e->getMessage());
        }
    }
    // 解密
    public function decode()
    {
        if (!$this->getRequest()->isPost()) {
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            exit;
        }
        $data = $this->getRequest()->getPost('data');
        if (empty($data)) {
            Header("HTTP/1.1 401 Unauthorized");
            exit;
        }
        $data = aesDecrypt($data, $this->key, $this->iv);
        if ($data === false) {
            Header("HTTP/1.1 401 Unauthorized");
            exit;
        }
        return json_decode($data, true);
    }

}
