<?php
class orderdetailAction extends Yaf_Action_Abstract
{
    protected $baseData = [];
    protected $domain;
    /**
     * @method 订单详情
     * @url    /v1/api/order/detail
     * @http   post
     * @desc
     * @param  data  string [必填] 加密后的参数{"activity_order_number":"订单号"}
     * @returnValue order          []      订单数据，解密后获得
     * @returnValue contact_name   string 联系人
     * @returnValue contact_tel    string 联系电话
     * @returnValue activity                [] 活动
     * @returnValue activity_number         string 活动-活动编号
     * @returnValue activity_thumb_src    string 活动-活动封面图
     * @returnValue activity_title          string 活动-活动标题
     * @returnValue activity_price          string 活动-活动价格
     * @returnValue store           [] 门店
     * @returnValue store_number    string 门店编号
     * @returnValue store_thumb_src string 门店封面图
     * @returnValue store_name      string 门店名称
     * @returnValue business_hours  string 营业时间
     * @returnValue address         string 门店地址
     * @returnValue contact_number  string 联系电话
     * @author 李黑帅
     * @copyright 2019/07/04
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
        //     "activity_order_number" => "O159505224523",
        // ];
        // $this->loginInfo['user_number'] = 'W20200616020203684';
        $data['user_number'] = $this->loginInfo['user_number'];
        $order               = $this->getOrderInfo($data);
        if (!$order) {
            $this->getController()->error(300, '订单信息错误');
        }

        // 获取域名配置信息
        $domainConfig = $this->db->select('config', ['name', 'value'], ['group' => 'domain']);
        $this->domain = [];
        foreach ($domainConfig as $key => $value) {
            $this->domain[$value['name']] = $value['value'];
        }
        $activity_number = $order['activity_number'];

        unset($order['activity_number']);
        $activity = $this->getActivity($activity_number);
        if (!$activity) {
            $this->getController()->error(300, '订单信息错误');
        }

        // 获取门店信息
        $store = $this->getStore($activity['store_number']);
        if (!$store) {
            $this->getController()->error(300, '订单信息错误');
        }
        unset($activity['store_number']);
        $result = [
            'order'    => aesEncrypt(json_encode($order), $this->getController()->key, $this->getController()->iv),
            'activity' => $activity,
            'store'    => $store,
        ];
        $this->getController()->success(200, $result);
    }

    // 获取订单信息
    protected function getOrderInfo($param)
    {
        $data = $this->db->get('activity_order', ['contact_name', 'contact_tel', 'activity_number'], $param);
        if (is_null($data)) {
            return false;
        }
        return $data;
    }
    // 获取活动
    protected function getActivity($activity_number)
    {
        $data = $this->db->get('activity', [
            'store_number',
            'activity_number',
            'activity_thumb_src',
            'activity_title',
            'activity_price',
        ], [
            'deletetime'      => null,
            'activity_number' => $activity_number,
        ]);
        if (is_null($data)) {
            return [];
        }
        $data['activity_thumb_src'] = $this->domain['admin_domain'] . $data['activity_thumb_src'];

        return $data;
    }

    // 获取门店列表
    protected function getStore($store_number)
    {
        $data = $this->db->get('store', [
            'store_number',
            'store_thumb_src',
            'store_name',
            'business_hours_start',
            'business_hours_end',
            'address',
            'contact_number',
        ], [
            'deletetime'   => null,
            'store_number' => $store_number,
        ]);
        if (is_null($data)) {
            return [];
        }
        $data['business_hours'] = $data['business_hours_start'] . '~' . $data['business_hours_end'];
        unset($data['business_hours_start'], $data['business_hours_end']);
        $data['store_thumb_src'] = $this->domain['admin_domain'] . $data['store_thumb_src'];

        return $data;
    }
}
