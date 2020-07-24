<?php
class storejoinAction extends Yaf_Action_Abstract
{
    protected $baseData = [];
    public $check       = [
        'name'    => ['required', '', '请传入姓名'],
        'tel'     => ['required|tel', '', '请传入正确的联系电话'],
        'remarks' => ['', '', ''],
    ];
    /**
     * @method 申请加盟
     * @url    /v1/api/store/join
     * @http   post
     * @desc
     * @param  name       string [必填] 姓名
     * @param  tel        string [必填] 电话(手机号或者座机号)
     * @param  remarks      string [选填] 备注
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
        $param = $this->getController()->verificationParam($this->check, 'post');
        if (!is_array($param)) {
            $this->getController()->error(412, $param);
        }

        $db = $this->getController()->connectMysql();

        $param['createtime'] = time();
        $param['ip']         = ip();
        $db->insert('join', $param);
        if ($db->id() < 1) {
            $this->getController()->error(300, '申请未能提交成功');
        }
        $this->getController()->success(200, '', '申请提交成功，等待客服联系');
    }
}
