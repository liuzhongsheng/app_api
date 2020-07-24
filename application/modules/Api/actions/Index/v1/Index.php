<?php
class indexindexAction extends Yaf_Action_Abstract
{
    protected $baseData = [];
    protected $db       = null;
    protected $param    = [];
    protected $domain;
    public $check = [
        'longitude' => ['', 0],
        'latitude'  => ['', 0],
    ];
    /**
     * @method 首页
     * @url    /v1/api/index/index
     * @http   GET
     * @desc
     * @param  longitude       string [选填] 经度
     * @param  latitude        string [选填] 纬度
     * @author 李黑帅
     * @copyright 2019/07/04
     * @returnValue banner  [] 轮播图
     * @returnValue activity                [] 最新活动
     * @returnValue activity_number         string 最新活动-活动编号
     * @returnValue activity_thumb_src    string 最新活动-活动封面图
     * @returnValue activity_title          string 最新活动-活动标题
     * @returnValue activity_price          string 最新活动-活动价格
     * @returnValue store_name              string 最新活动-分店名称
     * @returnValue store   [] 最新加盟
     * @returnValue store_number        string 最新加盟-门店编号
     * @returnValue store_thumb_src     string 最新加盟-门店封面图
     * @returnValue store_name          string 最新加盟-门店名称
     * @returnValue product             [] 推荐产品
     * @returnValue product_number      string 推荐产品-产品编号
     * @returnValue category            string 推荐产品-所属类别
     * @returnValue product_thumb_src   string 推荐产品-产品封面图
     * @returnValue product_name        string 推荐产品-产品名称
     * @returnValue product_name_subtitle    string 推荐产品-产品副标题
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
            //$this->baseData = $this->getController()->checkToken();
            $this->db    = $this->getController()->connectMysql();
            $this->param = $this->getController()->verificationParam($this->check, 'query');
            if (!is_array($this->param)) {
                $this->getController()->error(412, $this->param);
            }
            // 获取域名配置信息
            $domainConfig = $this->db->select('config', ['name', 'value'], ['group' => 'domain']);
            $this->domain = [];
            foreach ($domainConfig as $key => $value) {
                $this->domain[$value['name']] = $value['value'];
            }

            // 获取banner
            $config = $this->db->get('config', 'value', ['name' => 'home_banner', 'group' => 'carousel']);

            if (!is_null($config)) {
                $banner = explode(',', $config);
                foreach ($banner as $key => &$value) {
                    $value = $this->domain['admin_domain'] . $value;
                }
                unset($value);
            } else {
                $banner = [];
            }
            // 获取活动
            $activity = $this->getActivity();
            // 获取最新加盟门店
            $store = $this->getStore();

            // 获取产品列表
            $product = $this->getProduct();
            $data    = [
                'banner'   => $banner,
                'activity' => $activity,
                'store'    => $store,
                'product'  => $product,
            ];
            $this->getController()->success(200, $data);
        } catch (Exception $e) {
            debugLog($e->getMessage());
            $this->getController()->error(9003);
        }
    }
    // 获取活动
    protected function getActivity()
    {
        return [];
        $time = time();
        $sql  = "SELECT a.activity_number, a.activity_thumb_src, a.activity_title, a.activity_title,a.activity_price,b.store_name FROM `q_activity` a LEFT JOIN `q_store` b ON a.`store_number`=b.`store_number` WHERE a.`deletetime` IS NULL and b.`deletetime` IS NULL and a.start_time<{$time} and a.end_time > {$time} ORDER BY a.`createtime` DESC";
        $data = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        if (empty($data)) {
            return [];
        }
        foreach ($data as $key => &$value) {
            $value['activity_thumber_src'] = $this->domain['admin_domain'] . $value['activity_thumber_src'];
        }
        unset($value);
        return $data;
    }

    // 获取门店列表
    protected function getStore()
    {
        $data = $this->db->select('store', ['store_number', 'store_thumb_src', 'store_name'], ['ORDER' => ['createtime' => 'DESC'], 'deletetime' => null, 'LIMIT' => 5]);
        if (empty($data)) {
            return [];
        }
        foreach ($data as $key => &$value) {
            $value['store_thumb_src'] = $this->domain['admin_domain'] . $value['store_thumb_src'];
        }
        unset($value);
        return $data;
    }

    // 产品列表
    protected function getProduct()
    {
        $sql  = 'SELECT a.`product_number`,a.`product_thumb_src`,a.`product_name`,a.`product_name_subtitle`,a.`product_content`,b.name category FROM `q_product` a LEFT JOIN `q_category` b ON a.`category_id`=b.`id` WHERE a.`deletetime` IS NULL ORDER BY a.`createtime` DESC';
        $data = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        if (empty($data)) {
            return [];
        }
        foreach ($data as $key => &$value) {
            $value['product_thumb_src'] = $this->domain['admin_domain'] . $value['product_thumb_src'];
        }
        unset($value);
        return $data;
    }
}
