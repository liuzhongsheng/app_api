<?php
class userindexAction extends Yaf_Action_Abstract
{
    protected $baseData = [];
    /**
     * @method 个人中心
     * @url    /v1/api/user/index
     * @http   GET
     * @desc
     * @author 李黑帅
     * @copyright 2019/07/04
     * @return
     */
    public function execute()
    {
        if (!$this->getRequest()->isGet()) {
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            exit;
        }
        // $this->baseData = $this->getController()->checkToken();
        // $this->baseData['app_class_id'] = 127;
        // $this->baseData['number']       = 'C158651162032';
        // $this->baseData['gloab'] = 'true';
        $this->loginInfo = $this->getController()->checkLoginStatus();
        $this->db        = $this->getController()->connectMysql();
        $data            = [
            'current_income_amount' => $this->loginInfo['current_income_amount'],
            'total_income_amount'   => $this->loginInfo['current_income_amount'],
            'withdrawal_amount'     => $this->loginInfo['withdrawal_amount'],
        ];
        $this->getController()->success(200, $data);
    }

}
