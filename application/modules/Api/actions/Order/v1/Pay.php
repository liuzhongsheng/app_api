<?php
class orderpayAction extends Yaf_Action_Abstract
{
    protected $baseData = [];
    public $db          = null;
    public $check       = [
        'curriculum_number' => ['required', '', '请传入课程编号'],
        'order_number'      => ['required', '', '请传入订单编号'],
        'user_number'       => ['required', '', '请传入用户编号'],
        'score'             => ['', 1],
        'content'           => ['', ''],
    ];
    /**
     * @method 订单支付
     * @url    /v1/api/order/pay
     * @http   POST
     * @desc
     * @param  data string [必填] 加密后参数
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
        $data            = $this->getController()->decode();
        $this->loginInfo = $this->getController()->checkLoginStatus();
        // $this->db = $this->getController()->connectMysql();
        // $data     = [
        //     "activity_order_number" => "O159505224523",
        // ];
        // $this->loginInfo['user_number'] = 'W20200616020203684';
        // $this->loginInfo['openid']      = 'o-AWq5SRFK3d3oZ7d5kQWlpxE4AY';
        $data['user_number'] = $this->loginInfo['user_number'];
        $total_fee           = $this->getOrderInfo($data);
        if (!$total_fee) {
            $this->getController()->error(300, '订单信息错误');
        }

        $tempWechatConfig = $this->db->select('config', ['name', 'value'], ['group' => 'wechat_config']);
        $config           = [];
        foreach ($tempWechatConfig as $key => $value) {
            $config[$value['name']] = $value['value'];
        }

        $baseData = [
            'appid'   => $config['appid'], //申请商户号的appid或商户号绑定的appid
            'mchid'   => $config['mch_id'], //微信支付分配的商户号
            'pay_key' => $config['pay_key'], //微信支付key
        ];
        $obj = new SuperPay\Init($baseData);

        // 微信支付
        $data = [
            // 以下选项为必填项
            'class_type_name' => 'Pay', // 操作类型：TransferAccounts 提现 Pay 支付
            'class_name'      => 'Wechat', // 要调用的类名支持：Wechat
            'out_trade_no'    => $data['activity_order_number'], //商户系统内部订单号，要求32个字符内，只能是数字、大小写字母_-|*且在同一个商户号下唯一
            'total_fee'       => $total_fee, //订单总金额，单位为元
            'body'            => '活动商品购买', //商品简单描述，该字段请按照规范传递
            'notify_url'      => $config['notify_url'], //异步接收微信支付结果通知的回调地址，通知url必须为外网可访问的url，不能携带参数。
            'trade_type'      => 'JSAPI', //支持JSAPI支付（或小程序支付）、NATIVE--Native支付、APP--app支付，MWEB--H5支付，不同trade_type决定了调起支付的方式，请根据支付产品正确上传
            'openid'          => 'o-AWq5SRFK3d3oZ7d5kQWlpxE4AY', //trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识
            // 其他选填项可根据自己实际需要添加，参数名需和小程序文档上一致
        ];

        $result = $obj->query($data, 'pay');
        if ($result['return_code'] == 'SUCCESS') {
            $this->getController()->success(200, aesEncrypt(json_encode($result), $this->getController()->key, $this->getController()->iv));
        }

        $this->getController()->error(300, $result['return_msg']);
    }

// 获取订单信息
    public function getOrderInfo($param)
    {
        $param['paytime']    = 0;
        $param['status']     = '待支付';
        $param['deletetime'] = null;
        $data                = $this->db->get('activity_order', 'activity_price', $param);
        if (is_null($data)) {
            return false;
        }
        return $data;
    }
}
