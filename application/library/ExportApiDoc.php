<?php

/** 注释格式如下：*/
/**
 * @method 注册用户
 * @url    email/send?token=xxx
 * @http  POST
 * @param  token              string [必填] 调用接口凭证 (post|get)
 * @param  type               int    [必填] 用户类型：1普通 2代理
 * @param  phone              string [必填] 注册手机号
 * @author soul
 * @copyright 2017/4/13
 * @return {"status":false,"data":'失败原因',"code":0}
 */
/** 以上为注释格式 */
class ExportApiDoc
{
    protected $section;
    protected $PHPWord;
    protected $table;
    protected $number = 1;
    public $config;
    public $version;
    public $modules;
    public function __construct($config=[],$modulesName='',$modules='api',$version = 'v1.0')
    {
        $this->modules=$modules;
        //导入配置文件
        $this->config = $config;
        if($modulesName != ''){
            $this->config['word_name'] = $modulesName;
        }
        
        //设置版本号
        $this->version = $version;
        //导入类文件
        #require 'PHPWord/PHPWord.php';
        $this->PHPWord = $PHPWord = new \PhpOffice\PhpWord\PhpWord();
        //设置文档字体
        $PHPWord->setDefaultFontName('宋体');
        //创建页面
        $this->section = $PHPWord->createSection();
    }
    public function getDoc()
    {
        //设置页眉
        $this->setHeaders($this->config['word_name'], '作者：刘中胜');
        //设置文档标题
        $this->setWordTitle($this->config['word_name']);
        //在数据不为空的时候，设置顶级接口标题
        $this->setTopLevel(self::getDocNote(),[]);
        return $this->dow();
    }
    /**
     * @return array 获取注释
     */
    protected function getDocNote()
    {
        $files = $this->listDir();
        $docData = [];
        if (count($files) > 0) {
            ksort($files);
            foreach ($files as $key => $val) {

                if (strpos($val, '.php')) {
                    include_once $val;
                    
                }
                $pathData = explode('/', pathinfo($val)['dirname']);
                $className = ucfirst($pathData[count($pathData)-2].str_replace('.php', '', pathinfo($val)['basename']));

                $className .= 'Action';
                //需要严格判断是否父类存在 class_implements
                $parent_class = get_parent_class($className);
                //方法名
                $methodName = get_class_methods($className);
                $methodNameByParents = [];
                if (!empty($parent_class)) {
                    $methodNameByParents = get_class_methods($parent_class);
                }
      
                array_diff($methodName,$methodNameByParents);
           
          
                foreach ($methodName as $val) {
                    #$reflection = new \ReflectionClass ($api);
                    #$methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

                    $method = new Reflectionmethod ($className,$val);
         
                    if (!$method->isPublic() || strpos($val, '__') === 0 || $val === "getRulesAction") {
                        continue;
                    }
                    //获取方法的注释
                    $doc = $method->getDocComment();

                    $doc = ltrim($doc, '/**');
                    $doc = rtrim($doc, '*/');
         
                    if (empty($doc)) {
                        continue;
                    }
                    $doc = explode("\n", $doc);
                    $data = [];
                    foreach ($doc as $value) {
                        //获取接口名
                        $strNum = strpos($value, '* @method');
                        if ($strNum !== false) {
                            $data['api_name'] = trim(str_replace('* @method', '', $value));
                            continue;
                        }
                        //获取请求地址
                        $strNum = strpos($value, '* @url');
                        if ($strNum !== false) {
                            $data['url'] = trim(str_replace('* @url', '', $value));
                            continue;
                        }
                        //获取请求类型
                        $strNum = strpos($value, '* @http');
                        if ($strNum !== false) {
                            $data['http'] = trim(str_replace('* @http', '', $value));
                            continue;
                        }
                        //获取接口说明
                        $desc = strpos($value, '* @desc');
                        if ($desc !== false) {
                            $data['desc'] = trim(str_replace('* @desc', '', $value));
                            continue;
                        }
                        //获取字段相关信息
                        $strNum = strpos($value, '* @param');
                        if ($strNum !== false) {
                            //获取字段名
                            $params = trim(str_replace('* @param', '', $value));
                            $lineNum = strpos($params, ' ');
                            $field_name = trim(mb_substr($params, 0, $lineNum, 'UTF-8'));
                            //获取字段类型
                            $params = trim(str_replace($field_name, '', $params));
                            $lineNum = strpos($params, ' ');
                            $field_type = trim(mb_substr($params, 0, $lineNum, 'UTF-8'));
                            //获取字段是否必填
                            $params = trim(str_replace($field_type, '', $params));
                            $lineNum = strpos($params, '[必填]');
                            if ($lineNum !== false) {
                                $non_empty = trim(substr($params, 0, 8));
                            } else {
                                $non_empty = '[选填]';
                            }
                            //获取字段描述
                            $params = trim(str_replace($non_empty, '', $params));
                            $data['params'][] = [
                                'field_name' => $field_name,
                                'field_type' => $field_type,
                                'non-empty' => $non_empty,
                                'field_desc' => $params
                            ];
                        }
                        //获取返回值信息
                        $strNum = strpos($value, '* @returnValue');

                        if ($strNum !== false) {
                            //获取字段名
                            $params = trim(str_replace('* @returnValue', '', $value));
                            $lineNum = strpos($params, ' ');
                            $field_name = trim(mb_substr($params, 0, $lineNum, 'UTF-8'));
                            //获取字段类型
                            $params = trim(str_replace($field_name, '', $params));
                            $lineNum = strpos($params, ' ');
                            $field_type = trim(mb_substr($params, 0, $lineNum, 'UTF-8'));
  
                            //获取字段描述
                            $params = trim(str_replace($field_type, '', $params));
                            $data['returnValue'][] = [
                                'field_name' => $field_name,
                                'field_type' => $field_type,
                                'field_desc' => $params
                            ];
                        }
                        //获取返回值
                        $strNum = strpos($value, '* @return ');
                        if ($strNum !== false) {
                            $data['return'] = trim(str_replace('* @return', '', $value));
                            continue;
                        }
                        //获取作者
                        $strNum = strpos($value, '* @author');
                        if ($strNum !== false) {
                            $data['author'] = trim(str_replace('* @author', '', $value));
                            continue;
                        }
                        //获取最后修改时间
                        $strNum = strpos($value, '* @copyright');
                        if ($strNum !== false) {
                            $data['copyright'] = trim(str_replace('* @copyright', '', $value));
                            continue;
                        }
                    }
                    $docData[] = $data;
                }
            }
        
        }
        return $docData;
    }
    /**
     * 设置页眉
     * @param string title 主信息
     * @param sring  info  简介
     **/
    public function setHeaders($title = '', $info = '')
    {
        //设置页眉
        $header = $this->section->createHeader();
        $this->PHPWord->addTableStyle('headers', ['borderBottomSize' => 6, 'borderBottomColor' => 'cccccc']);
        $table = $header->addTable('headers');
        $table->addRow();
        $table->addCell(6000)->addText(iconv('utf-8', 'GB2312//IGNORE', ' ' . $title));
        $table->addCell(4000)->addText(iconv('utf-8', 'GB2312//IGNORE', $info . '  '), NUll, ['align' => 'right']);
    }
    /**
     * 设置文档标题
     * @param string $title 文档标题
     * @param string $subheading 文档副标题
     **/
    public function setWordTitle($title = '')
    {
        //顶级标题样式
        $titleStyle = [
            'color' => '000000',
            'size' => 18,
            'bold' => true,
        ];
        //创建文档顶级标题
        $this->PHPWord->addTitleStyle(1, $titleStyle);
        $this->section->addTitle(iconv('utf-8', 'GB2312//IGNORE', $title), 1);
    }
    /**
     * 顶级接口标题
     * @param array dataArr 要生成的数组
     * @param string  domain 项目域名
     **/
    public function setTopLevel($dataArr = [], $codeData = [])
    {
        //顶级标题样式
        $titleStyle = [
            'color' => '000000',
            'size' => 16,
            'bold' => true,
        ];
        $this->PHPWord->addTitleStyle(2, $titleStyle);
        //更新日志
        //检测是否拥有更新日志
        if (array_key_exists($this->version, $this->config)) {
            
            $this->section->addTextBreak();
            $this->section->addTitle(iconv('utf-8', 'GB2312//IGNORE', '接口更新记录'), 2);
            $this->setSaveLog();
            $this->setLogContent($this->config[$this->version]);
        }
   
        //设置公共header
        if (array_key_exists('header',$this->config)) {
            $this->section->addTextBreak();
            $this->section->addTitle(iconv('utf-8', 'GB2312//IGNORE', '公共请求header'), 2);
            $this->setHeaderTitle();
            $this->setHeaderContent($this->config['header']);
        }
        //如果有code值就渲染
        if (!empty($codeData)) {
            $this->section->addTextBreak();
            $this->section->addTitle(iconv('utf-8', 'GB2312//IGNORE', '接口返回码说明'), 2);
//           $this->setContentTitle('接口返回码说明：');
            $this->setCodeTitle();
            $this->setCodeContent($codeData);
        }
        //遍历所有顶级标题
        foreach ($dataArr as $value) {
            $this->section->addTextBreak();
            $this->section->addTitle(iconv('utf-8', 'GB2312//IGNORE', $this->number . '、' . $value['api_name']), 2);
            $this->setContentTitle('请求地址：');
            $this->setRequestUrl($this->config['test_url'] . $value['url']);
            $this->setContentTitle('请求方式：');
            $this->setRequestMode($value['http']);
			if(array_key_exists('desc', $value)){
				$this->setContentTitle('接口说明：');
				$this->setApiDesc($value['desc']);
			}
            if(array_key_exists('params', $value)){
				$this->setContentTitle('请求参数：');
				$this->setParameterTitle();
				$this->setParameterContent($value['params']);
			}

            if(array_key_exists('returnValue', $value)){
                $this->setContentTitle('返回结果说明：');
                $this->setReturnDataParamTitle();
                $this->setReturnDataParam($value['returnValue']);
            }
            $this->setContentTitle('成功示例：');
            $this->setReturnData($value['return']);
            $this->number++;
        }

    }
    /**
     * 二级接口标题
     * @param array dataArr 要生成的数组
     * @param string  domain 项目域名
     **/
//   public function setTwoLevel($dataArr=[],$domain)
//   {
//
//       //顶级标题样式
//       $titleStyle = [
//           'color'=>'000000',
//           'size'=>18,
//           'bold'=>true,
//       ];
//       $this->PHPWord->addTitleStyle(2, $titleStyle);
//       $this->PHPWord->addTitleStyle(3, $titleStyle);
//       //遍历所有顶级标题
//       $id = 0;
//       $i = 0;
//       foreach ($dataArr as $value) {
//           if($value['id'] != $id){
//               //如果当前id不等于上次保存的id，则为2级分类
//               $this->section->addTitle(iconv('utf-8', 'GB2312//IGNORE', $this->number.'、'.$value['name']), 2);
//               $id = $value['id'];
//               $i=0;
//               $this->number++;
//           }
//           $i++;
//           //添加三级分类
//           $this->section->addTextBreak();
//           $this->section->addTitle(iconv('utf-8', 'GB2312//IGNORE', ($this->number-1).'.'.$i.'、'.$value['title']), 3);
//           $this->setContentTitle('请求地址：');
//           $this->setRequestUrl($domain.$value['post_url']);
//           $this->setContentTitle('请求方式：');
//           $this->setRequestMode($value['post_type']);
//           if($value['desc'] != ''){
//               $this->setContentTitle('接口说明：');
//               $this->setApiDesc($value['desc']);
//           }
//           $this->setContentTitle('请求参数：');
//           $this->setParameterTitle();
//           $this->setParameterContent($value['field_describe']);
//
//           // $this->setContentTitle('成功示例：');
//           $this->setContentTitle('成功示例：');
//           $this->setReturnData($value['demonstrate']);
//
//       }
//   }
    /**
     * 设置内容小标题
     * @param string title 小标题名称
     **/
    public function setContentTitle($title = '')
    {
        $this->section->addTextBreak();
        $this->section->addText(iconv('utf-8', 'GB2312//IGNORE', $title), ['size' => 12, 'bold' => true]);
    }
    /**
     * 设置请求地址
     * @param string url 请求域名
     **/
    public function setRequestUrl($url = '')
    {
        $this->section->addTextBreak();
        $styleTable = array('bgColor' => 'ADD8E6', 'cellMarginTop' => '80');
        // $styleCell = array('valign'=>'center', 'align'=>'center');
        $this->PHPWord->addTableStyle('apiurl', $styleTable);
        $table = $this->section->addTable('apiurl');
        // //添加行
        $table->addRow(500);
        $table->addCell(200, ['bgColor' => 'dddddd', 'valign' => 'center'])->addText('');
        //字段名称
        $table->addCell(9500)->addText(iconv('utf-8', 'GB2312//IGNORE', ' ' . $url), ['size' => '14', 'color' => '428bca']);
    }
    /**
     * 设置请求方式
     * @param
     **/
    public function setRequestMode($request)
    {
        $this->section->addTextBreak();
        $styleTable = array('bgColor' => 'ADD8E6', 'cellMarginTop' => '80');
        // $styleCell = array('valign'=>'center', 'align'=>'center');
        $this->PHPWord->addTableStyle('apiurl', $styleTable);
        $table = $this->section->addTable('apiurl');
        //添加行
        $table->addRow(500);
        $table->addCell(200, ['bgColor' => 'dddddd'])->addText('');
        //字段名称
        $table->addCell(9500)->addText(iconv('utf-8', 'GB2312//IGNORE', ' ' . $request), ['size' => '14', 'color' => '428bca']);
    }
    /**
     * 设置接口描述
     * @param string desc 描述信息
     **/
    public function setApiDesc($desc = '')
    {
        $this->section->addTextBreak();
        $styleTable = array('bgColor' => 'FFFFFF', 'cellMarginTop' => '80');
        $styleCell = array('valign'=>'center', 'align'=>'center');
        $this->PHPWord->addTableStyle('apiurl', $styleTable);
        $table = $this->section->addTable('apiurl');
        //添加行
        $table->addRow(500);
        $table->addCell(200, ['bgColor' => 'dddddd'])->addText('');
        $table->addCell(200, ['bgColor' => 'ADD8E6'])->addText('');
        //字段名称
        $table->addCell(9500, ['cellMarginLeft' => 1000])->addText(iconv('utf-8', 'GB2312//IGNORE', ' 　' . $desc), ['size' => '14', 'color' => '000000']);
        $table->addCell(200, ['bgColor' => 'ADD8E6'])->addText('');
    }
    /**
     * 设置请求参数标题
     **/
    public function setParameterTitle()
    {
        $this->section->addTextBreak();
        $styleTable = array('borderSize' => 6, 'borderColor' => 'cccccc');
        // $styleCell = array('valign'=>'center', 'align'=>'center');
        $this->PHPWord->addTableStyle('apiTab', $styleTable);
        //表格创建
        $table = $this->section->addTable('apiTab');
        //添加行
        $table->addRow(400);
        //字段名称
        $table->addCell(2000, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', '字段名称'), NULL, ['align' => 'center']);
        //字段类型
        $table->addCell(1500, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', '字段类型'), NULL, ['align' => 'center']);
        //是否必填
        $table->addCell(1000, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', '必填'), NULL, ['align' => 'center']);
        //字段描述
//       $table -> addCell(3000,['valign'=>'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', '默认值'),NULL,['align'=>'center']);
        //字段描述
        $table->addCell(6300, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', '字段描述'), NULL, ['align' => 'center']);
        $this->table = $table;
    }

       /**
     * 设置请求参数标题
     **/
    public function setReturnDataParamTitle()
    {
        $this->section->addTextBreak();
        $styleTable = array('borderSize' => 6, 'borderColor' => 'cccccc');
        // $styleCell = array('valign'=>'center', 'align'=>'center');
        $this->PHPWord->addTableStyle('apiTab', $styleTable);
        //表格创建
        $table = $this->section->addTable('apiTab');
        //添加行
        $table->addRow(400);
        //字段名称
        $table->addCell(2000, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', '字段名称'), NULL, ['align' => 'center']);
        //字段类型
        $table->addCell(1500, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', '字段类型'), NULL, ['align' => 'center']);

        //字段描述
//       $table -> addCell(3000,['valign'=>'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', '默认值'),NULL,['align'=>'center']);
        //字段描述
        $table->addCell(6300, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', '字段描述'), NULL, ['align' => 'center']);
        $this->table = $table;
    }
    /**
     * 设置请求参数标题
     **/
    public function setSaveLog()
    {
        $this->section->addTextBreak();
        $styleTable = array('borderSize' => 6, 'borderColor' => 'cccccc');
        // $styleCell = array('valign'=>'center', 'align'=>'center');
        $this->PHPWord->addTableStyle('apiTab', $styleTable);
        //表格创建
        $table = $this->section->addTable('apiTab');
        //添加行
        $table->addRow(400);
        //更新时间
        $table->addCell(2000, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', '更新日期'), NULL, ['align' => 'center']);
        //接口名称
        $table->addCell(2000, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', '接口名称'), NULL, ['align' => 'center']);
        //更新内容
        $table->addCell(4800, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', '更新内容'), NULL, ['align' => 'center']);
        //更新人
        // $table->addCell(2000, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', '更新人'), NULL, ['align' => 'center']);
        //字段描述
//       $table -> addCell(3000,['valign'=>'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', '默认值'),NULL,['align'=>'center']);
        $this->table = $table;
    }
        /**
     * 设置请求参数标题
     **/
    public function setHeaderTitle()
    {
        $this->section->addTextBreak();
        $styleTable = array('borderSize' => 6, 'borderColor' => 'cccccc');
        // $styleCell = array('valign'=>'center', 'align'=>'center');
        $this->PHPWord->addTableStyle('apiTab', $styleTable);
        //表格创建
        $table = $this->section->addTable('apiTab');
        //添加行
        $table->addRow(400);
        //更新时间
        $table->addCell(2000, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', '参数名称'), NULL, ['align' => 'center']);
        //接口名称
        $table->addCell(2000, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', '是否必填'), NULL, ['align' => 'center']);
        //更新内容
        $table->addCell(6300, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', '使用介绍'), NULL, ['align' => 'center']);
        //更新人
        #$table->addCell(2000, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', '更新人'), NULL, ['align' => 'center']);
        //字段描述
//       $table -> addCell(3000,['valign'=>'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', '默认值'),NULL,['align'=>'center']);
        $this->table = $table;
    }
    /**
     * @param $data 要请求的参数列表
     * @return bool
     */
    public function setParameterContent($data)
    {
        if (empty($data)) {
            return false;
        }
        foreach ($data as $value) {
            $table = $this->table;
            $table->addRow(400);
            //字段名称
            $table->addCell(2000, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', $value['field_name']), NULL, ['align' => 'center']);
            //字段类型
            $table->addCell(1000, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', $value['field_type']), NULL, ['align' => 'center']);
            //是否必填
            $table->addCell(1000, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', $value['non-empty']), NULL, ['align' => 'center']);
            //字段描述
//           $table -> addCell(3000,['valign'=>'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', ' '.$value['default_value']));
            //字段描述
            $table->addCell(6300, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', ' ' . $value['field_desc']));
        }
    }

    public function setReturnDataParam($data)
    {
        if (empty($data)) {
            return false;
        }

        foreach ($data as $value) {
            $table = $this->table;
            $table->addRow(400);
            //字段名称
            $table->addCell(2000, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', $value['field_name']), NULL, ['align' => 'center']);
            //字段类型
            $table->addCell(1000, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', $value['field_type']), NULL, ['align' => 'center']);
            // //是否必填
            // $table->addCell(1000, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', $value['non-empty']), NULL, ['align' => 'center']);
            //字段描述
//           $table -> addCell(3000,['valign'=>'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', ' '.$value['default_value']));
            //字段描述
            $table->addCell(6300, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', ' ' . $value['field_desc']));
        }
    }
    /**
     * @param $data 要更新的日志记录
     * @return bool
     */
    public function setLogContent($data)
    {
        if (empty($data)) {
            return false;
        }
        foreach ($data as $value) {
            $table = $this->table;
            $table->addRow(400);
            //更新时间
            $table->addCell(1500, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', $value[0]), NULL, ['align' => 'center']);
            //接口名称
            $table->addCell(2000, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', $value[1]), NULL, ['align' => 'center']);
            //更新内容
            $table->addCell(4800, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', ' ' . $value[2]));
       
        }
    }
    
    /**
     * @param $data 设置公共header
     * @return bool
     */
    public function setHeaderContent($data)
    {
        if (empty($data)) {
            return false;
        }
        foreach ($data as $value) {
            $table = $this->table;
            $table->addRow(400);
            //更新时间
            $table->addCell(2000, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', $value[0]), NULL, ['align' => 'center']);
            //接口名称
            $table->addCell(2000, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', $value[1]), NULL, ['align' => 'center']);
            //更新内容
            $table->addCell(6300, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', ' ' . $value[2]));
            //是否必填
           #$table->addCell(2000, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', $value[3]), NULL, ['align' => 'center']);
        }
    }
    /**
     * 设置code标题
     **/
    public function setCodeTitle()
    {
        $this->section->addTextBreak();
        $styleTable = array('borderSize' => 6, 'borderColor' => 'cccccc');
        $this->PHPWord->addTableStyle('code', $styleTable);
        //表格创建
        $table = $this->section->addTable('code');
        //添加行
        $table->addRow(400);
        //字段名称
        $table->addCell(4000, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', 'code'), NULL, ['align' => 'center']);
        //字段类型
        $table->addCell(6000, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', 'code说明'), NULL, ['align' => 'center']);
        $this->table = $table;
    }
    /**
     * 设置code内容
     * @param string $data 内容
     **/
    private function setCodeContent($data)
    {
        foreach ($data as $value) {
            $table = $this->table;
            //添加行
            $table->addRow(400);
            //字段名称
            $table->addCell(4000, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', $value['code']), NULL, ['align' => 'center']);
            //字段类型
            $table->addCell(6000, ['valign' => 'center'])->addText(iconv('utf-8', 'GB2312//IGNORE', ' ' . $value['desc']));
        }
    }

    /**
     * 返回结果说明
     **/
    public function setReturnData($data)
    {
        $this->section->addText(iconv('utf-8', 'GB2312//IGNORE', $data));
    }
    /**
     * @param string $docName 文档名称
     * @return string 保存文档
     */
    public function dow($docName = '接口文档')
    {
        //设置页脚
        $this->setFooter();
        

        //下载信息
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($this->PHPWord, 'Word2007');
        #print_r($objWriter);
        $url = $this->config['word_name'] . date('YmdHi') . '.docx';
        //检测是否拥有目录
        $dirName = $this->check_dir();
        $fileName = $url;
        $url = $dirName . '/' . $url;
        $objWriter->save($url);
        return [
            'url'=>$url,
            'file_name'=>$fileName
        ];
    }
    /**
     * 设置页脚
     **/
    private function setFooter()
    {
        //添加页脚
        $footer = $this->section->createFooter();
        $footer->addPreserveText(iconv('utf-8', 'GB2312//IGNORE', '当前第{PAGE}页，共{NUMPAGES}页'), NULL, ['align' => 'center']);
    }
    /**
     * 检测是否拥有文件存放目录，如果没有则创建
     */
    private function check_dir()
    {
        $dirName = $this->config['doc_dir'];
        if (file_exists($dirName) == false) {
            mkdir($dirName, 0755, true);
        }
        return rtrim($dirName, '/');
    }
    /**
     * 遍历目录
     **/
    private function listDir()
    {
        $dirInfo = [];
        foreach ($this->config['api_dir_path'][$this->modules] as $value){
            $dir = APP_PATH.'/application/modules/'.$value.'/';
            foreach (glob($dir . '*') as $v) {
                if (is_dir($v)) {
					continue;
                    //$dirInfo = array_merge($dirInfo, listDir($v));
                } else {
                    $dirInfo[] = $v;
                }
            }
        }
        return $dirInfo;
    }
}