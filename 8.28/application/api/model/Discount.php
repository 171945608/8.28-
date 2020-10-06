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

class Discount
{
    /**
     * 特价分类 全部列表
     * */
    public static function getCateList($page = 1, $limit = 999, $map = [], $order = 'weigh asc')
    {
        $list = Db::name('discount_cate')
            ->where($map)
            ->page($page)
            ->limit($limit)
            ->order($order)
            ->select();
        return $list;
    }

    /**
     * 特价分类 获取
     * */
    public static function getDiscountCate($id)
    {
        $info = Db::name('discount_cate')->where('id', $id)->find();
        return $info;
    }

    /**
     * 特价商品 获取
     * */
    public static function getDiscountGoods($map)
    {
        $info = Db::name('discount_goods')->where($map)->find();
        return $info;
    }

    /**
     * 特价商品 新增
     * */
    public static function addDiscountGoods($data)
    {
        $res = Db::name('discount_goods')->insert($data);
        return $res;
    }

    /**
     * 特价商品 修改
     * */
    public static function editDiscountGoods($map, $data)
    {
        $res = Db::name('discount_goods')->where($map)->update($data);
        return $res;
    }

    /**
     * 特价商品 删除
     * */
    public static function deleteDiscountGoods($map)
    {
        $res = Db::name('discount_goods')->where($map)->delete();
        return $res;
    }

    /**
     * 特价商品 列表
     * */
    public static function getGoodsList($page = 1, $limit = 999, $map = [], $order = 'createtime desc')
    {
        $list = Db::name('discount_goods')
            ->where($map)
            ->where('audit_status', 1)
            ->page($page)
            ->limit($limit)
            ->order($order)
            ->select();

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
                $list[$key]['store'] = Shop::getStore($goods['store_id']);
            }
            array_values($list);
        }

        return $list;
    }

    /**
     * 商品详情
     * */
    public static function getGoods($goods_id)
    {
        $discount_goods = self::getDiscountGoods([
            'goods_id' => $goods_id
        ]);
        if (empty($discount_goods) || $discount_goods['audit_status'] != 1) {
            return [];
        }

        $goods = Goods::getGoods($goods_id);
        $goods['store'] = Shop::getStore($goods['store_id']);
        $goods['discount_price'] = $discount_goods['discount_price'];

        return $goods;
    }

    /**
     * 特价商品 列表 全部
     * */
    public static function getAllGoodsList($page = 1, $limit = 999, $map = [], $order = 'createtime desc')
    {
        //echo 111;exit;
        $list = Db::name('discount_goods')
            ->where($map)
            ->page($page)
            ->limit($limit)
            ->order($order)
            ->select();
        //halt($list);

        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $goods = Goods::getGoods($val['goods_id']);
                $list[$key]['id'] = $goods['id'];
                $list[$key]['name'] = $goods['name'];
                $list[$key]['image'] = $goods['image'];
                $list[$key]['oprice'] = $goods['oprice'];

                $list[$key]['audit_str'] = $val['audit_status'] == 1 ? "已审核" : "未审核";

                $discount_cate = self::getDiscountCate($val['discount_cate_id']);
                $list[$key]['discount_cate'] =$discount_cate['name'];
            }
        }

        return $list;
    }


    /**
     * 店铺特价商品 计数
     * */
    public static function getAllGoodsCount($map = [])
    {
        $count = Db::name('discount_goods')
            ->where($map)
            ->count();
        return $count;
    }

}