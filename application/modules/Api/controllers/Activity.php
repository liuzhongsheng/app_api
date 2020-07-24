<?php

class ActivityController extends BaseController
{

    protected $actions_path = ACTIONS_MODULE_PATH . '/actions/Activity/' . API_VERSION . '/';
    public $actions         = [];
    public function init()
    {
        parent::init();
        # 如果请求不到的地址则自动进入actions寻找文件
        $this->actions = [
            API_CONTROL . 'list'   => $this->actions_path . 'List.php',     // 活动列表
            API_CONTROL . 'detail' => $this->actions_path . 'Detail.php',   // 活动详情
            API_CONTROL . 'forwardingvolume' => $this->actions_path . 'Forwardingvolume.php',   // 活动详情
        ];
    }

}
