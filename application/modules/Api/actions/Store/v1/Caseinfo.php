
<?php
class storecaseinfoAction extends Yaf_Action_Abstract
{
    protected $baseData = [];
    public $check       = [
        'store_number' => ['required', '001', '请传入门店编号'],
        'case_number'  => ['required', 'C001', '请传入案例编号'],
    ];
    /**
     * @method 案例详情
     * @url    /v1/api/store/caseinfo
     * @http   GET
     * @desc
     * @param  store_number    string [必填] 门店编号
     * @param  case_number     string [选填] 案例编号
     * @author 李黑帅
     * @copyright 2019/07/04
     * returnValue case_number              string  案例编号
     * returnValue case_title               string  案例标题
     * returnValue case_thumb_src           string  案例图片
     * returnValue case_like_num            string  点赞数量
     * returnValue store_name               string  门店名称
     * returnValue store_thumb_src          string  门店图片
     * returnValue like                     string  点赞状态：true是 false 否
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
        $this->loginInfo = $this->getController()->checkLoginStatus(false);
        $this->db        = $this->getController()->connectMysql();
        // 获取域名配置信息
        $domainConfig = $this->db->select('config', ['name', 'value'], ['group' => 'domain']);
        $this->domain = [];
        foreach ($domainConfig as $key => $value) {
            $this->domain[$value['name']] = $value['value'];
        }

        // 获取成功案例
        $case = $this->getCase();
        // $this->loginInfo['user_number'] = 'W20200616020203684';
        $case['like'] = false;
        if ($this->loginInfo) {
            $case['like'] = $this->db->has('store_case_like', [
                'user_number' => $this->loginInfo['user_number'],
                'case_number' => $this->param['case_number'],
            ]);
        }
        $this->getController()->success(200, $case);

    }

    // 获取成功案例
    protected function getCase()
    {
        $data = $this->db->get('case', [
            '[>]store' => ['store_number' => 'store_number'],
        ], [
            'case.case_number',
            'case.case_title',
            'case.case_thumb_src',
            'case.case_like_num',
            'store.store_name',
            'store.store_thumb_src',
        ], [
            'case.store_number' => $this->param['store_number'],
            'case.case_number'  => $this->param['case_number'],
        ]);
        if (!empty($data)) {
            $data['case_thumb_src']  = $this->domain['admin_domain'] . $data['case_thumb_src'];
            $data['store_thumb_src'] = $this->domain['admin_domain'] . $data['store_thumb_src'];
        }
        return $data;
    }

}