<?php
/**
 * 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf_Bootstrap_Abstract{

    public function _initConfig(Yaf_Dispatcher $dispatcher) {
        $config = Yaf_Application::app()->getConfig();
        Yaf_Registry::set("config", $config);
        define('OSS_IMAGE_DOMAIN', $config->oss->ali_oss_image_domain);
        Yaf_Registry::set('redis', $config->redis);
        Yaf_Session::getInstance();
    }
    public function _initCommonFunctions(){  
        Yaf_Loader::import(Yaf_Application::app()->getConfig()->application->directory . '/common/functions.php'); 
        checkCC(); 
    }
    public function _initDefaultName(Yaf_Dispatcher $dispatcher) {
        $dispatcher->setDefaultModule("Api")->setDefaultController("Index")->setDefaultAction("index");
    }
    
    /**
     * 调用时载入数据库orm
     **/
    public function _initDatabase() {
        $arrConfig = Yaf_Registry::get('config');
		/**
        $database = new \Medoo\Medoo([
            'dsn' => [
                'driver'    => 'sqlsrv',
                'server'    => $arrConfig->mssql->db->hostname,
                'Database'  => $arrConfig->mssql->db->database
            ],
            'database_type' => $arrConfig->mssql->db->type,
            'username' => $arrConfig->mssql->db->username,
            'password' => $arrConfig->mssql->db->password
        ]);
        Yaf_Registry::set('db', $database);
		
		$option = [
            'database_type' => $arrConfig->mssql->db->type,
            'database_name' => $arrConfig->mssql->db->database,
            'server'        => $arrConfig->mssql->db->hostname,
            'username'      => $arrConfig->mssql->db->username,
            'password'      => $arrConfig->mssql->db->password,
            'prefix'        => $arrConfig->mssql->db->prefix,
            'logging'       => $arrConfig->mssql->db->log,
            'charset'       => 'utf8'
        ];
		
		//require 'library/Medoo.php';
        Yaf_Registry::set('db', new \Medoo\Medoo($option));
**/
        /*$option = [
            'database_type' => $arrConfig->edu_mssql->db->type,
            'database_name' => $arrConfig->edu_mssql->db->database,
            'server'        => $arrConfig->edu_mssql->db->hostname,
            'username'      => $arrConfig->edu_mssql->db->username,
            'password'      => $arrConfig->edu_mssql->db->password,
            'prefix'        => $arrConfig->edu_mssql->db->prefix,
            'logging'       => $arrConfig->edu_mssql->db->log,
            'charset'       => 'utf8'
        ];
        Yaf_Registry::set('edu_db', new \Medoo\Medoo($option));
		
		$option = [
            'database_type' => $arrConfig->common_mssql->db->type,
            'database_name' => $arrConfig->common_mssql->db->database,
            'server'        => $arrConfig->common_mssql->db->hostname,
            'username'      => $arrConfig->common_mssql->db->username,
            'password'      => $arrConfig->common_mssql->db->password,
            'prefix'        => $arrConfig->common_mssql->db->prefix,
            'logging'       => $arrConfig->common_mssql->db->log,
            'charset'       => 'utf8'
        ];
        Yaf_Registry::set('common_db', new \Medoo\Medoo($option));
  
        $option = [
            'database_type' => $arrConfig->coupon_mysql->db->type,
            'database_name' => $arrConfig->coupon_mysql->db->database,
            'server'        => $arrConfig->coupon_mysql->db->hostname,
            'username'      => $arrConfig->coupon_mysql->db->username,
            'password'      => $arrConfig->coupon_mysql->db->password,
            'port'          => $arrConfig->coupon_mysql->db->port,
            'logging'       => $arrConfig->coupon_mysql->db->log,
            'charset'       => 'utf8'
        ];
        Yaf_Registry::set('coupon_db', new \Medoo\Medoo($option));*/
    }
    
    /**
     * 注册插件
     **/
    public function _initPlugin(Yaf_Dispatcher $dispatcher){
        // 注册路由插件
        $route = new RoutePlugin();
        $dispatcher->registerPlugin($route);
    }
}
  