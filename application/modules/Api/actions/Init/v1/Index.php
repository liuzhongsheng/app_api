<?php
class initindexAction extends Yaf_Action_Abstract
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
     * @method 初始化
     * @url    /v1/api/init/index
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
            phpinfo();
        return false;
    }
   
}
