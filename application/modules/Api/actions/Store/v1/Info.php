<?php
class storeinfoAction extends Yaf_Action_Abstract
{
    protected $baseData = [];
    protected $db;
    public $check = [
        'store_number' => ['required', '001', '请传入门店编号'],
    ];
    /**
     * @method 门店详情
     * @url    /v1/api/store/info
     * @http   GET
     * @desc
     * @param  store_number    string [必填] 门店编号
     * @author 李黑帅
     * @copyright 2019/07/04
     * returnValue store                    []  门店信息
     * returnValue store_number             string  门店编号
     * returnValue store_name               string  门店姓名
     * returnValue store_thumb_src          string  门店封面
     * returnValue address                  string  门店地址
     * returnValue contact_number           string  联系电话
     * returnValue activity                 []  活动信息
     * returnValue activity_number          string  活动编号
     * returnValue activity_thumb_src       string  活动图片
     * returnValue activity_title           string  活动标题
     * returnValue activity_price           string  活动活动价格

     * returnValue image                    []      门店展示
     * returnValue longitude                string  经度
     * returnValue latitude                 string  纬度
     * returnValue business_hours           string  营业时间
     * returnValue staff                    []  员工列表
     * returnValue staff_number             string  员工编号
     * returnValue staff_name               string  员工姓名
     * returnValue staff_job                string  员工职务
     * returnValue staff_thumb_src          string  员工封面
     * returnValue case                     []  案例
     * returnValue case_number              string  案例编号
     * returnValue case_title               string  案例标题
     * returnValue case_thumb_src           string  案例图片
     * returnValue case_like_num            string  点赞数量
     * returnValue store_name               string  门店名称
     * returnValue product                  []  产品列表
     * returnValue product_number           string  产品编号
     * returnValue product_thumb_src        string  产品封面
     * returnValue product_name             string  产品名称
     * returnValue product_name_subtitle    string  产品副标题
     * returnValue cate_name                string  产品分类名称
     * @return
     */
    public function execute()
    {
                if (!$this->getRequest()->isGet()) {
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            exit;
        }
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
        $store = $this->getStore();
        // 获取推荐活动
        $activity = $this->getActivity();
        // 获取员工
        $staff = $this->getStaff();
        // 获取成功案例
        $case = $this->getCase();
        // 获取产品列表
        $product = $this->getProduct();
        $data    = [
            'store'    => $store,
            'activity' => $activity,
            'staff'    => $staff,
            'case'     => $case,
            'product'  => $product,
        ];

        $this->getController()->success(200, $data);

    }
    // 获取当前门店信息
    protected function getStore()
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
            'longitude',
            'latitude'
        ], [
            'deletetime'   => null,
            'store_number' => $this->param['store_number'],
        ]);
        if (is_null($data)) {
            return [];
        }
        $data['business_hours'] = $data['business_hours_start'] . '~' . $data['business_hours_end'];
        unset($data['business_hours_start'], $data['business_hours_end']);
        $data['store_thumb_src'] = $this->domain['admin_domain'] . $data['store_thumb_src'];
        if(!empty($data['image'])){
            $data['image'] = explode(',', $data['image']);
            foreach ($data['image'] as $key => &$value) {
                $value = $this->domain['admin_domain'] . $value;
            }
            unset($value);
        }
        return $data;
    }
    // 获取推荐活动
    protected function getActivity()
    {
        $data = $this->db->get('activity', [
            '[>]activity_store'=>['activity_number'=>'activity_number']
        ], [
            'activity.activity_number',
            'activity.activity_thumb_src',
            'activity.activity_title',
            'activity.activity_price',
        ], [
            'activity_store.store_number' => $this->param['store_number'],
            // 'activity.end_time[>]'           => time(),
            'ORDER'                 => ['activity.start_time' => 'ASC'],
        ]);
        if (!is_null($data)) {
            $data['activity_thumb_src'] = $this->domain['admin_domain'] . $data['activity_thumb_src'];
            return $data;
        }
        return [];
    }
    // 获取员工列表
    protected function getStaff()
    {
        $data = $this->db->select('staff', [
            'staff_number',
            'staff_thumb_src',
            'staff_name',
            'staff_job',
        ], [
            'store_number' => $this->param['store_number'],
            'ORDER'        => ['createtime' => 'DESC'],
            'LIMIT'        => 5,
        ]);
        if (!empty($data)) {
            foreach ($data as $key => &$value) {
                $value['staff_thumb_src'] = $this->domain['admin_domain'] . $value['staff_thumb_src'];
            }
            unset($value);
        }
        return $data;
    }
    // 获取成功案例
    protected function getCase()
    {
        $data = $this->db->select('case', [
            '[>]store' => ['store_number' => 'store_number'],
        ], [
            'case.case_number',
            'case.case_title',
            'case.case_thumb_src',
            'case.case_like_num',
            'store.store_name',
        ], [
            'case.store_number' => $this->param['store_number'],
            'ORDER'             => ['case.createtime' => 'DESC'],
        ]);
        if (!empty($data)) {
            foreach ($data as $key => &$value) {
                $value['case_thumb_src'] = $this->domain['admin_domain'] . $value['case_thumb_src'];
            }
            unset($value);
        }
        return $data;
    }

    // 获取产品列表
    protected function getProduct($product_number = '002')
    {
        $data = $this->db->select('product', [
            '[>]category' => ['category_id' => 'id'],
        ], [
            'product.product_number',
            'product.product_thumb_src',
            'product.product_name',
            'product.product_name_subtitle',
            'category.name(cate_name)',
        ], [
            'product.product_number' => explode(',', $product_number),
            'ORDER'                  => ['product.createtime' => 'DESC'],
        ]);
        if (!empty($data)) {
            foreach ($data as $key => &$value) {
                $value['product_thumb_src'] = $this->domain['admin_domain'] . $value['product_thumb_src'];
            }
            unset($value);
        }
        return $data;
    }
}
