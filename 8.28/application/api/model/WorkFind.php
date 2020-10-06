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

class WorkFind
{
    /**
     * 新增
     * */
    public static function addWorkFind($user_id, $begin_time, $content, $linkman, $linkman_sex, $link_phone)
    {
        $data = [
            'begin_time'  => strtotime($begin_time),
            'content'     => $content,
            'linkman'     => $linkman,
            'linkman_sex' => $linkman_sex,
            'link_phone'  => $link_phone,
            'createtime'  => time(),
            'user_id'     => $user_id,
        ];
        $res = Db::name('work_find')->insert($data);
        return $res;
    }

    /**
     * 列表
     * */
    public static function getWorkFindList($page, $limit, $where)
    {
        $list = Db::name('work_find')
            ->where($where)
            ->page($page)
            ->limit($limit)
            ->order('id desc')
            //->fetchSql(true)
            ->select();
        //halt($list);
        return $list;
    }

    public static function formatWorkFindList($list)
    {
        $now = time();
        foreach ($list as $key => $val) {
            $is_old = $val['begin_time'] < $now;
            $list[$key]['is_old'] = $is_old;
        }
        return $list;
    }

    /**
     * 详情
     * */
    public static function getWorkFind($id)
    {
        $info = Db::name('work_find')->where('id', $id)->find();
        return $info;
    }

    /**
     * 变更
     * */
    public static function setWorkFind($id, $data)
    {
       /* $delivery = self::getWorkFind($id);
        if ($delivery['begin_time'] < time()) {
            return false;
        }*/
        $res = Db::name('work_find')->where('id', $id)->update($data);
        return $res;
    }

    /**
     * 删除
     * */
    public static function deleteWorkFind($id)
    {
        $res = Db::name('work_find')->where('id', $id)->delete();
        return $res;
    }


}