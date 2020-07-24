<?php
class storecallupAction extends Yaf_Action_Abstract
{
    protected $baseData = [];
    public $check       = [
        'tel'     => ['required|tel', '18210560183', '请传入正确的联系电话'],
        'address' => ['', '', ''],
    ];
    /**
     * @method 拨打电话
     * @url    /v1/api/store/callup
     * @http   post
     * @desc
     * @param  tel        string [必填] 电话(手机号或者座机号)
     * @param  address    string [选填] 详细地址
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
        $db->insert('call_up', $param);
        if ($db->id() < 1) {
            $this->getController()->error(300, '提交失败');
        }
        $this->getController()->success(200, '', '提交成功');
    }
}
