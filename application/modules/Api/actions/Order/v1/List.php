<?php
class orderlistAction extends Yaf_Action_Abstract
{
    protected $baseData = [];
    public $check       = [
        'pagesize' => ['', 1, ''],
        'pageNum'  => ['', 10, ''],
        'type'     => ['', 0, ''],
    ];
    /**
     * @method 订单列表
     * @url    /v1/api/order/list
     * @http   get
     * @param  pagesize        string [选填] 下一页页码默认1
     * @param  pageNum         string [选填] 每页显示数量，默认10
     * @param  type            strubg [选填] 0全部 1待支付 2已支付 3已失效
     * @author 李黑帅
     * @copyright 2019/07/04
     * @returnValue pagesize                string  下一页页码
     * @returnValue data                    []      数据
     * @return
     */
    public function execute()
    {
        if (!$this->getRequest()->isGet()) {
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            exit;
        }
        $this->loginInfo = $this->getController()->checkLoginStatus();
        // $this->loginInfo['user_number'] = 'W20200616020203684';

        $data['user_number'] = $this->loginInfo['user_number'];
        $db                  = $this->getController()->connectMysql();
        $param               = $this->getController()->verificationParam($this->check, 'query');
        if (!is_array($param)) {
            $this->getController()->error(412, $param);
        }
        $this->db = $this->getController()->connectMysql();
        $where    = '';
        $time     = time();
        // 筛选条件
        switch ($param['type']) {
            case 1:
                // 按进行中筛选
                $where .= " and b.start_time>{$time} and a.status = '待支付'";
                break;
            case 2:
                // 按未开始进行筛选
                $where .= " and b.status = '已支付'";
                break;
            case 3:
                //按已结束进行筛选
                $where .= " and b.end_time>{$time} and a.status = '待支付'";
                break;
            default:
                # code...
                break;
        }

        $sql = sprintf("SELECT d.store_name,b.activity_thumb_src,b.activity_title,b.activity_price,a.contact_name,a.contact_tel,b.start_time,b.end_time,a.status FROM `q_activity_order` a  LEFT JOIN `q_activity` b ON a.activity_number=b.activity_number left join q_activity_store c  ON a.activity_number=c.activity_number LEFT JOIN `q_store` d ON c.`store_number`=d.`store_number`  where b.deletetime is null %s ORDER BY a.`createtime` DESC limit %d,%d", $where, ($param['pagesize'] - 1) * $param['pageNum'], $param['pageNum']);

        $data = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        if (!empty($data)) {

            // 获取域名配置信息
            $domainConfig = $db->select('config', ['name', 'value'], ['group' => 'domain']);
            $domain       = [];
            foreach ($domainConfig as $key => $value) {
                $domain[$value['name']] = $value['value'];
            }
            foreach ($data as $key => &$value) {
                $value['activity_thumb_src'] = $domain['admin_domain'] . $value['activity_thumb_src'];
                if ($value['start_time'] > time() && $value['status'] == '待支付') {
                    $value['status'] = 1;
                }
                if ($value['status'] == '已支付') {
                    $value['status'] = 2;
                }
                if ($value['end_time'] > time() && $value['status'] == '待支付') {
                    $value['status'] = 3;
                }
            }
            unset($value);
        }
        $this->getController()->success(200, [
            'pagesize' => $param['pagesize'] + 1,
            'data'     => $data,
        ]);
    }

}
