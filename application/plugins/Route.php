<?php
class RoutePlugin extends Yaf_Plugin_Abstract {

    /**
     * 在路由之前改变url
     * 格式:url/版本号/模块/控制器/方法/参数(可选)
     **/
    public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {
        # 获取当前请求的类型, 可能的返回值为GET,POST,HEAD,PUT,CLI等
        $method = $request->getMethod();
        
        # 获取当前请求的request_uri 
        $url = $request->getRequestUri();
        
        # 首先验证method是否合法
        $check_method = strtolower($method);
        $allow_method = ['get', 'post', 'put', 'delete'];
        
        # 如果请求类型错误返回405
        if(!in_array($check_method, $allow_method)){
            header('HTTP/1.1 405 Method not allowed');
        }
        
        $url    = ltrim($url, '/');
        $url_cut= explode('/', $url);
        # 如果url切割后的数组少于3个返回400错误
        $urlCutCount = count($url_cut);
        if($urlCutCount < 4){
            header('HTTP/1.1 400 Bad Request');
        }
        if($urlCutCount == 4){
            list($version, $module, $control,$action) = $url_cut;
        }else{
            list($version, $module, $control, $action,$param) = $url_cut;
            # 如果存在参数则赋值给id
            $request->setParam('id', htmlspecialchars($param, ENT_QUOTES));
        }
        # 重置request_url:/模块/控制器/方法
        
        $request->setRequestUri('/'.$module.'/'.$control.'/'.$control.ucfirst($action));
        // $control = $version.$module.$control;
        $controller = ucfirst(strtolower($control)) . '_' . ucfirst(strtolower($method));
        # 重新指定模块
        $request->setModuleName(strtoupper($module));
        
        # 重新指定控制器
        $request->setControllerName($controller);
        # 重新指定操作
        # 如果不存在则使用默认值"index"
        
        // var_dump($version.$module.$control.ucfirst($action));

        $request->setActionName($action?'v1'.$control.ucfirst($action):'index');
        
        # 设定model常量
        $module = ucfirst($module);
        define('MODULE',$module);
        
        # 设定actions路径
        define('ACTIONS_MODULE_PATH','modules/'.$module);
        
        # 设定版本号
        define('API_VERSION', strtolower($version));
        define('API_CONTROL', strtolower($control));
    }
    
    public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        #print('插件关闭');
    }

}
 