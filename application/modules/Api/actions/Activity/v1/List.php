<?php
class activitylistAction extends Yaf_Action_Abstract
{
    protected $baseData = [];
    public $check       = [
        'longitude'    => ['', '0', ''],
        'latitude'     => ['', '0', ''],
        'search_key'   => ['', '', ''],
        'pagesize'     => ['', 1, ''],
        'pageNum'      => ['', 10, ''],
        'type'         => ['', 0, ''],
        'store_number' => ['required', '', '请传入门店编号'],
    ];
    /**
     * @method 活动列表
     * @url    /v1/api/activity/list
     * @http   GET
     * @desc
     * @param  store_number    string [选填] 门店编号，从门店列表进来时需传递
     * @param  longitude       string [选填] 经度
     * @param  latitude        string [选填] 纬度
     * @param  search_key      string [选填] 搜索关键词
     * @param  pagesize        string [选填] 下一页页码默认1
     * @param  pageNum         string [选填] 每页显示数量，默认10
     * @param  type            strubg [选填] 0全部 1进行中 2预备中 3已结束
     * @author 李黑帅
     * @copyright 2019/07/04
     * @returnValue pagesize                string  下一页页码
     * @returnValue data                    []      数据
     * @returnValue activity_number         string 活动编号
     * @returnValue activity_thumb_src    string 活动封面图
     * @returnValue activity_title          string 活动标题
     * @returnValue activity_price          string 活动价格
     * @returnValue store_name              string 分店名称
     * @returnValue status                  string 活动状态：1进行中 2预备中 3已结束
     * @return
     */
    public function execute()
    {
        if (!$this->getRequest()->isGet()) {
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            exit;
        }
        $param = $this->getController()->verificationParam($this->check, 'query');
        if (!is_array($param)) {
            $this->getController()->error(412, $param);
        }
        $where = '';
        $db    = $this->getController()->connectMysql();
        // 如果有搜索条件，进行搜索
        if ($param['search_key'] != '') {
            $search_key = $param['search_key'];
            $where .= " and  a.activity_title like '%{$search_key}%'";
        }
        $time = time();
        // 筛选条件
        switch ($param['type']) {
            case 1:
                // 按进行中筛选
                $where .= " and a.start_time<{$time} and a.end_time > {$time}";
                break;
            case 2:
                // 按未开始进行筛选
                $where .= " and  a.start_time>{$time}";
                break;
            case 3:
                //按已结束进行筛选
                $where .= " and  a.end_time<{$time}";
                break;
            default:
                # code...
                break;
        }
        // if(!empty($param['store_number'])){
        $where .= sprintf(" and b.store_number='%s'", $param['store_number']);
        // }
        //  $sql  = sprintf("SELECT a.activity_number, a.activity_thumb_src, a.activity_title, a.activity_title,a.activity_price,b.store_name,a.start_time,a.end_time FROM `q_activity` a LEFT JOIN `q_store` b ON a.`store_number`=b.`store_number` WHERE a.`deletetime` IS NULL and b.`deletetime` IS NULL %s ORDER BY a.`createtime` DESC limit %d,%d", $where, ($param['pagesize'] - 1) * $param['pageNum'], $param['pageNum']);
        // $data = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        $sql  = sprintf("SELECT a.activity_number, a.activity_thumb_src, a.activity_title, a.activity_title,a.activity_price,a.start_time,a.end_time FROM `q_activity` a left JOIN q_activity_store b ON a.activity_number=b.activity_number WHERE a.`deletetime` IS NULL %s ORDER BY a.`createtime` DESC limit %d,%d", $where, ($param['pagesize'] - 1) * $param['pageNum'], $param['pageNum']);
        $data = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        if (!empty($data)) {
            $domain       = [];
            $domainConfig = $db->select('config', ['name', 'value'], ['group' => 'domain']);
            foreach ($domainConfig as $key => $value) {
                $domain[$value['name']] = $value['value'];
            }
            foreach ($data as $key => &$value) {
                if ($value['start_time'] > $time) {
                    $value['status'] = 2;
                }
                if ($value['end_time'] < $time) {
                    $value['status'] = 3;
                }
                if ($value['end_time'] > $time && $value['start_time'] < $time) {
                    $value['status'] = 1;
                }
                unset($data[$key]['start_time']);
                unset($data[$key]['end_time']);
                $value['activity_thumb_src'] = $domain['admin_domain'] . $value['activity_thumb_src'];
            }
            unset($value);
        }
        array_values($data);

        $this->getController()->success(200, [
            'pagesize' => $param['pagesize'] + 1,
            'data'     => $data,
        ]);
    }
}
