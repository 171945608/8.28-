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

class WorkSupply
{
    /**
     * 新增供活
     * */
    public static function addWorkSupply($user_id, $begin_time, $end_time, $content, $address, $linkman, $linkman_sex, $link_phone)
    {
        $data = [
            'begin_time'  => strtotime($begin_time),
            'end_time'    => strtotime($end_time),
            'content'     => $content,
            'address'     => $address,
            'linkman'     => $linkman,
            'linkman_sex' => $linkman_sex,
            'link_phone'  => $link_phone,
            'createtime'  => time(),
            'user_id'     => $user_id,
        ];
        $res = Db::name('work_supply')->insert($data);
        return $res;
    }

    /**
     * 供活列表
     * */
    public static function getWorkSupplyList($page, $limit, $where)
    {
        $list = Db::name('work_supply')
            ->where($where)
            ->page($page)
            ->limit($limit)
            ->order('id desc')
            ->select();
        return $list;
    }

    public static function formatWorkSupplyList($list)
    {
        $now = time();
        foreach ($list as $key => $val) {
            $is_old = $val['end_time'] < $now;
            $list[$key]['is_old'] = $is_old;
        }
        return $list;
    }


    /**
     * 供活详情
     * */
    public static function getWorkSupply($id)
    {
        $info = Db::name('work_supply')->where('id', $id)->find();
        return $info;
    }

    /**
     * 变更信息
     * */
    public static function setWorkSupply($id, $data)
    {
        /*$delivery = self::getWorkSupply($id);
        if ($delivery['end_time'] < time()) {
            return false;
        }*/
        $res = Db::name('work_supply')->where('id', $id)->update($data);
        return $res;
    }

    /**
     * 删除信息
     * */
    public static function deleteWorkSupply($id)
    {
        $res = Db::name('work_supply')->where('id', $id)->delete();
        return $res;
    }



}