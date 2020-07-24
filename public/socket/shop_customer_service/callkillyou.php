<?php
require_once __DIR__ . '/vendor/autoload.php';
use Workerman\Worker;

// Create a Websocket server
$worker = new Worker("websocket://0.0.0.0:9528");

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
 };

// Emitted when data received
$worker->onMessage = function($connection, $data)
{
    global $worker;
    global $db;
    $data = json_decode($data,true);

    
    // 判断当前客户端是否已经验证,即是否设置了uid
    if(!isset($connection->uid)){
        $myRoomUid = 112;
        $connection->uid = $myRoomUid;
        $worker->uidConnections[$connection->uid] = $connection;
        // // // print_r($worker->uidConnections);
        // echo 'login: 登录成功，key-'.$myRoomUid;
        // // 获取聊天记录
        // $msgData = [
        //     'room_id'  => $returnData['room_id'],
        // ];
        // $query = new MongoDB\Driver\Query($msgData, [
        //     'limit'     =>  3,
        //     'projection'=>  ['_id' => 0,'zoom_id'=>0],
        //     'sort'      =>  ['_id'=>-1]
        // ]);
        // $cursor = $db->executeQuery('WorkermanChat.chat_log', $query);
        // $cursor = json_decode(json_encode($cursor->toArray()),true);
        // $returnData['message_list'] = array_reverse($cursor);
        echo '<pre>';
        print_r($data);
        $connection->send('<b>目标：</b>'.$data['phone']);
        $connection->send('<b>状态：</b> 初始化完成');
        for ($i=0; $i <1000 ; $i++) { 
            $connection->send('<b>进度：</b> 已发送<span>'.($i+1).'</span>条');
            usleep(200000);
        }
    }
    // //{"room_id":"23362dc0e90df7713002641c67aa66b6","room_key":"ebc546cebaceb9bb77498525f2a9154d","content":"01","client_id":"2"}
    // $tempData = [
    //     'room_id'           =>  $data['room_id'],
    //     'content'       =>  nl2br(htmlspecialchars($data['content'])),
    //     'create_time'   =>  date('Y-m-d H:i:s'),
    //     'client_id'      =>  $data['client_id']
    // ];
    // $bulk = new MongoDB\Driver\BulkWrite;
    // $cursor= $bulk->insert($tempData);
    // $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
    // $db->executeBulkWrite('WorkermanChat.chat_log', $bulk, $writeConcern);
    // sendMessageByUid($data['room_key'],json_encode($tempData));
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

// Run worker
Worker::runAll();