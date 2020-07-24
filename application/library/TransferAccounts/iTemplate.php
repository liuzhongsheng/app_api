<?php

// 声明一个提交转账接口
interface commit
{
    public function queryPay($param);
}

// 声明一个查询转账接口
interface find
{
    public function queryFind($param);
}
