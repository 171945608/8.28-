<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */

//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;

require_once "Connection.php";
/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    const MYSQL_ADDR = "127.0.0.1";
    const MYSQL_PORT = 3306;
    const MYSQL_USER = 'kuaicaibao';
    const MYSQL_PWD = 'Zw8AHMPtXwErMCEf';
    const MYSQL_DB = 'kuaicaibao';

    public static function onWorkerStart()
    {
        global $db;
        $db = new \Workerman\MySQL\Connection(
            self::MYSQL_ADDR,
            self::MYSQL_PORT,
            self::MYSQL_USER,
            self::MYSQL_PWD,
            self::MYSQL_DB
        );
    }

    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     *
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    {
        echo "client_{$client_id} in" . PHP_EOL;
    }

    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $message 具体消息
     */
    public static function onMessage($client_id, $message)
    {
        $data = json_decode($message, true);
        switch (strtolower((string)$data['op'])) {
            case 'bind': //绑定
                Gateway::bindUid($client_id, $data['from_id']);
                echo "client_{$client_id} bind to uid_{$data['from_id']}\n";
                break;

            case 'send': //发送
                if ($data['send_type'] == 1) { //私聊
                    Gateway::isUidOnline($data['to_id']) && Gateway::sendToUid($data['to_id'], $message);
                }

                if ($data['send_type'] == 2) { //群聊
                    self::pushRoomMsg($data['room_id'], $data['from_id'], $message);
                }

                self::save($data); //保存
                echo "uid_{$data['from_id']} send_type: send_type_{$data['send_type']}\n";
                break;
        }
    }

    /**
     * 当用户断开连接时触发
     * @param int $client_id 连接id
     */
    public static function onClose($client_id)
    {
        echo "client_{$client_id} out" . PHP_EOL;
    }

    //群聊发送
    public static function pushRoomMsg($room_id, $from_id, $data)
    {
        global $db;
        $sql = sprintf('select user_id from fa_chat_room_user where room_id=%u', $room_id);
        $coll = $db->query($sql);
        if (!empty($coll)) {
            foreach ($coll as $key => $val) {
                if ($val['user_id'] != $from_id) {
                    Gateway::sendToUid($val['user_id'], $data);
                }
            }
        }
    }

    //保存数据
    public static function save($data)
    {
        global $db;
        $sql = sprintf("insert into fa_chat_msg (from_id,send_time,send_type,msg_type,msg_cont,to_id,room_id) values(%u,%u,%u,%u,'%s',%u,%u)",
            $data['from_id'], time(), $data['send_type'], $data['msg_type'], $data['msg_cont'], $data['to_id'], $data['room_id']);
        $db->query($sql);
    }
}
