<?php
class storecaseAction extends Yaf_Action_Abstract
{
    protected $baseData = [];
    protected $db;
    public $check = [
        'store_number' => ['', '', ''],
        'pagesize'     => ['', 1, ''],
        'pageNum'      => ['', 10, ''],
    ];
    /**
     * @method 门店案例
     * @url    /v1/api/store/case
     * @http   GET
     * @desc
     * @param  store_number    string [选填] 门店编号
     * @param  pagesize        string [必填] 下一页页码
     * @param  pageNum         string [必填] 每页显示数量，默认6
     * @author 李黑帅
     * @copyright 2019/07/04
     * @returnValue pagesize            string 下一页页码
     * returnValue data                     []  案例
     * returnValue case_number              string  案例编号
     * returnValue case_title               string  案例标题
     * returnValue case_thumb_src           string  案例图片
     * returnValue case_like_num            string  点赞数量
     * returnValue store_name               string  门店名称
     * returnValue store_number             string  门店编号
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
        $where = [

            'ORDER' => ['case.createtime' => 'DESC'],
            'LIMIT' => [($param['pagesize'] - 1) * $param['pageNum'], $param['pageNum']],
        ];
        if (!empty($para['store_number'])) {
            $where['case.store_number'] = $param['store_number'];
        }
        // 获取员工
        $data = $this->db->select('case', [
            '[>]store' => ['store_number' => 'store_number'],
        ], [
            'case.case_number',
            'case.case_title',
            'case.case_thumb_src',
            'case.case_like_num',
            'store.store_name',
            'store.store_number',
        ], $where);
        if (!empty($data)) {
            foreach ($data as $key => &$value) {
                $value['case_thumb_src'] = $this->domain['admin_domain'] . $value['case_thumb_src'];
            }
            unset($value);
        }

        $this->getController()->success(200, [
            'pagesize' => $param['pagesize'] + 1,
            'data'     => $data,
        ]);

    }

}
