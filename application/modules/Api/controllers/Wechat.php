<?php

class WechatController extends BaseController {
    
    protected $actions_path = ACTIONS_MODULE_PATH.'/actions/Wechat/'.API_VERSION.'/';
    public $actions = [];
    public function init()
    {
        parent::init();
        $this->actions = [
        	// 微信通用登录
            API_CONTROL.'login'         => $this->actions_path.'Login.php',  
        ];
    }


}