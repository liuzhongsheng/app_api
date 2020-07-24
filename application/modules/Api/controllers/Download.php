<?php

class DownloadController extends BaseController {
    

    public function downloadapiwordAction()
    {
        $config  = include APP_PATH . '/config/ExportApiDocConfig.php';
        $wordObj = new \ExportApiDoc($config,'纤丽兰心v1接口文档');
        $data    = $wordObj->getDoc();
        $this->getView()->assign('file_name',$data['file_name']);
        $this->getView()->assign('url',$data['url']);
    }

    /**
     * 获取appkey
     **/
    protected function getAppKey()
    {

    }
}