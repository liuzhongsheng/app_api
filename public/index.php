<?php
#ini_set('error_reporting', E_ALL); 
#ini_set('display_errors', 'on');

if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
	return 200;
}
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials:true');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers:Origin,DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding,content-type,access_token,refresh_token,X-CSRF-Token,access-token,refresh-token,HTTP_ACCESS_TOKEN');

require '../vendor/autoload.php';

define("APP_PATH",  realpath(dirname(__FILE__) . '/../'));  //指向public的上一级 
$app  = new Yaf_Application(APP_PATH . '/config/application.ini');


$app->bootstrap()->run();