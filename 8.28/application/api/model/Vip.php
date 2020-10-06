<?php

namespace app\api\model;

use think\Db;
use think\Exception;
use think\Config;

/**
 * VIP
 * */
class Vip
{
    /**
     * VIP分类 列表
     * */
    public static function getCateList($page = 1, $limit = 999, $map = [], $order = 'weigh asc')
    {
        $list = Db::name('vip_cate')
            ->where($map)
            ->page($page)
            ->limit($limit)
            ->order($order)
            ->select();
        return $list;
    }

    /**
     * VIP分类 获取
     * */
    public static function getCate($id)
    {
        $res = Db::name('vip_cate')->where('id', $id)->find();
        return $res;
    }

    /**
     * VIP商品 删除
     * */
    public static function deleteGoods($map)
    {
        $res = Db::name('vip_goods')->where($map)->delete();
        return $res;
    }

    /**
     * VIP商品 新增
     * */
    public static function addGoods($data)
    {
        $res = Db::name('vip_goods')->insert($data);
        return $res;
    }


    /**
     * VIP商品 列表 无条件
     * */
    public static function getGoodsList($page = 1, $limit = 999, $map = [], $order = 'createtime desc')
    {
        $list = Db::name('vip_goods')
            ->where($map)
            ->page($page)
            ->limit($limit)
            ->order($order)
            ->select();
        return $list;
    }

    /**
     * VIP商品 列表 增加字段
     * */
    public static function addFieldForList($list)
    {
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $goods = Goods::getGoods($val['goods_id']);
                if ($goods['audit'] != 2 || $goods['state'] != 1) {
                    unset($list[$key]);
                    continue;
                }
                $list[$key]['id'] = $goods['id'];
                $list[$key]['name'] = $goods['name'];
                $list[$key]['image'] = $goods['image'];
                $list[$key]['oprice'] = $goods['oprice'];
                $list[$key]['new'] = $goods['new'];
                $list[$key]['hot'] = $goods['hot'];
                $list[$key]['special'] = $goods['special'];

                $cate = self::getCate($val['vip_cate_id']);
                $list[$key]['vip_cate'] =$cate['name'];

                $list[$key]['store'] = Shop::getStore($goods['store_id']);
            }
            array_values($list);
        }
        return $list;
    }

    /**
     * VIP商品 计数 无条件
     * */
    public static function getGoodsCount($map = [])
    {
        $count = Db::name('vip_goods')
            ->where($map)
            ->count();
        return $count;
    }

    /**
     * VIP商品 详情
     * */
    public static function getGoods($goods_id)
    {
        $info = Db::name('vip_goods')->where('goods_id', $goods_id)->find();
        return $info;
    }

    /**
     * VIP商品 详情 增加字段
     * */
    public static function addFieldForInfo($info)
    {
        $goods = Goods::getGoods($info['goods_id']);
        if (!empty($info)) {
            $goods['store'] = Shop::getStore($goods['store_id']);
            $goods['vip_price'] = $info['vip_price'];
        }
        return $goods;
    }


}