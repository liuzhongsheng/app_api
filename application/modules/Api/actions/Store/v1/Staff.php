<?php
class storestaffAction extends Yaf_Action_Abstract
{
    protected $baseData = [];
    protected $db;
    public $check = [
        'store_number' => ['required', '001', '请传入门店编号'],
        'pagesize'     => ['', 1, ''],
        'pageNum'      => ['', 10, ''],
    ];
    /**
     * @method 门店员工列表
     * @url    /v1/api/store/staff
     * @http   GET
     * @desc
     * @param  store_number    string [必填] 门店编号
     * @param  pagesize        string [必填] 下一页页码
     * @param  pageNum         string [必填] 每页显示数量，默认6
     * @author 李黑帅
     * @copyright 2019/07/04
     * @returnValue pagesize            string 下一页页码
     * returnValue data                []  员工列表
     * returnValue staff_number         string  员工编号
     * returnValue staff_name           string  员工姓名
     * returnValue staff_job            string  员工职务
     * returnValue staff_thumb_src      string  员工封面
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
        $this->db = $this->getController()->connectMysql();
        // 获取域名配置信息
        $domainConfig = $this->db->select('config', ['name', 'value'], ['group' => 'domain']);
        $this->domain = [];
        foreach ($domainConfig as $key => $value) {
            $this->domain[$value['name']] = $value['value'];
        }
        // 获取员工
        $where = [

            'ORDER' => ['staff.createtime' => 'DESC'],
            'LIMIT' => [($param['pagesize'] - 1) * $param['pageNum'], $param['pageNum']],
        ];
        if (!empty($para['store_number'])) {
            $where['staff.store_number'] = $param['store_number'];
        }
        // 获取员工
        $data = $this->db->select('staff', [
            '[>]store' => ['store_number' => 'store_number'],
        ], [
            'staff.staff_number',
            'staff.staff_name',
            'staff.staff_thumb_src',
            'staff.staff_job',
            'store.store_name',
            'store.store_number',
        ], $where);
        if (!empty($data)) {
            foreach ($data as $key => &$value) {
                $value['staff_thumb_src'] = $this->domain['admin_domain'] . $value['staff_thumb_src'];
            }
            unset($value);
        }

        $this->getController()->success(200, [
            'pagesize' => $param['pagesize'] + 1,
            'data'     => $data,
        ]);

    }

}
