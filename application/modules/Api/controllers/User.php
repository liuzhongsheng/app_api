<?php

class UserController extends BaseController
{

    protected $actions_path = ACTIONS_MODULE_PATH . '/actions/User/' . API_VERSION . '/';
    public $actions         = [];
    public function init()
    {
        parent::init();
        # 如果请求不到的地址则自动进入actions寻找文件
        $this->actions = [
            API_CONTROL . 'index'         => $this->actions_path . 'Index.php',
            API_CONTROL . 'cashback'      => $this->actions_path . 'Cashback.php', // 我的邀请页面
            API_CONTROL . 'withdrawal'    => $this->actions_path . 'Withdrawal.php',
            API_CONTROL . 'withdrawallog' => $this->actions_path . 'Withdrawallog.php', // 员工列表
        ];
    }

}
