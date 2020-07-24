<?php

class OrderController extends BaseController
{

    protected $actions_path = ACTIONS_MODULE_PATH . '/actions/Order/' . API_VERSION . '/';
    public $actions         = [];
    public function init()
    {
        parent::init();
        # 如果请求不到的地址则自动进入actions寻找文件
        $this->actions = [
            API_CONTROL . 'create' => $this->actions_path . 'Create.php', //创建订单
            API_CONTROL . 'detail' => $this->actions_path . 'Detail.php', //订单详情
            API_CONTROL . 'pay'    => $this->actions_path . 'Pay.php', //我的订单
            API_CONTROL . 'list'   => $this->actions_path . 'List.php', //订单列表
            API_CONTROL . 'notify' => $this->actions_path . 'Notify.php',
        ];
    }
}
