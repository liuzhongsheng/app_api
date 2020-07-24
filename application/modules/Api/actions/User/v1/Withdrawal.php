<?php
class userwithdrawalAction extends Yaf_Action_Abstract
{
    protected $baseData = [];
    /**
     * @method 提现申请
     * @url    /v1/api/user/withdrawal
     * @http   post
     * @desc
     * @param  data        string [必填] {"amount":"10"}
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
        $this->data = $this->getController()->decode();
        // $this->data = [
        //     'amount' => 0.1,
        // ];

        $this->loginInfo = $this->getController()->checkLoginStatus();
        // $this->loginInfo['user_number'] = 'W20200616020203684';
        $database = $this->getController()->connectMysql();
        if ($this->loginInfo['current_income_amount'] < $this->data['amount']) {
            $this->getController()->error(50001);
        }
        $temp   = $db->select('config', ['name', 'value'], ['group' => 'withdrawal']);
        $config = [];
        foreach ($temp as $key => $value) {
            $config[$value['name']] = $value['value'];
        }
        if ($this->data['amount'] < $config['min_withdrawal']) {
            $this->getController()->error(50003);
        }
        if ($this->data['amount'] > $config['max_withdrawal']) {
            $this->getController()->error(50004);
        }
        //       $baseData = [
        //     'mch_appid' => '', //申请商户号的appid或商户号绑定的appid
        //     'mchid'     => '', //微信支付分配的商户号
        //     'pay_key'   => '', //微信支付key
        // ];
        // $obj = new SuperPay\Init($baseData);
        // $data = [
        //     'class_type_name'  => 'TransferAccounts', // 操作类型：TransferAccounts 提现 Pay 支付
        //     'class_name'       => 'Wechat', // 要调用的类名支持：Wechat
        //     'device_info'      => '', // 设备号,选填，微信支付分配的终端设备号
        //     'partner_trade_no' => '', // 订单号商户订单号，需保持唯一性(只能是字母或者数字，不能包含有其它字符)
        //     'openid'           => '', // 用户openid
        //     'check_name'       => '', // 校验用户姓名选项 NO_CHECK：不校验真实姓名 FORCE_CHECK：强校验真实姓名
        //     //'re_user_name'     => '', // 收款用户真实姓名。如果check_name设置为FORCE_CHECK，则必填用户真实姓名
        //     'amount'           => '', // 企业付款金额，单位为元
        //     'desc'             => '', // 企业付款备注，必填。注意：备注中的敏感词会被转成字符*
        // ];
        // $obj->query($data);
        $result = $database->action(function ($database) {
            $withdrawal = [
                'withdrawal_order_number' => 'T' . time() . mt_rand(10, 99),
                'user_number'             => $this->loginInfo['user_number'],
                'amount'                  => $this->data['amount'],
                'createtime'              => time(),
            ];
            $database->insert('withdrawal', $withdrawal);

            if ($database->id() > 0) {
                $temp = [
                    'user_number' => $this->loginInfo['user_number'],
                    'money'       => $this->data['amount'],
                    'event'       => '提现',
                    'before'      => $this->loginInfo['current_income_amount'],
                    'after'       => $this->loginInfo['current_income_amount'] + $this->data['amount'],
                    'memo'        => '提现订单-' . $withdrawal['withdrawal_order_number'],
                    'createtime'  => time(),
                ];
                $database->insert('user_money_log', $temp);
                if ($database->id() > 0) {
                    $UpdateUserData = [
                        'current_income_amount[-]' => $this->data['amount'],
                        'withdrawal_amount[+]'     => $this->data['amount'],
                    ];
                    $res = $database->update('wechat_user', $UpdateUserData, [
                        'user_number' => $this->loginInfo['user_number'],
                    ]);
                    if ($res->rowCount() > 0) {
                        return true;
                    }
                }
            }
            return false;
        });

        if (!$result) {
            $this->getController()->error(50002);
        }
        $this->getController()->success(200, '', '提交成功等待处理');
    }
}
