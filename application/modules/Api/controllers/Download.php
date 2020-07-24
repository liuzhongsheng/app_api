<?php

class DownloadController extends BaseController {
    

    public function downloadapiwordAction()
    {
        $config  = include APP_PATH . '/config/ExportApiDocConfig.php';
        $wordObj = new \ExportApiDoc($config,'接口v1接口文档');
        $data    = $wordObj->getDoc();
        $this->getView()->assign('file_name',$data['file_name']);
        $this->getView()->assign('url',$data['url']);
    }

}