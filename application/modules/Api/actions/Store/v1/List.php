<?php
class storelistAction extends Yaf_Action_Abstract
{
    protected $baseData = [];
    public $check       = [
        'pagesize'      => ['', 1, ''],
        'pageNum'       => ['', 10, ''],
        'search_key'    => ['', '', ''],
        'longitude'     => ['', 0, ''],
        'latitude'      => ['', 0, ''],
        'province_code' => ['', '', ''],
    ];
    /**
     * @method 门店列表
     * @url    /v1/api/store/list
     * @http   GET
     * @desc
     * @param  longitude       string [选填] 经度
     * @param  latitude        string [选填] 纬度
     * @param  search_key      string [选填] 搜索关键词
     * @param  pagesize        string [必填] 下一页页码
     * @param  pageNum         string [必填] 每页显示数量，默认6
     * @param  province_code   string [选填] 省份编号,为空代表全国
     * @author 李黑帅
     * @copyright 2019/07/04
     * @returnValue pagesize        string 下一页页码
     * @returnValue data            [] 门店数据
     * @returnValue store_number    string 门店编号
     * @returnValue store_thumb_src string 门店封面图
     * @returnValue store_name      string 门店名称
     * @returnValue business_hours  string 营业时间
     * @returnValue address         string 门店地址
     * @return
     */
    public function execute()
    {
        //程序运行开始时间
        $startTime = explode(' ', microtime());
                if (!$this->getRequest()->isGet()) {
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            exit;
        }
        $param = $this->getController()->verificationParam($this->check, 'query');
        if (!is_array($param)) {
            $this->getController()->error(412, $param);
        }

        $db    = $this->getController()->connectMysql();
        $where = '';
        if ($param['province_code'] != '') {
            $where .= 'and province_code=' . $param['province_code'];
        }
        if ($param['search_key'] != '') {
            $search_key = $param['search_key'];
            $where .= " and  store_name like '%{$search_key}%'";
        }
        $sql = sprintf("SELECT
                        store_number,
                        store_thumb_src,
                        store_name,
                        business_hours_start,
                        business_hours_end,
                        address,
                          getDistance(%s,%s,longitude,latitude)  AS distance
                        FROM
                          `q_store`
                        where deletetime is null %s
                        order by distance ASC
                        limit %d,%d ", $param['longitude'], $param['latitude'], $where, ($param['pagesize'] - 1) * $param['pageNum'], $param['pageNum']);

        $data = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        if (!empty($data)) {
            $config       = Yaf_Registry::get('config');
            $domainConfig = $db->select('config', ['name', 'value'], ['group' => 'domain']);
            $domain       = [];
            foreach ($domainConfig as $key => $value) {
                $domain[$value['name']] = $value['value'];
            }

            foreach ($data as $key => &$value) {
                if ($value['distance'] < 1) {
                    $value['distance'] = 1;
                }

                $value['store_thumb_src'] = $domain['admin_domain'] . $value['store_thumb_src'];
                $value['business_hours']  = $value['business_hours_start'] . '~' . $value['business_hours_end'];
                unset($data[$key]['business_hours_start'], $data[$key]['business_hours_end']);
            }
            unset($value);
        }
        $endTime = explode(' ', microtime());
        $this->getController()->success(200, [
            'pagesize' => $param['pagesize'] + 1,
            'data'     => $data,
        ]);
    }
}
