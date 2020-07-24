<?php
class ordercreateAction extends Yaf_Action_Abstract
{
    protected $baseData = [];
    public $param       = [];
    /**
     * @method 创建订单
     * @url    /v1/api/order/create
     * @http   POST
     * @desc   该接口需要登录{"contact_name":"联系人","contact_tel":"联系电话","activity_number":"活动编号","store_number":"门店编号"}
     * @param  data string [必填] 加密后参数
     * @returnValue order_number string 解密后字段为订单编号
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
        $data            = $this->getController()->decode();
        $this->loginInfo = $this->getController()->checkLoginStatus();
        $this->db        = $this->getController()->connectMysql();
        // $data            = [
        //     "contact_name"    => "刘中胜",
        //     "contact_tel"     => "18210560183",
        //     "activity_number" => "A1595052091t",
        // ];
        $data['activity_order_number'] = 'O' . time() . mt_rand(10, 99);
        $data['user_number']           = $this->loginInfo['user_number'];
        $data['createtime']            = time();
        $data['activity_price']        = $this->getActivityPrice($data['activity_number']);
        if (is_null($data['activity_price'])) {
            $this->getController()->error(300, '活动状态异常');
        }
        $this->db->insert('activity_order', $data);
        if ($this->db->id() > 0) {
            $this->getController()->success(200, aesEncrypt(json_encode([
                'order_number' => $data['activity_order_number'],
            ]), $this->getController()->key, $this->getController()->iv));
        }
        $this->getController()->error(300, '订单未能创建成功');
    }

    // 获取活动价格
    protected function getActivityPrice($activity_number)
    {
        return $this->db->get('activity', 'activity_price', [
            'activity_number' => $activity_number,
            'start_time[<]'   => time(),
            'end_time[>]'     => time(),
        ]);
    }

}
