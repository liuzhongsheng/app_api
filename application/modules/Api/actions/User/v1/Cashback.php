<?php
class usercashbackAction extends Yaf_Action_Abstract
{
    protected $baseData = [];
    public $check       = [
        'pagesize' => ['', 1, ''],
        'pageNum'  => ['', 10, ''],
    ];
    /**
     * @method 我的邀请
     * @url    /v1/api/user/cashback
     * @http   GET
     * @desc
     * @param  pagesize        string [必填] 下一页页码
     * @param  pageNum         string [必填] 每页显示数量，默认6
     * @author 李黑帅
     * @copyright 2019/07/04
     * @return
     */
    public function execute()
    {
        if (!$this->getRequest()->isGet()) {
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            exit;
        }
        $param = $this->getController()->verificationParam($this->check, 'query');
        if (!is_array($param)) {
            $this->getController()->error(412, $param);
        }
        $this->loginInfo = $this->getController()->checkLoginStatus();
        // $this->loginInfo['user_number'] = 'W20200616020203684';
        $this->db = $this->getController()->connectMysql();
        // 获取所有下级信息
        $user = $this->db->select('wechat_user', 'user_number');
        $data = $this->db->select('user_money_log', [
            '[>]wechat_user' => ['user_number' => 'user_number'],
        ], [
            'wechat_user.profile_photo',
            'wechat_user.nickname',
            'user_money_log.money',
        ], [
            'user_money_log.user_number' => $user,
            'ORDER'                      => ['user_money_log.createtime' => 'DESC'],
            'LIMIT'                      => [($param['pagesize'] - 1) * $param['pageNum'], $param['pageNum']],
        ]);
        $data = [

            'invite_to_buy'        => $this->loginInfo['invite_to_buy'],
            'invite_to_buy_amount' => $this->loginInfo['invite_to_buy_amount'],
            'pagesize'             => $param['pagesize'] + 1,
            'data'                 => $data,
        ];
        $this->getController()->success(200, $data);
    }

}
