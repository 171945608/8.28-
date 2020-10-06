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

class Goods
{
    /**
     * 商品信息 获取
     * */
    public static function getGoods($goods_id)
    {
        $info = Db::name('goods')->where('id', $goods_id)->find();
        return $info;
    }

    /**
     * 参加限时团购
     * */
    public static function joinGroupbuy($goods_id, $groupbuy_id)
    {
        $goods = self::getGoods($goods_id);
        //添加团购次数
        Shop::incStoreGroupbuy($goods['store_id']);

        //删除旧有
        Groupbuy::delGroupbuyGoods([
            'goods_id' => $goods['id']
        ]);

        //新增数据
        $res = Groupbuy::addGroupbuyGoods([
            'groupbuy_id' => $groupbuy_id,
            'goods_id'    => $goods['id'],
            'store_id'    => $goods['store_id'],
        ]);

        //发布团购消息
        if ($res) {
            $user_ids = User::getMsgToIds();
            $data = [];
            $now = time();
            foreach ($user_ids as $user_id) {
                array_push($data, [
                    'user_id'  => $user_id,
                    'goods_id' => $goods['id'],
                    'is_read'  => 0,
                    'time'     => $now,
                ]);
            }
            Db::name('msg_groupbuy')->insertAll($data);
        }

        return $res;
    }

    /**
     * 团购商品列表
     * */
    public static function getGroupbuyGoodsList($store_id, $page, $limit)
    {
        $list = Groupbuy::getGroupbuyGoodsList($page, $limit, [
            'store_id' => $store_id
        ]);

        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $goods = self::getGoods($val['goods_id']);
                $list[$key]['goods_name'] = $goods['name'];

                $groupbuy = Groupbuy::getGroupbuy($val['groupbuy_id']);
                $list[$key]['groupbuy'] = sprintf("[ %s | %s 至 %s ]", $groupbuy['title'],
                    date('Y-m-d', $groupbuy['start_time']),
                    date('Y-m-d', $groupbuy['end_time'])
                );
            }
        }
        return $list;
    }


    /**
     * 商品列表
     * */
    public static function getGoodsList($page = 1, $limit = 999, $map = [], $field = true)
    {
        $list = Db::name('goods')
            ->where($map)
            ->page($page)
            ->limit($limit)
            ->field($field)
            ->order('id desc')
            ->select();
        return $list;
    }


    /**
     * 为列表添加字段
     *
     * */
    public static function addFieldForList($list)
    {
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $store = Shop::getStore($val['store_id']);
                $list[$key]['store'] = $store;
            }
        }

        return $list;
    }


    /**
     * 参加特价销售
     * */
    public static function joinDiscount($goods_id, $discount_cate_id, $discount_price)
    {
        $goods = Goods::getGoods($goods_id);
        Discount::deleteDiscountGoods([
            'goods_id' => $goods['id']
        ]);

        //新增数据
        $res = Discount::addDiscountGoods([
            'goods_id'         => $goods['id'],
            'store_id'         => $goods['store_id'],
            'discount_cate_id' => $discount_cate_id,
            'discount_price'   => $discount_price,
            'audit_status'     => 0,
            'createtime'       => time(),
        ]);
        return $res;
    }


    /**
     * 参加VIP销售
     * */
    public static function joinVip($goods_id, $vip_cate_id, $vip_price)
    {
        $goods = Goods::getGoods($goods_id);
        Vip::deleteGoods([
            'goods_id' => $goods['id']
        ]);

        //新增数据
        $res = Vip::addGoods([
            'goods_id'       => $goods['id'],
            'store_id'       => $goods['store_id'],
            'vip_cate_id'    => $vip_cate_id,
            'vip_price'      => $vip_price,
            'createtime'     => time(),
            'is_recommended' => 0,
        ]);
        return $res;
    }

    /**
     * 我的收藏 商品列表
     * */
    public static function getStarredGoodsIds($user_id)
    {
        $id_arr = Db::name('goods_collect')
            ->where('user_id', $user_id)
            ->column('goods_id');
        return $id_arr;
    }

    /**
     * 我的收藏 删除商品收藏
     * */
    public static function delGoodsStar($ids, $login_id)
    {
        $res = Db::name('goods_collect')
            ->where('goods_id', 'in', $ids)
            ->where('user_id', $login_id)
            ->delete();
        return $res;
    }
}