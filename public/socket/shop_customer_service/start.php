<?php
require_once __DIR__ . '/vendor/autoload.php';
use Workerman\Worker;

// Create a Websocket server
$worker = new Worker("websocket://0.0.0.0:9527");

$worker->onWorkerStart = function($ws_worker)
{
    global $db;
    $db = new MongoDB\Driver\Manager('mongodb://120.77.152.67/');
};

// 4 processes
$worker->count = 1;
$worker->uidConnections = array();
// Emitted when new connection come
$worker->onConnect = function($connection)
{
    // echo '连接id:'.$connection->id;
    echo '<br>';
    echo '连接ip:'.$connection->getRemoteIp();
 };

// Emitted when data received
$worker->onMessage = function($connection, $data)
{
    global $worker;
    global $db;
    $ip = $connection->getRemoteIp();
    $data = json_decode($data,true);
    //{"type":"login","form_user_id":"2","to_user_id":"1","service_desk":"swla_shop"}
    
    // 判断当前客户端是否已经验证,即是否设置了uid
    if(!isset($connection->uid) && $data['type'] != 'message_list'){
        // {"type":"login","form_user_id":"2","to_user_id":"1","service_desk":"swla_shop"}
        // 登录流程
        // 检测目标是否登录
        // 如果如果当前用户没有记录，则查相反组合数据
        $clentData = [
            'to_user_id'    => $data['form_user_id'],
            'form_user_id'  => $data['to_user_id'],
            'service_desk'  => $data['service_desk'],
        ];
        $query = new MongoDB\Driver\Query($clentData, []);
        $cursor = $db->executeQuery('WorkermanChat.login_log', $query);
        $tempClentData = json_decode(json_encode($cursor->toArray()),true);
        //如果存在则取出,
        $returnData = [
            'room_key' => '',
            'room_id'  => '',
            'message_list'=>[]
        ];
        $myRoomUid = '';
        $msgData = [
            'form_user_id'  => $data['form_user_id'],
            'to_user_id'    => $data['to_user_id'],
            'service_desk'  => $data['service_desk']
        ];
        // 对方存在，开始登录
        if(!empty($tempClentData)){
            $query  = new MongoDB\Driver\Query($msgData, []);
            $cursor = $db->executeQuery('WorkermanChat.login_log', $query);
            $cursor = json_decode(json_encode($cursor->toArray()),true);
            //开始登录
            if(!empty($cursor)){
                $myRoomUid=$cursor[0]['room_key'];
                $bulk = new MongoDB\Driver\BulkWrite;
                $room_key = $cursor[0]['room_key'];
                $bulk -> update([
                    'room_key'      =>  $room_key,
                    'service_desk'  => $data['service_desk'],
                    'form_user_id'  =>  $data['form_user_id'],
                    'to_user_id'    =>  $data['to_user_id'],
                ],
                [
                    '$set' => [
                        'login' => 'true',
                        'last_login_ip'   => $ip,
                        'last_login_time' => date('Y-m-d H:i:s')
                    ]
                ]);
                $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
                $result = $db->executeBulkWrite('WorkermanChat.login_log', $bulk, $writeConcern);

            }else{
                $bulk = new MongoDB\Driver\BulkWrite;
                // 登录
                $msgData['room_id']  =  $tempClentData[0]['room_id'];
                $str = time().mt_rand(1,9999999);
                $room_key = md5('room_key'.$str);
                $msgData['room_key'] =  $room_key;
                $msgData['login']    =  'true';
                $msgData['last_login_time']    =  date('Y-m-d H:i:s');
                $cursor= $bulk->insert($msgData);
                $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
                $result = $db->executeBulkWrite('WorkermanChat.login_log', $bulk, $writeConcern);
            }
            $returnData['room_key'] = $tempClentData[0]['room_key'];
            $returnData['room_id']  = $tempClentData[0]['room_id'];
        }else{
            $str = time().mt_rand(1,9999999);
            $room_key = md5('room_key'.$str);
            $room_id  = md5('room_id'.$str);
            $clentData['room_id']  =  $room_id;
            $clentData['room_key'] =  $room_key;
            $clentData['login']    =  'false';
            $clentData['last_login_time']    =  '';
            $bulk = new MongoDB\Driver\BulkWrite;
            // 写入对方数据
            $cursor= $bulk->insert($clentData);
            // 登录
            $msgData['room_id']  =  $room_id;
            $str = time().mt_rand(1,9999999);
            $room_key = md5('room_key'.$str);
            $msgData['room_key'] =  $room_key;
            $msgData['login']    =  'true';
            $msgData['last_login_ip'] = $ip;
            $msgData['last_login_time']    =  date('Y-m-d H:i:s');
            $cursor= $bulk->insert($msgData);
            $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
            $result = $db->executeBulkWrite('WorkermanChat.login_log', $bulk, $writeConcern);
            $tempData = json_decode(json_encode($cursor),true);
            $returnData['room_key'] = $clentData['room_key'];
            $returnData['room_id']  = $clentData['room_id'];
        }
        $connection->uid = $myRoomUid;
        $worker->uidConnections[$connection->uid] = $connection;
        // // print_r($worker->uidConnections);
        echo 'login: 登录成功，key-'.$myRoomUid;
        // 获取聊天记录
        $msgData = [
            'room_id'  => $returnData['room_id'],
        ];
        $query = new MongoDB\Driver\Query($msgData, [
            'limit'     =>  3,
            'projection'=>  ['_id' => 0,'room_id'=>0],
            'sort'      =>  ['_id'=>-1]
        ]);
        $cursor = $db->executeQuery('WorkermanChat.chat_log', $query);
        $cursor = json_decode(json_encode($cursor->toArray()),true);
        $returnData['message_list'] = array_reverse($cursor);
        echo '<pre>';
        print_r(json_encode($returnData));
        return $connection->send(json_encode($returnData));
    }
    
  
    switch ($data['type']) {
        case 'say':
            // {"type":"say","room_id":"23362dc0e90df7713002641c67aa66b6","room_key":"ebc546cebaceb9bb77498525f2a9154d","content":"01","client_id":"2","service_desk":"swla_shop"}
            // 发言
            say($data);
            break;
        // 获取对话列表
        case 'message_list':
            // {"type":"message_list","client_id":"2","service_desk":"swla_shop"}
            // getMessageList($data);
            $data = getMessageList($data);
            $connection->send(json_encode($data));
            break;
        default:
            # code...
            break;
    }
  

};

// Emitted when connection closed
$worker->onClose = function($connection)
{
    echo "Connection closed\n";
};
// 针对uid推送数据
function sendMessageByUid($uid, $message)
{
    global $worker;
    if(isset($worker->uidConnections[$uid]))
    {
        $connection = $worker->uidConnections[$uid];
        $connection->send($message);
    }
}
// 对话列表
function getMessageList($data){
    global $db;
    $clentData = [
        'form_user_id'  => $data['client_id'],
        'service_desk'  => $data['service_desk']
    ];
    $query = new MongoDB\Driver\Query($clentData, [
        'sort'      =>  ['_id'=>-1]
    ]);
    $cursor = $db->executeQuery('WorkermanChat.login_log', $query);
    $clentData = json_decode(json_encode($cursor->toArray()),true);
    $returnData = [];
    foreach ($clentData as $key => $value) {
        // $tempData[] = $value['room_id'];
        $clentData = [
            'room_id'       => $value['room_id'],
            'service_desk'  => $data['service_desk']
        ];
        $query = new MongoDB\Driver\Query($clentData, [
            'limit'     => 1,
            'sort'      =>  ['_id'=>-1],
            'projection'=>  ['_id' => 0,'room_id'=>0]
        ]);
        $cursor = $db->executeQuery('WorkermanChat.chat_log', $query);
        $clentData = json_decode(json_encode($cursor->toArray()),true);
        echo '<pre>';
        print_r($clentData);
        $clentData[0]['nickname'] = '李黑帅';
        $clentData[0]['pic'] = 'https://wx.qlogo.cn/mmopen/vi_32/LS5XUjwZzDQSrVtBeOgYyYgR4ueW0XAKxUdN9y1iccerN5qPgaR1SQPTOwCpG2VLBJoBdTVf5t6eTbmmAOHuHjQ/132';
        
        unset($val);
        $returnData[] = $clentData[0];
    }
    return $returnData;
}
// 说话
function say($data)
{
    global $db;
    $tempData = [
        'room_id'       =>  $data['room_id'],
        'content'       =>  nl2br(htmlspecialchars($data['content'])),
        'create_time'   =>  date('Y-m-d H:i:s'),
        'client_id'     =>  $data['client_id'],
        'service_desk'  =>  $data['service_desk'],
        'ip'            =>  $ip
    ];
    $bulk = new MongoDB\Driver\BulkWrite;
    $cursor= $bulk->insert($tempData);
    $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
    $db->executeBulkWrite('WorkermanChat.chat_log', $bulk, $writeConcern);
    sendMessageByUid($data['room_key'],json_encode($tempData));
}

// Run worker
Worker::runAll();