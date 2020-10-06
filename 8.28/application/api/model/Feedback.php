<?php
/**
 * @author 见龙在野
 * @date 2020-08-08
 */

namespace app\api\model;

use think\Db;

class Feedback
{
    //新增
    public static function addRow($data)
    {
        $res = Db::name('feedback')->insert($data);
        return $res;
    }

    //查看
    public static function getRow($map)
    {
        $row = Db::name('feedback')->where($map)->find();
        return $row;
    }

    //编辑
    public static function setRow($map, $data)
    {
        $res = Db::name('feedback')->where($map)->update($data);
        return $res;
    }

    //删除
    public static function delRows($map)
    {
        $res = Db::name('feedback')->where($map)->delete();
        return $res;
    }

    //列表
    public static function getRows($map = [], $page = 1, $limit = 999, $order = 'id desc', $field = true, $except = false)
    {
        $rows = Db::name('feedback')
            ->where($map)
            ->page($page)
            ->limit($limit)
            ->order($order)
            ->field($field, $except)
            ->select();
        return $rows;
    }

}