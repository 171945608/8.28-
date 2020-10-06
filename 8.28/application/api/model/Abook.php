<?php
/**
 * @author 见龙在野
 * @date 2020-08-08
 */

namespace app\api\model;

use think\Db;

class Abook
{
    /**
     * 用户级接口
     * */

    //新增
    public static function addRow($title, $content, $uid)
    {
        $data = [
            'title' => $title,
            'content' => $content,
            'time' => time(),
            'user_id' => $uid,
        ];
        $res = Db::name('account_book')->insert($data);
        return $res;
    }

    //查看
    public static function getRow($id, $uid)
    {
        $row = Db::name('account_book')->where('id', $id)->where('user_id', $uid)->find();
        return $row;
    }

    //编辑
    public static function setRow($data, $id, $uid)
    {
        $res = Db::name('account_book')->where('id', $id)->where('user_id', $uid)->update($data);
        return $res;
    }

    //删除
    public static function delRow($id, $uid)
    {
        $res = Db::name('account_book')->where('id', $id)->where('user_id', $uid)->delete();
        return $res;
    }

    //删除
    public static function delRows($ids, $uid)
    {
        $res = Db::name('account_book')->where('id', 'in', $ids)->where('user_id', $uid)->delete();
        return $res;
    }

    /**
     * 用户级 平台级
     * */
    //列表
    public static function getRows($map = [], $page = 1, $limit =999, $order = 'id desc')
    {
        $rows = Db::name('account_book')
            ->where($map)
            ->page($page)
            ->limit($limit)
            ->order($order)
            ->field('content', true)
            ->select();
        return $rows;
    }

}