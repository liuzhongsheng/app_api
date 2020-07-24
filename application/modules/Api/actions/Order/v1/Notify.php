<?php
class ordernotifyAction extends Yaf_Action_Abstract
{
    protected $return = [];
    /**
     * @method 订单回调-无需对接
     * @url    /v1/api/order/notify
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
        $obj  = new SuperPay\Init();
        $data = [
            'class_type_name' => 'Pay', // 操作类型：TransferAccounts 提现 Pay 支付
            'class_name'      => 'Wechat', // 要调用的类名支持：Wechat
        ];
        $this->return = $obj->query($data, 'notify');
        $this->return = [
            'out_trade_no' => 'O159505224523',
        ];
        if (is_array($this->return)) {
            $db     = $this->getController()->connectMysql();
            $result = $db->action(function ($database) {
                $return = $this->return;
                // 获取订单信息
                $order = $database->get('activity_order', '*', [
                    'activity_order_number' => $return['out_trade_no'],
                ]);
                echo '<pre>';
                print_r($order);
                if (is_null($order)) {
                    $str = "Error: order:{$return['out_trade_no']} status:{$return['result_code']}  message:{$return['err_code_des']}";
                    dump_log($str, 'pay_query');
                    return false;
                }
                // 写入支付表
                $res = [
                    'appid'          => $return['appid'],
                    'bank_type'      => $return['bank_type'],
                    'cash_fee'       => $return['cash_fee'],
                    'fee_type'       => $return['fee_type'],
                    'is_subscribe'   => $return['is_subscribe'],
                    'mch_id'         => $return['mch_id'],
                    'nonce_str'      => $return['nonce_str'],
                    'openid'         => $return['openid'],
                    'out_trade_no'   => $return['out_trade_no'],
                    'result_code'    => $return['result_code'],
                    'return_code'    => $return['return_code'],
                    'sign'           => $return['sign'],
                    'time_end'       => $return['time_end'],
                    'total_fee'      => floatval($return['total_fee'] / 100),
                    'trade_type'     => $return['trade_type'],
                    'transaction_id' => $return['transaction_id'],
                ];
                $database->insert('activity_order_pay', $res);
                if (!$database->id()) {
                    $str = "Error: order:{$return['out_trade_no']} status:{$return['result_code']} message:写入order_pay失败";
                    dump_log($str, 'pay_query');
                    return false;
                }

                // 更新订单表
                $res = $database->update('activity_order', [
                    'status'  => '已支付',
                    'paytime' => time(),
                ], [
                    'activity_order_number' => $order['activity_order_number'],
                ]);

                if (!$res->rowCount()) {
                    print_r($database->error());
                    echo '第一次更新失败';
                    $str = "Error: order:{$return['out_trade_no']} status:{$return['result_code']} message:更新order_失败";
                    dump_log($str, 'pay_query');
                    return false;
                }

                // 返现
                $push_number = $database->get('wechat_user', 'push_number', [
                    'user_number' => $order['user_number'],
                ]);
                var_dump($push_number);
                if ($push_number) {
                    $cashback_amount = $database->get('activity', 'cashback_amount', [
                        'activity_number' => $order['activity_number'],
                    ]);
                    var_dump($cashback_amount);
                    if ($cashback_amount > 0) {
                        $userInfo = $database->get('wechat_user', '*', [
                            'user_number' => $push_number,
                        ]);
                        print_r($userInfo);
                        if (!is_null($userInfo)) {
                            $temp = [
                                'user_number' => $push_number,
                                'money'       => $cashback_amount,
                                'event'       => '邀请下单返现',
                                'before'      => $userInfo['current_income_amount'],
                                'after'       => $userInfo['current_income_amount'] + $cashback_amount,
                                'memo'        => '活动订单-' . $order['activity_order_number'] . '返现',
                                'createtime'  => time(),
                            ];
                            print_r($temp);
                            $database->insert('user_money_log', $temp);
                            if ($database->id() > 0) {
                                $UpdateUserData = [
                                    'current_income_amount[+]' => $cashback_amount,
                                    'total_income_amount[+]'   => $cashback_amount,
                                    'invite_to_buy_amount[+]'  => $cashback_amount,
                                    'invite_to_buy[+]'         => 1,
                                ];
                                $database->update('wechat_user', $UpdateUserData, [
                                    'user_number' => $push_number,
                                ]);
                                // 更新订单表
                                $res = $database->update('activity_order', [
                                    'cashback' => '已返现',
                                ], [
                                    'activity_order_number' => $order['activity_order_number'],
                                ]);
                                echo '已返现';
                            }
                            return true;
                        }
                    } else {
                        $UpdateUserData = [
                            'invite_to_buy[+]' => 1,
                        ];
                        $database->update('wechat_user', $UpdateUserData, [
                            'user_number' => $push_number,
                        ]);
                    }
                }
                echo '无需返现';
                // 更新订单表
                $res = $database->update('activity_order', [
                    'cashback' => '无需返现',
                ], [
                    'activity_order_number' => $order['activity_order_number'],
                ]);

                return true;
            });
            if ($result == true) {
                $str = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                echo $str;
            }
        }
        return false;
    }

}
