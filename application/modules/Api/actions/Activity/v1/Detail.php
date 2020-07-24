<?php
class activitydetailAction extends Yaf_Action_Abstract
{
    protected $baseData = [];
    protected $db       = null;
    protected $domain;
    protected $param;
    public $check = [
        'store_number'    => ['required', '', '请传入门店编号'],
        'activity_number' => ['required', '', '请传入活动编号'],
    ];
    /**
     * @method 活动详情
     * @url    /v1/api/activity/detail
     * @http   GET
     * @desc   该接口如果传递有authorization则获取自己当前排名
     * @param  activity_number    string [必填] 活动编号
     * @param  store_number    string [必填] 门店编号
     * @author 李黑帅
     * @copyright 2019/07/04
     * @returnValue activity                [] 活动
     * @returnValue activity_number         string 活动-活动编号
     * @returnValue activity_thumb_src    string 活动-活动封面图
     * @returnValue activity_title          string 活动-活动标题
     * @returnValue activity_price          string 活动-活动价格
     * @returnValue activity_title_subtitle string 活动-活动副标题
     * @returnValue set_meal                [] 活动-活动服务
     * @returnValue name                    string 活动-活动服务-名字
     * @returnValue value                   string 活动-活动服务-值
     * @returnValue prize                   string 活动-活动礼品
     * @returnValue start_time              string 活动-开始时间
     * @returnValue end_time                string 活动-结束时间
     * @returnValue attendance              string 活动-参加人数
     * @returnValue views                   string 活动-浏览量
     * @returnValue forwarding_volume       string 活动-转发量
     * @returnValue content                 string 活动-详细介绍

     * @returnValue store           [] 门店
     * @returnValue store_number    string 门店编号
     * @returnValue store_thumb_src string 门店封面图
     * @returnValue store_name      string 门店名称
     * @returnValue business_hours  string 营业时间
     * @returnValue address         string 门店地址
     * @returnValue contact_number  string 联系电话
     * @returnValue image           string 门店展示

     * @returnValue rush_purchase_log   [] 抢购记录
     * @returnValue contact_name        string 联系人
     * @returnValue contact_tel         string 联系电话
     * @returnValue createtime          string 下单时间
     * @returnValue profile_photo       string 头像
     * @returnValue nickname            string 昵称(参加列表使用该字段)
     * @returnValue ranking         [] 排行榜
     * @returnValue nickname        string 昵称
     * @returnValue profile_photo   string 头像
     * @returnValue amount          string 金额
     * @returnValue my_info         [] 我的信息
     * @returnValue amount          string 金额
     * @returnValue randking        string 排名
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
            $this->loginInfo = $this->getController()->checkLoginStatus(false);

            $this->param = $this->getController()->verificationParam($this->check, 'query');
            if (!is_array($this->param)) {
                $this->getController()->error(412, $this->param);
            }
            $this->db = $this->getController()->connectMysql();
            // 获取域名配置信息
            $domainConfig = $this->db->select('config', ['name', 'value'], ['group' => 'domain']);
            $this->domain = [];
            foreach ($domainConfig as $key => $value) {
                $this->domain[$value['name']] = $value['value'];
            }

            // 获取活动
            $activity = $this->getActivity();
            // 获取榜单信息
            $ranking = $this->getRandking($activity['activity_number']);
            // 获取门店信息
            $store = $this->getStore($activity['store_number']);
            unset($activity['store_number']);
            // 获取产品列表
            $rush_purchase_log = $this->getActivityOrder($activity['activity_number']);
            if ($this->loginInfo['user_number']) {
                $my_info = $this->getMyInfo($activity['activity_number']);
            } else {
                $my_info = [];
            }

            $data = [
                'activity'          => $activity,
                'rush_purchase_log' => $rush_purchase_log,
                'ranking'           => $ranking,
                'store'             => $store,
                'my_info'           => $my_info,
            ];
            // 更新浏览量
            $this->db->update('activity', [
                'views[+]' => 1,
            ], [
                'activity_number' => $activity['activity_number'],
            ]);
            $this->getController()->success(200, $data);
        } catch (Exception $e) {
            debugLog($e->getMessage());
            $this->getController()->error(9003);
        }
    }
    // 获取活动
    protected function getActivity()
    {
        $data = $this->db->get('activity', [
            '[>]activity_store' => ['activity_number' => 'activity_number'],
        ], [
            'activity_store.store_number',
            'activity.activity_number',
            'activity.activity_thumb_src',
            'activity.activity_title',
            'activity.activity_price',
            'activity.activity_title_subtitle',
            'activity.json',
            'activity.prize',
            'activity.start_time',
            'activity.end_time',
            'activity.attendance',
            'activity.views',
            'activity.forwarding_volume',
            'activity.content',
        ], [
            'activity.deletetime'         => null,
            'activity.activity_number'    => $this->param['activity_number'],
            'activity_store.store_number' => $this->param['store_number'],
        ]);
        if (is_null($data)) {
            return [];
        }
        if (is_null($data['content'])) {
            $data['content'] = '';
        }
        $data['content'] = html_entity_decode($data['content']);
        $data['activity_thumb_src'] = $this->domain['admin_domain'] . $data['activity_thumb_src'];
        if (!empty($data['prize'])) {
            $data['prize'] = $this->domain['admin_domain'] . $data['prize'];
        }
        $data['set_meal'] = json_decode($data['json']);
        if (is_null($data['set_meal'])) {
            $data['set_meal'] = [];
        } else {
            $temp = [];
            foreach ($data['set_meal'] as $key => $value) {
                $temp[] = [
                    'name'  => $key,
                    'value' => $value,
                ];
            }
            $data['set_meal'] = $temp;
        }

        unset($data['json']);
        return $data;
    }

    // 获取门店信息
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
            'image',
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
        if (!empty($data['image'])) {
            $data['image'] = explode(',', $data['image']);
            foreach ($data['image'] as $key => &$value) {
                $value = $this->domain['admin_domain'] . $value;
            }
            unset($value);
        }
        return $data;
    }

    // 活动订单
    protected function getActivityOrder($activity_number)
    {
        $data = $this->db->select('activity_order', ['[>]wechat_user' => 'user_number'], [
            'activity_order.user_number',
            'activity_order.contact_name',
            'activity_order.contact_tel',
            'activity_order.createtime',
            'wechat_user.profile_photo',
            'wechat_user.nickname',
        ], [
            'deletetime'                     => null,
            'activity_order.activity_number' => $activity_number,
            'activity_order.status'          => '已支付',
            'ORDER'                          => ['activity_order.createtime' => 'DESC'],
            'LIMIT'                          => 20,
        ]);
        if (is_null($data)) {
            return [];
        }
        foreach ($data as $key => &$value) {
            $value['contact_tel']  = hiddenPhone($value['contact_tel']);
            $value['contact_name'] = substr_cut($value['contact_name']);
            $value['createtime']   = date('m-d H:i:s', $value['createtime']);
            unset($data[$key]['user_number']);
        }
        unset($value);
        array_values($data);
        return $data;
    }

    // 榜单列表
    protected function getRandking($activity_number)
    {
        $data = $this->db->select('activity_randking', ['[>]wechat_user' => 'user_number'], [
            'activity_randking.amount',
            'wechat_user.profile_photo',
            'wechat_user.nickname',
        ], [
            'activity_randking.activity_number' => $activity_number,
            'ORDER'                             => ['activity_randking.amount' => 'DESC'],
            'LIMIT'                             => 30,
        ]);
        if (is_null($data)) {
            return [];
        }
        return $data;
    }

    // 获取我的排名信息
    protected function getMyInfo($activity_number)
    {
        $sql = sprintf("SELECT randking,amount FROM (SELECT id,user_number,amount,(@rownum:=@rownum+1) AS randking
                FROM q_activity_randking t,(SELECT @rownum:=0) r
                WHERE activity_number = '%s'
                ORDER BY amount DESC) a WHERE a.user_number='%s'", $activity_number, $this->loginInfo['user_number']);
        $data = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        if (!empty($data)) {
            return $data[0];
        }
        return [];
    }
}
