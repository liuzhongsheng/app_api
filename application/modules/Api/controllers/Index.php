<?php

class IndexController extends BaseController {
    protected $actions_path = ACTIONS_MODULE_PATH.'/actions/Index/'.API_VERSION.'/';
    public $actions = [];
    public function init()
    {
        parent::init();
        $this->actions = [
            API_CONTROL.'index'  => $this->actions_path.'Index.php',
        ];
    }
}
