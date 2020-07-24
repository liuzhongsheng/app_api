<?php
class activityforwardingvolumeAction extends Yaf_Action_Abstract
{
    protected $baseData = [];
    protected $db       = null;
    protected $domain;
    protected $param;
    public $check = [
        'activity_number' => ['required', 'A010', '请传入活动编号'],
    ];
    /**
     * @method 记录转发次数
     * @url    /v1/api/activity/forwardingvolume
     * @http   GET
     * @desc
     * @param  activity_number    string [必填] 活动编号
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
        try {
            $this->param = $this->getController()->verificationParam($this->check, 'query');
            if (!is_array($this->param)) {
                $this->getController()->error(412, $this->param);
            }
            $this->db = $this->getController()->connectMysql();

            // 更新浏览量
            $this->db->update('activity', [
                'forwarding_volume[+]' => 1,
            ], [
                'activity_number' => $this->param['activity_number'],
            ]);
            $this->getController()->success(200, $data);
        } catch (Exception $e) {
            debugLog($e->getMessage());
            $this->getController()->error(9003);
        }
    }

}
