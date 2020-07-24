<?php
class userwithdrawallogAction extends Yaf_Action_Abstract
{
    protected $baseData = [];
    /**
     * @method 收益提现
     * @url    /v1/api/user/withdrawallog
     * @http   GET
     * @desc
     * @author 李黑帅
     * @copyright 2019/07/04
     * returnValue current_income_amount string 账户可用余额
     * returnValue min string 最小提现金额
     * returnValue max string 最大提现金额
     * returnValue data [] 列表
     * returnValue amount string 金额
     * returnValue createtime string 创建时间
     * @return
     */
    public function execute()
    {
        $this->loginInfo = $this->getController()->checkLoginStatus();
        $db        = $this->getController()->connectMysql();
        $temp    = $db->select('config', ['name', 'value'], ['group' => 'withdrawal']);
        $config          = [];
        foreach ($temp as $key => $value) {
            $config[$value['name']] = $value['value'];
        }
        $data = $db->select('withdrawal',['amount','createtime'],[
            'user_number' => $this->loginInfo['user_number'],
        ]);
        if(!empty($data)){
            foreach ($data as $key => &$value) {
                $value['createtime'] = date('Y-m-d H:i:s',$value['createtime']);
            }
            unset($value);
        }else{
            $data = [];
        }
        $data = [
            'current_income_amount' => $this->loginInfo['current_income_amount'],
            'min'                   => $config['min_withdrawal'],
            'max'                   => $config['max_withdrawal'],
            'data'                  => $data,
        ];
        $this->getController()->success(200, $data);
    }

}
