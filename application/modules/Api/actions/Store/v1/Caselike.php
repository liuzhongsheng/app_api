<?php
class storecaselikeAction extends Yaf_Action_Abstract
{
    protected $baseData = [];
    public $check       = [
        'case_number' => ['required', '', '请传入案例编号'],
    ];
    /**
     * @method 案例点赞
     * @url    /v1/api/store/caselike
     * @http   POST
     * @desc
     * @param  case_number     string [必填] 案例编号

     * @author 李黑帅
     * @copyright 2019/07/04
     * @return
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            exit;
        }
        $this->loginInfo = $this->getController()->checkLoginStatus();
        $param           = $this->getController()->verificationParam($this->check, 'post');
        if (!is_array($param)) {
            $this->getController()->error(412, $param);
        }
        $db = $this->getController()->connectMysql();
        // $this->loginInfo['user_number'] = 'W20200616020203684';
        $data = [
            'user_number' => $this->loginInfo['user_number'],
            'case_number' => $param['case_number'],
        ];
        if ($db->has('store_case_like', $data)) {
            $res = $db->delete('store_case_like', $data);
            if ($res->rowCount() > 0) {
                $db->update('case', [
                    'case_like_num[-]' => 1,
                ], [
                    'case_number' => $param['case_number'],
                ]);
            }
            $this->getController()->success(200, '取消成功');
        }
        $db->insert('store_case_like', $data);
        if ($db->id() > 0) {
            $db->update('case', [
                'case_like_num[+]' => 1,
            ], [
                'case_number' => $param['case_number'],
            ]);
        }
        $this->getController()->success(200, '点赞成功');
    }

}
