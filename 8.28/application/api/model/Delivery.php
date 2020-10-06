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

class Delivery
{
    /**
     * 新增发货
     * */
    public static function addDelivery($user_id, $delivery_time, $from_address, $to_address, $delivery_man, $delivery_man_sex, $link_phone, $remark)
    {
        $data = [
            'delivery_time'    => strtotime($delivery_time),
            'from_address'     => $from_address,
            'to_address'       => $to_address,
            'delivery_man'     => $delivery_man,
            'delivery_man_sex' => $delivery_man_sex,
            'link_phone'       => $link_phone,
            'remark'           => $remark,
            'createtime'       => time(),
            'user_id'          => $user_id,
        ];
        $res = Db::name('delivery')->insert($data);
        return $res;
    }

    /**
     * 发货列表
     * */
    public static function getDeliveryList($page, $limit, $where)
    {
        $list = Db::name('delivery')
            ->where($where)
            ->page($page)
            ->limit($limit)
            ->order('id desc')
            ->select();
        return $list;
    }

    public static function formatDeliveryList($list)
    {
        $now = time();
        foreach ($list as $key => $val) {
            $is_old = $val['delivery_time'] < $now;
            $list[$key]['is_old'] = $is_old;
        }
        return $list;
    }

    /**
     * 发货详情
     * */
    public static function getDelivery($delivery_id)
    {
        $info = Db::name('delivery')->where('id', $delivery_id)->find();
        return $info;
    }

    /**
     * 变更信息
     * */
    public static function setDelivery($delivery_id, $data)
    {
       /* $delivery = self::getDelivery($delivery_id);
        if ($delivery['delivery_time'] < time()) {
            return false;
        }*/
        $res = Db::name('delivery')->where('id', $delivery_id)->update($data);
        return $res;
    }

    /**
     * 删除信息
     * */
    public static function deleteDelivery($delivery_id)
    {
        $res = Db::name('delivery')->where('id', $delivery_id)->delete();
        return $res;
    }


}