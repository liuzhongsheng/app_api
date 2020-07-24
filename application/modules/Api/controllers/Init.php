<?php

class InitController extends BaseController {
    protected $actions_path = ACTIONS_MODULE_PATH.'/actions/Init/'.API_VERSION.'/';
    public $actions = [];
    public function init()
    {
        parent::init();
        $this->actions = [
            API_CONTROL.'index'  => $this->actions_path.'Index.php',
        ];
    }
}
