<?php
/**
 * Created by PhpStorm.
 * User: 18660
 * Date: 2020/6/21
 * Time: 20:21
 */

Class WebSocketServer
{
    //server参数
    const SERVER_ADDR = "0.0.0.0";
    const SERVER_PORT = 9600;

    //redis参数
    const REDIS_ADDR = "127.0.0.1";
    const REDIS_PORT = 6379;
    const REDIS_AUTH = 'phpts';
    const REDIS_DB   = 1;

    //mysql参数
    const MYSQL_ADDR = "127.0.0.1";
    const MYSQL_PORT = 3306;
    const MYSQL_USER = 'kuaicaibao';
    const MYSQL_PWD  = 'Zw8AHMPtXwErMCEf';
    const MYSQL_DB   = 'kuaicaibao';

    protected $server = null;

    public function __construct()
    {
        // 初始化server
        if (is_null($this->server)) {
            //$this->server = new Swoole\WebSocket\Server(self::SERVER_ADDR, self::SERVER_PORT, SWOOLE_BASE, SWOOLE_SOCK_TCP | SWOOLE_SSL);
            $this->server = new Swoole\WebSocket\Server(self::SERVER_ADDR, self::SERVER_PORT);
            $this->server->set([
                'reactor_num'              => 4,
                'worker_num'               => 4,
                'heartbeat_check_interval' => 60,
//                'ssl_key_file' => '/www/wwwroot/shop.kuaicaibao.cn/swoole/cert/abc.kuaicaibao.cn.key',
//                'ssl_cert_file' => '/www/wwwroot/shop.kuaicaibao.cn/swoole/cert/abc.kuaicaibao.cn.pem',
            ]);

            $this->server->on("open", [$this, 'open']);
            $this->server->on("message", [$this, 'message']);
            $this->server->on("close", [$this, 'close']);
        }
        $this->server->start();
    }

    //open回调
    public function open(Swoole\WebSocket\Server $server, $request)
    {
        echo "fd_{$request->fd} in\n";
    }

    //message回调
    public function message(Swoole\WebSocket\Server $server, $frame)
    {
        $data = json_decode($frame->data, true);
        switch (strtolower((string)$data['op'])) {
            case 'bind': //绑定
                $this->bind($frame->fd, $data['from_id']);
                echo "fd_{$frame->fd} bind to uid_{$data['from_id']}\n";
                break;

            case 'send': //发送
                if ($data['send_type'] == 1) { //私聊
                    $this->pushPrivateMsg($server, $data['to_id'], $frame->data);
                }

                if ($data['send_type'] == 2) { //群聊
                    $this->pushRoomMsg($server, $data['room_id'], $data['from_id'], $frame->data);
                }

                $this->save($data); //保存
                echo "uid_{$data['from_id']} send_type: send_type_{$data['send_type']}\n";
                break;
        }
    }

    //close回调
    public function close(Swoole\WebSocket\Server $server, $fd)
    {
        echo "fd_{$fd} out\n";
        $this->unbind($fd);
    }

    //双向绑定 可关闭旧的绑定fd
    public function bind($fd, $uid)
    {
        $redis = new Swoole\Coroutine\Redis(['compatibility_mode' => true]);
        $redis->connect(self::REDIS_ADDR, self::REDIS_PORT);
        $redis->auth(self::REDIS_AUTH);
        $redis->select(self::REDIS_DB);

        $redis->set("uid_{$uid}", $fd); //绑定fd到uid
        $redis->set("fd_{$fd}", $uid);  //绑定uid到fd
    }

    //解除绑定
    public function unbind($fd)
    {
        $redis = new Swoole\Coroutine\Redis(['compatibility_mode' => true]);
        $redis->connect(self::REDIS_ADDR, self::REDIS_PORT);
        $redis->auth(self::REDIS_AUTH);
        $redis->select(self::REDIS_DB);

        $uid = $redis->get("fd_{$fd}");
        $redis->del("fd_{$fd}"); //解除fd绑定uid

        if (!empty($uid)) { //解除uid绑定fd
            $nfd = $redis->get("uid_{$uid}");
            !empty($nfd) && $nfd == $fd && $redis->del("uid_{$uid}");
        }
    }

    //私聊发送
    public function pushPrivateMsg(Swoole\WebSocket\Server $server, $to_id, $data)
    {
        $redis = new Swoole\Coroutine\Redis(['compatibility_mode' => true]);
        $redis->connect(self::REDIS_ADDR, self::REDIS_PORT);
        $redis->auth(self::REDIS_AUTH);
        $redis->select(self::REDIS_DB);

        $to_fd = $redis->get("uid_{$to_id}");
        $established = $server->isEstablished($to_fd);
        echo "to_fd: {$to_fd} {$established}\n";
        $server->push($to_fd, $data);
    }

    //群聊发送
    public function pushRoomMsg(Swoole\WebSocket\Server $server, $room_id, $from_id, $data)
    {
        $db = new Swoole\Coroutine\MySQL();
        $db->connect([
            'host'     => self::MYSQL_ADDR,
            'port'     => self::MYSQL_PORT,
            'user'     => self::MYSQL_USER,
            'password' => self::MYSQL_PWD,
            'database' => self::MYSQL_DB,
        ]);

        $sql = sprintf('select user_id from fa_chat_room_user where room_id=%u', $room_id);
        $coll = $db->query($sql);
        $id_arr = [];
        if (!empty($coll)) {
            foreach ($coll as $key => $val) {
                if ($val['user_id'] != $from_id) {
                    array_push($id_arr, $val['user_id']);
                }
            }
        }

        $redis = new Swoole\Coroutine\Redis(['compatibility_mode' => true]);
        $redis->connect(self::REDIS_ADDR, self::REDIS_PORT);
        $redis->auth(self::REDIS_AUTH);
        $redis->select(self::REDIS_DB);

        foreach ($id_arr as $key => $val) {
            $to_fd = $redis->get("uid_{$val}");
            !empty($to_fd) && $server->push($to_fd, $data);
        }
    }


    //保存数据
    public function save($data)
    {
        $db = new Swoole\Coroutine\MySQL();
        $db->connect([
            'host'     => self::MYSQL_ADDR,
            'port'     => self::MYSQL_PORT,
            'user'     => self::MYSQL_USER,
            'password' => self::MYSQL_PWD,
            'database' => self::MYSQL_DB,
        ]);

        $sql = sprintf("insert into fa_chat_msg (from_id,send_time,send_type,msg_type,msg_cont,to_id,room_id) values(%u,%u,%u,%u,'%s',%u,%u)",
            $data['from_id'], time(), $data['send_type'], $data['msg_type'], $data['msg_cont'], $data['to_id'], $data['room_id']);
        $db->query($sql);

        $res = $db->query("SELECT LAST_INSERT_ID() as msg_id");
        $msg_id = $res[0]['msg_id'];

        $sql = sprintf("insert into fa_chat_msg_id (send_time,send_type,msg_id,user_id,ano_id) values(%u,%u,%u,%u,%u)",
            time(), $data['send_type'], $msg_id, $data['from_id'], $data['to_id']);
        $db->query($sql);

        $sql = sprintf("insert into fa_chat_msg_id (send_time,send_type,msg_id,user_id,ano_id) values(%u,%u,%u,%u,%u)",
            time(), $data['send_type'], $msg_id, $data['to_id'], $data['from_id']);
        $db->query($sql);
    }

}

//启动服务
new WebSocketServer();