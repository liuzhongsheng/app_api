
<?php
class wechatloginAction extends Yaf_Action_Abstract
{
    protected $baseData = [];
    protected $db;
    protected $config;
    /**
     * @method 微信登录
     * @url    /v1/api/wechat/login
     * @http   post
     * @desc  该接口会在header里返回authorization，该字段用于校验登录状态
     * @param  code             string [必填] 微信返回的code
     * @param  pic              string [必填] 头像
     * @param  nickName         string [必填] 昵称
     * @param  sex              string [必填] 性别
     * @param  province         string [选填] 省
     * @param  city             string [选填] 市
     * @param  area             string [选填] 区
     * @author 李黑帅
     * @copyright 2019/07/04
     * @return
     */
    public function execute()
    {

        if (!$this->getRequest()->isPost()) {
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            exit;
        }
        // header('authorization:Basic '.base64_encode(md5(md5('W20200616020203684'.'o-AWq5SRFK3d3oZ7d5kQWlpxE4AY')).'|'.'W20200616020203684'));
        $code                           = $this->getRequest()->getPost('code', '1');
        if (empty($code)) {
            $this->getController()->error(412, '请传入code码');
        }
        $data['profile_photo'] = $this->getRequest()->getPost('pic', '2');
        if (empty($data['profile_photo'])) {
            $this->getController()->error(412, '请传入头像');
        }
        $data['nickname'] = $this->getRequest()->getPost('nickName', '3');
        if (empty($data['nickname'])) {
            $this->getController()->error(412, '请传递昵称');
        }
        $data['sex']      = $this->getRequest()->getPost('sex', '0');
        $data['province'] = $this->getRequest()->getPost('province', '');
        $data['city']     = $this->getRequest()->getPost('city', '');
        $data['area']     = $this->getRequest()->getPost('area', '');
        $this->db = $this->getController()->connectMysql();
        $tempConfig = $this->db->select('config',['name','value'],['group'=>'wechat_config']);
        $this->config = [];
        if(!empty($tempConfig)){
            foreach ($tempConfig as $key => $value) {
                $this->config[$value['name']] =$value['value'];
            }
        }
        // 获取openid
        $openid = $this->getUserInfo($code);
        if (is_array($openid)) {
            $this->getController()->error($openid['code'], '数据异常-'.$openid['message']);
        }
        // 检测微信用户是否存在
        $user_number = $this->db->get('wechat_user', 'user_number', [
            'openid'         => $openid,
        ]);

        if (is_null($user_number)) {
            // 如果不存在则添加数据
            $user_number     = 'W' . date('Ymdhis') . mt_rand(10, 99);
            $data['user_number']    = $user_number;
            $data['openid']         = $openid;
            $data['createtime']     = time();
            $this->db->insert('wechat_user', $data);
        } else {
            // 如果存在则更新数据
            $data['updatetime']     = time();
            $this->db->update('wechat_user', $data, ['openid' => $openid]);
        }
        header('authorization:Basic '.base64_encode(md5(md5($user_number.$openid)).'|'.$user_number));
        $this->getController()->success(200, '','登录成功');
    }

    public function getUserInfo($code)
    {
        $url          = sprintf('https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code', $this->config['appid'], $this->config['app_secret'], $code);
        $info         = file_get_contents($url); //发送HTTPs请求并获取返回的数据，推荐使用curl
        $json         = json_decode($info); //对json数据解码
        $arr          = get_object_vars($json);
        $arr['appid'] = $this->config['appid'];
        if ($arr['errcode'] == 0) {
            $openid = $arr['openid'];
            return $openid;
        }
        return [
            'error'   => true,
            'code'    => $arr['errcode'],
            'message' => $arr['errmsg'],
        ];

    }

}