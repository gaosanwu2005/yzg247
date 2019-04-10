<?php

namespace app\api\controller;

use think\worker\Server;

class Worker extends Server
{
    protected $socket = 'websocket://0.0.0.0:2346';

//初始化
    public function __construct()
    {
        config('app_debug', true);//关debug
        $this->processes = 1;  // ====这里进程数必须必须必须设置为1===
        parent::__construct();
        $this->worker->uidConnections = array(); //新增加一个属性，用来保存uid到connection的映射(uid是用户id或者客户端唯一标识)
    }

    public function index()
    {
//        $this->start();
    }

    /**
     * 收到信息
     * @param $connection
     * @param $data
     */
    public function onMessage($connection, $dataJ)
    {
        var_dump($dataJ);
        $data = json_decode($dataJ, true);
        $type = $data['type'];  //消息类型：bind-登录，绑定连接，msg-发送消息, read-已读消息, close-下线释放连接

        // 判断当前客户端是否已经验证,即是否设置了uid
        switch ($type) {
            case 'bind':
                $fromUserId = $data['fromUser'];
                $toUserId = $data['toUser'];
//                echo "in bind fromUser:$fromUserId toUser: $toUserId";
                if (!isset($connection->uid)) {
                    /* 保存uid到connection的映射，这样可以方便的通过uid查找connection，
                    * 实现针对特定uid推送数据
                    */
                    $connection->uid = $fromUserId;
                    if (!isset($this->worker->uidConnections[$fromUserId])) {

                        $this->worker->uidConnections[$connection->uid] = $connection;
                        $connection->send(json_encode(['type' => 'system', 'content' => '用户-' . $connection->uid . ' 已上线']));
                        echo '用户-' . $connection->uid . ' 已上线';
                    }
                    $historyMessageList = $this->getHistoryMessage($fromUserId, $toUserId);//先发送已读的历史消息
                    if (count($historyMessageList) > 0) {
                        $data['state'] = '1';
                        $data['type'] = 'msgList';
                        $data['messageList'] = $historyMessageList;
                        $connection->send(json_encode($data));
                    }

                    $unreadMessageList = $this->getUnreadMessage($fromUserId, $toUserId);//再发送未读的历史消息
                    if (count($unreadMessageList) > 0) {
                        $data['state'] = '0';
                        $data['type'] = 'msgList';
                        $data['messageList'] = $unreadMessageList;
                        $connection->send(json_encode($data));
                    }

                }
                break;
            case 'msg':
                $fromUserId = $data['fromUser'];
                $toUserId = $data['toUser'];
                $message['type'] = 'msg';
                $message['content'] = $data['msg'];

                if ($toUserId == 'all') {
                    echo '全局广播';
                    $this->broadcast($message);
                } else {
                    echo '给特定uid发送';
                    $this->sendMessageByUid($fromUserId, $toUserId, $message);
                }
                break;
            case 'group':
                $fromUserId = $data['fromUser'];
                $toUserId = $data['toUser'];
                $message['type'] = 'group';
                $message['content'] = $data['msg'];
                echo '给特定群id发送';
                $this->sendMessageBygroupid($fromUserId, $toUserId, $message);
                break;
            case 'read':
                $msgid = $data['msgid'];//信息id
                $this->receiveMessage($msgid);
                break;
            default:
                return $connection->send(json_encode(['type' => 'system', 'content' => '未知类型消息']));
        }
    }

// 向所有验证的用户推送数据
    function broadcast($message)
    {
        foreach ($this->uidConnections as $connection) {
            $data['msgid'] = 0;
            $data['message'] = $message;
            $connection->send(json_encode($data));
        }
    }

// 针对uid推送数据
    function sendMessageByUid($fromUserId, $uid, $message)
    {
        $msgid = $this->saveMessage($fromUserId, $uid, $message['content']);//向数据库保存聊天记录
        if (isset($this->worker->uidConnections[$uid]))  //如果接收用户当前在线
        {
            $connection = $this->worker->uidConnections[$uid];
            $data['msgid'] = $msgid;
            $data['type'] = $message['type'];
            $data['from_user_id'] = $fromUserId;
            $data['content'] = $message['content'];
            $data['send_time'] = date('Y-m-d H:i:s');
            echo $fromUserId . " 向用户" . $uid . "发送了" . $message['content'];
            $connection->send(json_encode($data));
        }
    }

// 针对 群id推送数据
    function sendMessageBygroupid($fromUserId, $groupid, $message)
    {
        $msgid = $this->saveGroupMessage($fromUserId, $groupid, $message['content']);//向数据库保存聊天记录
        $re = db('chart_groupuser')->find($groupid);
        $uids = json_decode($re['uids']);
        foreach ($uids as $uid) {
            if (isset($this->worker->uidConnections[$uid]))  //如果接收用户当前在线
            {
                $connection = $this->worker->uidConnections[$uid];
                $data['msgid'] = $msgid;
                $data['type'] = $message['type'];
                $data['from_user_id'] = $fromUserId;
                $data['content'] = $message['content'];
                $data['send_time'] = date('Y-m-d H:i:s');
                $connection->send(json_encode($data));
            }
        }
        echo $fromUserId . " 向群" . $groupid . "发送了" . $message['content'];

    }

    //向数据库保存聊天记录
    function saveMessage($fromUserId, $to_user_id, $content)
    {
        $re = db('chart')->insertGetId([
            'from_user_id' => $fromUserId,
            'to_user_id'   => $to_user_id,
            'content'      => $content,
            'send_time'    => date('Y-m-d H:i:s'),
        ]);
        return $re;
    }

    //向数据库保存群聊天记录
    function saveGroupMessage($fromUserId, $groupid, $content)
    {
        $re = db('chart_group')->insertGetId([
            'from_user_id' => $fromUserId,
            'to_user_id'   => $groupid,
            'content'      => $content,
            'send_time'    => date('Y-m-d H:i:s'),
        ]);
        return $re;
    }

//修改数据接收状态
    function receiveMessage($msgid)
    {
        $re = db('chart')->update(['id' => $msgid, 'is_receive' => 1]);
        return $re;
    }

//获取未读历史消息
    function getUnreadMessage($fromUserId, $toUserId)
    {
        $list = db('chart')->query("SELECT * FROM `fa_chart` WHERE  (`from_user_id` = {$fromUserId}  AND `to_user_id` = {$toUserId}  AND `is_receive` = 0) OR (`from_user_id` = {$toUserId}  AND `to_user_id` = {$fromUserId}  AND `is_receive` = 0) order by id desc limit 20");

        return array_reverse($list);
    }

//获取已读历史消息
    function getHistoryMessage($fromUserId, $toUserId)
    {
        $list = db('chart')->query("SELECT * FROM `fa_chart` WHERE  (`from_user_id` = {$fromUserId}  AND `to_user_id` = {$toUserId}  AND `is_receive` = 1) OR (`from_user_id` = {$toUserId}  AND `to_user_id` = {$fromUserId}  AND `is_receive` = 1) order by id desc limit 20");

        return array_reverse($list);
    }

    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection)
    {
//        $connection->send(json_encode(['type' => 'system', 'content' => "连接建立"]));
    }

    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection)
    {
        if (isset($connection->uid)) {
            echo '连接断开:' . $connection->uid;
            // 连接断开时删除映射
            unset($this->worker->uidConnections[$connection->uid]);
        }
    }

    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public function onError($connection, $code, $msg)
    {
        echo "error $code $msg\n";
    }

    /**
     * 每个进程启动
     * @param $worker
     */
    public function onWorkerStart($worker)
    {
        echo '启动';
    }

//
}