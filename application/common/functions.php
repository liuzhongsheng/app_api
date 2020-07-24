<?php
function startwith($str,$pattern) {
    if(empty($str)){
        return true;
    }
    return (strpos($str,$pattern)) === 0 ? true:false;
}
/**
 * 只保留字符串首尾字符，隐藏中间用*代替（两个字符时只显示第一个）
 * @param string $user_name 姓名
 * @return string 格式化后的姓名
 */
function substr_cut($user_name){
    $strlen     = mb_strlen($user_name, 'utf-8');
    $firstStr     = mb_substr($user_name, 0, 1, 'utf-8');
    $lastStr     = mb_substr($user_name, -1, 1, 'utf-8');
    return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
}
// 匹配手机号或者电话
function isTel($tel)
{
    // 验证联系电话
    $isMob = "/^1[34578]{1}\d{9}$/";

    $isTel = "/^([0-9]{3,4}-)?[0-9]{7,8}$/";


    if (!preg_match($isMob, $tel) && !preg_match($isTel, $tel)) {

        return false;

    }
    return true;
}
// 加密 并转换成base64编码
function aesEncrypt($data,$Key,$iv) {
     return base64_encode(openssl_encrypt($data, 'AES-128-CBC', $Key, OPENSSL_RAW_DATA, $iv));
}

//解密base64编码
function aesDecrypt($data,$Key,$iv) {
     return openssl_decrypt(base64_decode($data), 'AES-128-CBC', $Key, OPENSSL_RAW_DATA, $iv);
}
// rpc error 返回
function rpcReturn($code = 200, $data = null)
{
    $param = require 'language.php';
    return msgpack_pack([
        'code'    => $code,
        'success' => $code == 200 ? 'true' : 'false',
        'message' => $param[$code],
        'data'    => $data,
    ]);
}
function checkCC()
{

    // empty($_SERVER['HTTP_VIA']) or exit('Access Denied'); //代理IP直接退出
    // $seconds  = '1'; //时间间隔
    // $refresh  = '5'; //防止快速刷新  刷新次数 设置监控变量
    // $cur_time = time();
    // $url      = $_SERVER["PHP_SELF"];
    // if (isset($_SESSION['last_time']) && $_SESSION['php_self'] == $url) {
    //     $_SESSION['refresh_times'] += 1;
    // } else {
    //     $_SESSION['refresh_times'] = 1;
    //     $_SESSION['php_self']      = $url;
    //     $_SESSION['last_time']     = $cur_time;
    // }
    // //处理监控结果
    // if ($cur_time - $_SESSION['last_time'] < $seconds && $_SESSION['php_self'] == $url) {
    //     if ($_SESSION['refresh_times'] >= $refresh) {
    //         //跳转至攻击者服务器地址
    //         header(sprintf('Location:%s', 'http://127.0.0.1'));
    //         exit('Access Denied');
    //     }
    // } else {
    //     $_SESSION['refresh_times'] = 0;
    //     $_SESSION['php_self']      = '';
    //     $_SESSION['last_time']     = $cur_time;
    // }
}
//加载语言包
function loadLang($code = 200)
{
    $data = require 'language.php';
    if (!array_key_exists($code, $data)) {
        return [
            'code'    => 300,
            'message' => '未定义语言包',
        ];
    }
    return [
        'code'    => $code,
        'message' => $data[$code],
    ];
}
function debugLog($message = null, $type = '')
{
    $api = $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
    if (is_array($message)) {
        if ($type == 'log') {
            $message = $message[0];
        } elseif ($type == 'error') {
            $message = $message[2];
        } elseif ($type == 'runtime') {
            $message = implode(',', $message);
        } else {
            $type = 'error';
        }
    }
    $dir  = mkLogDir('log/');
    $date = date('Y-m-d H:i:s');
    // $user_id = 1;
    $str = <<<Eof
============== $date ==================
接口:  $api
类型:  $type
内容:  $message

Eof;
    file_put_contents($dir . date('Ymd') . '_' . $type . '.log', $str . PHP_EOL, FILE_APPEND);
}
function dump_log($message = null, $logName = '')
{
    $dir = mkLogDir('log/');
    file_put_contents($dir . date('Ymd') . '_' . $logName . '.log', $message . PHP_EOL, FILE_APPEND);
}

# 创建缓存目录
function mkLogDir($logDir)
{
    $dirPath = APP_PATH . '/runtime/' . $logDir;
    if (!is_dir($dirPath)) {
        mkdir($dirPath, 0755, true);
    }
    return $dirPath;

}
// 获取年龄
function countage($birthday)
{
    $year  = date('Y');
    $month = date('m');
    if (substr($month, 0, 1) == 0) {
        $month = substr($month, 1);
    }
    $day = date('d');
    if (substr($day, 0, 1) == 0) {
        $day = substr($day, 1);
    }
    $arr = explode('-', $birthday);

    $age = $year - $arr[0];
    if ($month < $arr[1]) {
        $age = $age - 1;

    } elseif ($month == $arr[1] && $day < $arr[2]) {
        $age = $age - 1;

    }
    return $age;
}

//检测是否是手机号
function isPhone($mobile)
{
    if (!is_numeric($mobile)) {
        return false;
    }
    return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^19[^4]{1}\$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
}

//检测是否是邮件
function isEmail($email)
{
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    }
    return false;
}
//获取真实ip
function ip()
{
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $ip = getenv('REMOTE_ADDR');
    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches[0] : '';
}

function setRandNum($num = 12)
{
    $code = '';
    for ($i = 1; $i < $num; $i++) {
        //通过循环指定长度
        $randcode = mt_rand(0, 9); //指定为数字
        $code .= $randcode;
    }
    return $code;
}

function hiddenPhone($phone)
{
    $p = substr($phone, 0, 3) . "****" . substr($phone, 7, 4);
    return $p;
}

function setOrderNumber()
{
    return 'O' . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
}
/**
 * 获取地区的拼音首字母
 * @param string $s  地区名
 * @return string 大写字母
 */
function getFirstChar($s)
{
    $s0 = mb_substr($s, 0, 1, 'utf-8'); //获取名字的姓
    if ($s0 == '亳') {
        return 'B';
    } elseif ($s0 == '衢') {
        return 'Q';
    } elseif ($s0 == '泸' || $s0 == '漯') {
        return 'L';
    } elseif ($s0 == '濮') {
        return 'P';
    }
    $s = iconv('UTF-8', 'gb2312', $s0); //将UTF-8转换成GB2312编码
    if (ord($s0) > 128) {
        //汉字开头，汉字没有以U、V开头的
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
        if ($asc >= -20319 and $asc <= -20284) {
            return "A";
        }

        if ($asc >= -20283 and $asc <= -19776) {
            return "B";
        }

        if ($asc >= -19775 and $asc <= -19219) {
            return "C";
        }

        if ($asc >= -19218 and $asc <= -18711) {
            return "D";
        }

        if ($asc >= -18710 and $asc <= -18527) {
            return "E";
        }

        if ($asc >= -18526 and $asc <= -18240) {
            return "F";
        }

        if ($asc >= -18239 and $asc <= -17760) {
            return "G";
        }

        if ($asc >= -17759 and $asc <= -17248) {
            return "H";
        }

        if ($asc >= -17247 and $asc <= -17418) {
            return "I";
        }

        if ($asc >= -17417 and $asc <= -16475) {
            return "J";
        }

        if ($asc >= -16474 and $asc <= -16213) {
            return "K";
        }

        if ($asc >= -16212 and $asc <= -15641) {
            return "L";
        }

        if ($asc >= -15640 and $asc <= -15166) {
            return "M";
        }

        if ($asc >= -15165 and $asc <= -14923) {
            return "N";
        }

        if ($asc >= -14922 and $asc <= -14915) {
            return "O";
        }

        if ($asc >= -14914 and $asc <= -14631) {
            return "P";
        }

        if ($asc >= -14630 and $asc <= -14150) {
            return "Q";
        }

        if ($asc >= -14149 and $asc <= -14091) {
            return "R";
        }

        if ($asc >= -14090 and $asc <= -13319) {
            return "S";
        }

        if ($asc >= -13318 and $asc <= -12839) {
            return "T";
        }

        if ($asc >= -12838 and $asc <= -12557) {
            return "W";
        }

        if ($asc >= -12556 and $asc <= -11848) {
            return "X";
        }

        if ($asc >= -11847 and $asc <= -11056) {
            return "Y";
        }

        if ($asc >= -11055 and $asc <= -10247) {
            return "Z";
        }

    } else if (ord($s) >= 48 and ord($s) <= 57) {
        //数字开头
        switch (iconv_substr($s, 0, 1, 'utf-8')) {
            case 1:return "Y";
            case 2:return "E";
            case 3:return "S";
            case 4:return "S";
            case 5:return "W";
            case 6:return "L";
            case 7:return "Q";
            case 8:return "B";
            case 9:return "J";
            case 0:return "L";
        }
    } else if (ord($s) >= 65 and ord($s) <= 90) {
        //大写英文开头
        return substr($s, 0, 1);
    } else if (ord($s) >= 97 and ord($s) <= 122) {
        //小写英文开头
        return strtoupper(substr($s, 0, 1));
    } else {
        return iconv_substr($s0, 0, 1, 'utf-8');
        //中英混合的词语，不适合上面的各种情况，因此直接提取首个字符即可
    }
}

// 计算测试者年龄
function ageCalculation($birthday)
{
    /**
    (1)3－5岁者
    　　测试时已过当年生日,且超过6个月者:
    　　年龄=测试年-出生年+0.5
    　　测试时已过当年生日,且不满6个月者:
    　　年龄=测试年-出生年
    　　测试时未过当年生日,且距生日6个月以下者:
    　　年龄=测试年-出生年-0.5
    　　测试时未过当年生日,且距生日6个月以上者；
    　　年龄=测试年－出生年-1
    (2)6岁者
    　　测试时已过当年生日者:年龄=测试年-出生年
    　　测试时未过当年生日者:年龄=测试年-出生年-1

     **/
    // 当前年
    $thisYear = date('Y');
    // 当前月
    $thisMonth = date('n');
    // 今天日期
    $today      = date('j');
    $birthday   = explode('-', $birthday);
    $discrepant = ($birthday[1] - $thisMonth);
    // 年龄
    $age = $thisYear - $birthday[0];

    // 获取3-5岁年龄
    if ($age >= 3 && $age <= 6) {
        // 判断出生月份是否大于当前月份

        if ($birthday[1] <= $thisMonth) {
            if ($discrepant <= 6) {
                //测试时已过当年生日,且不满6个月者:
                //年龄=测试年-出生年
                return $age;
            }
            //测试时已过当年生日,且超过6个月者:
            //年龄=测试年-出生年+0.5
            return $age + 0.5;
        }
        if ($discrepant > 6 || ($discrepant == 6 && $birthday[2] < $today)) {
            //测试时未过当年生日,且距生日6个月以上者；
            //年龄=测试年－出生年-1
            return $age - 1;
        }
        //测试时未过当年生日,且距生日6个月以上者；
        //年龄=测试年－出生年-1
        return $age - 0.5;
    }
    //测试时已过当年生日者:年龄=测试年-出生年
    if ($birthday[1] < $thisMonth) {
        return $age;
    }
    //测试时未过当年生日者:年龄=测试年-出生年-1
    if ($birthday[2] < $today) {
        return $age - 1;
    }
    return $age;
}
