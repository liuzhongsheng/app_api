<?php

class StoreController extends BaseController
{

    protected $actions_path = ACTIONS_MODULE_PATH . '/actions/Store/' . API_VERSION . '/';
    public $actions         = [];
    public function init()
    {
        parent::init();
        # 如果请求不到的地址则自动进入actions寻找文件
        $this->actions = [
            API_CONTROL . 'info'          => $this->actions_path . 'Info.php', // 门店详情
            // API_CONTROL.'score'    => $this->actions_path.'Score.php', // 门店列表
            API_CONTROL . 'case'          => $this->actions_path . 'Case.php', // 案例列表

            // API_CONTROL.'activity'    => $this->actions_path.'Activity.php',
            API_CONTROL . 'caselike'      => $this->actions_path . 'Caselike.php',
            API_CONTROL . 'caseinfo'      => $this->actions_path . 'Caseinfo.php', // 案例详情
            API_CONTROL . 'list'          => $this->actions_path . 'List.php', // 门店列表
            API_CONTROL . 'staff'         => $this->actions_path . 'Staff.php', // 员工列表
            API_CONTROL . 'join'         => $this->actions_path . 'Join.php', 
            API_CONTROL . 'callup'         => $this->actions_path . 'Callup.php', 
        ];
    }

}