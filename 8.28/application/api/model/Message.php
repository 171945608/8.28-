<?php
/**
 * Created by PhpStorm.
 * User: 18660
 * Date: 2020/6/21
 * Time: 6:37
 */

namespace app\api\model;

use think\Db;
use think\Exception;
use think\Config;

class Message
{
    /**
     * 私聊列表
     * */
    public static function getChatUserList($user_id, $page, $limit)
    {
        $list = Db::name('chat_msg_id')
            ->where('user_id', $user_id)
            ->group('ano_id')
            ->field('ano_id,max(msg_id) as msg_id')
            ->order('msg_id desc')
            ->page($page)
            ->limit($limit)
            ->select();
//        halt($list);

        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $user = User::getUser('id', $val['ano_id']);
                if (!empty($user)) {
                    $list[$key]['user'] = [
                        'id'       => $user['id'],
                        'avatar'   => $user['avatar'],
                        'nickname' => $user['nickname'],
                    ];
                    $last = self::getChatMsg($val['msg_id']);
                    $list[$key]['msg'] = [
                        'id'       => $last['id'],
                        'msg_type' => $last['msg_type'],
                        'msg_cont' => $last['msg_cont'],
                    ];
                }
            }
        }
        return $list;
    }

    public static function getChatMsg($id)
    {
        $info = Db::name('chat_msg')
            ->where('id', $id)
            ->find();
        return $info;
    }

    /**
     * 私聊及记录
     * */
    public static function getChatList($user_id, $ano_id, $page, $limit)
    {
        $list = Db::name('chat_msg_id')
            ->where('user_id', $user_id)
            ->where('ano_id', $ano_id)
            ->order('id desc')
            ->page($page)
            ->limit($limit)
            ->select();

        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $msg = self::getChatMsg($val['msg_id']);
                $from = User::getUser('id', $msg['from_id']);
                $list[$key]['msg_type'] = $msg['msg_type'];
                $list[$key]['msg_cont'] = $msg['msg_cont'];
                $list[$key]['from_id'] = $from['id'];
                $list[$key]['from_avatar'] = $from['avatar'];
            }
        }
        return $list;
    }


}