<?php
class IndexController extends BaseController {
    public function indexAction() {//默认Action、
        

         $arrConfig = Yaf_Registry::get('config');
        $option    = [
            'database_type' => $arrConfig->mysql->db->type,
            'database_name' => $arrConfig->mysql->db->database,
            'server'        => $arrConfig->mysql->db->hostname,
            'username'      => $arrConfig->mysql->db->username,
            'password'      => $arrConfig->mysql->db->password,
            'prefix'        => $arrConfig->mysql->db->prefix,
            'logging'       => $arrConfig->mysql->db->log,
            'charset'       => 'utf8',
        ];
     $swladb= new \Medoo\Medoo($option);

            $option    = [
       'database_type' => $arrConfig->edu_mssql->db->type,
            'database_name' => $arrConfig->edu_mssql->db->database,
            'server'        => $arrConfig->edu_mssql->db->hostname,
            'username'      => $arrConfig->edu_mssql->db->username,
            'password'      => $arrConfig->edu_mssql->db->password,
            'prefix'        => $arrConfig->edu_mssql->db->prefix,
            'logging'       => $arrConfig->edu_mssql->db->log,
            'charset'       => 'utf8',
        ];
    $db = new \Medoo\Medoo($option);
    $data = $db->select("curriculum","*");
    foreach ($data as $key => $value) {
    	$newData = [
            'id' => $value['id'],
            'curriculum_number'	=> $value['curriculum_number'],
            'title'	=> $value['title'],
            'total_price'	=> $value['total_price'],
            'lessons'	=> $value['lessons'],
            'curricula_time'	=> $value['curricula_time'],
            'class_time'	=> $value['class_time'],
            'students_num'	=> $value['students_num'],
            'minimum_age'	=> $value['minimum_age'],
            'popularity'	=> $value['popularity'],
            'coupon'	=> $value['coupon'],
            'desc'	=> $value['desc'],
            'createtime'	=> strtotime($value['create_time']),
            'brand_number'	=> $value['brand_id'],
            'store_number'	=> $value['store_id'],
            'company_number'	=> $value['coid'],
            'thumb_src'	=> $value['thumb_src'],
            'app_class_id'	=> $value['app_class_id'],
            'weigh'	=> $value['sort'],
            'anytimewitch'	=> $value['anytime'] == 'on' ? 1: 0,
            'try_outwitch'	=> $value['try_out'] ? 0 : 1
    	];
    	if($value['delete_time']){
    		$newData['deletetime'] = strtotime($value['delete_time']);
    	}
    	print_r($newData);
    	 $swladb->insert('swla_edu_curriculum',$newData);
    }
    echo '<pre>';
    print_r($data);
    //  $arrConfig = Yaf_Registry::get('config');
    //     $option    = [
    //         'database_type' => $arrConfig->mysql->db->type,
    //         'database_name' => $arrConfig->mysql->db->database,
    //         'server'        => $arrConfig->mysql->db->hostname,
    //         'username'      => $arrConfig->mysql->db->username,
    //         'password'      => $arrConfig->mysql->db->password,
    //         'prefix'        => $arrConfig->mysql->db->prefix,
    //         'logging'       => $arrConfig->mysql->db->log,
    //         'charset'       => 'utf8',
    //     ];
    //  $swladb= new \Medoo\Medoo($option);

       
    // foreach ($data['districts'][0]['districts'] as $key => $value) {
    //     $center = explode(',', $value['center']);
    //     $res = [
    //         'name' => $value['name'],
    //         'area_code'=> $value['adcode'],
    //         'city_code'=>is_array($value['citycode']) ? implode(',', $value['citycode']) : $value['citycode'],
    //         'longitude'    =>$center[0],
    //         'latitude'     => $center[1]
    //     ];
    //      $swladb->insert('swla_area_province',$res);
    // }
    die();


        // foreach ($datas as $k => $v) {
        //     $t = $swladb->select("swla_area_area", '*',['area_code'=>$v['area_code']]);
        //     if(!empty($t)){
        //         $swladb->update('swla_area_area',[
        //         'name'=>$v['name'],
        //         'city_code'=>$v['city_code'],
        //     ],['area_code'=>$v['area_code']]);
        //     }
        // }
    // for($i = 0;$i<$count;$i++){
    //     $datas = $swladb->select("area_copy", '*',["LIMIT" => [$i*100 , 100]]);
    //     foreach ($datas as $k => $v) {
    //         $t = $swladb->select("swla_area_area", '*',['area_code'=>$v['area_code']]);
    //         if(!empty($t)){
    //             $swladb->update('swla_area_area',[
    //             'name'=>$v['name'],
    //             'city_code'=>$v['city_code'],
    //         ],['area_code'=>$v['area_code']]);
    //         }
    //     }
    // }
    // foreach($data as $key=>$value){
    //     $temp = explode(',', $value);
    //     $datas = [
    //         'name'  => $temp[0],
    //         'area_code'  => $temp[1],
    //         'city_code'  => $temp[2],
    //     ];
    //      $datas = $swladb->insert('area',$datas);
    // }
    return false;
    phpinfo();return false;
        $swladb = $this->getController()->connectMysql();
        echo '<pre>';
        print_r($db);
       $datas = $swladb->select("user", [
    "user_name",
    "email"
]);
 print_r($datas);

foreach($datas as $data)
{
    echo "user_name:" . $data["user_name"] . " - email:" . $data["email"] . "
";
}
 
// Select all columns
$datas = $swladb->select("account", "*");
 
// Select a column
$datas = $swladb->select("account", "user_name");
 
// $datas = array(
//  [0] => "foo",
//  [1] => "cat"
// )

        return false;
    }
}