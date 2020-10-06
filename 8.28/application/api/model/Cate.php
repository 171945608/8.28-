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

class Cate
{

    /**
     * 分类商品 默认
     * */
    public static function getCateGoodsList()
    {
        $list = Db::name('goods')
            ->where('audit', Mine::GOODS_AUDIT_PASS)
            ->where('state', Mine::GOODS_STATE_UP)
            ->where('cate', 1)
            ->limit(9)
            ->order('id desc')
            ->field('id,image,name')
            ->select();
        return $list;
    }

    /**
     * 分类店铺 默认
     * */
    public static function getCateStoreList()
    {
        $list = Db::name('store')
            ->alias('store')
            ->join('fa_shop shop', 'shop.id=store.shop_id')
            ->where('shop.audit_state', Mine::AUDIT_STATE_SUCCESS)
            ->where('shop.forbid_type', Mine::FORBID_NONE)
            ->where('store.cate', 1)
            ->limit(9)
            ->order('id desc')
            ->field('store.id,store.image,store.name')
            ->select();
        return $list;
    }

    /**
     * 分类列表
     * */
    public static function getCateList()
    {
        $list = Db::name('goods_cate_system')
            ->order('weigh asc')
            ->select();
        return $list;
    }

    public static function getCateListByMap($map)
    {
        $list = Db::name('goods_cate_system')
            ->where($map)
            ->order('weigh asc')
            ->select();
        return $list;
    }

    public static function getCateCountByMap($map)
    {
        return Db::name('goods_cate_system')
            ->where($map)
            ->count();
    }

    public static function getCateName($id)
    {
        return Db::name('goods_cate_system')->where('id', $id)->value('name');
    }

    public static function getDefaultCateId()
    {
        return Db::name('goods_cate_system')->order('weigh asc')->value('id');
    }

    /**
     * 分类商品 列表
     * */
    public static function getGoodsList($cate_id, $page, $limit, $sort)
    {
        switch ((int)$sort) {
            case 11:
                $order = 'g.price asc';
                break;
            case 12:
                $order = 'g.price desc';
                break;
            case 21:
                $order = 'g.delivery asc';
                break;
            case 22:
                $order = 'g.delivery desc';
                break;

            case 31:
                $order = 's.supply asc';
                break;
            case 32:
                $order = 's.supply desc';
                break;

            default:
                $order = 'g.id desc';
        }

        $list = Db::name('goods')->alias('g')
            ->join('fa_store s', 's.id = g.store_id')
            ->where('g.cate_id', $cate_id)
            ->where('g.audit', Mine::GOODS_AUDIT_PASS)
            ->where('g.state', Mine::GOODS_STATE_UP)
            ->where('g.cate_id', 'in', $cate_id)
            ->page($page)
            ->limit($limit)
            ->order($order)
            ->field('g.id,g.name,g.image,g.price,g.new,g.hot,g.special,g.discount,g.delivery,g.store_id,s.supply')
            ->select();
        return $list;
    }

    /**
     * 分类商品 列表 格式化 增加店铺数据
     * */
    public static function formatGoodsListOne($list)
    {
        foreach ($list as $key => $val) {
            $list[$key]['store'] = Mine::getStore('id', $val['store_id']);
        }
        return $list;
    }

    /**
     * 分类商品 详情
     * */
    public static function getGoods($id)
    {
        $info = Db::name('goods')
            ->where('audit', Mine::GOODS_AUDIT_PASS)
            ->where('state', Mine::GOODS_STATE_UP)
            ->where('id', $id)
            ->field(true)
            ->find();
        if (!empty($info)) {
            $info['store'] = Mine::getStore('id', $info['store_id']);
        }
        return $info;
    }

    /**
     * 分类商品 详情 服务
     * */
    public static function getGoodsEquities($goods_id)
    {
        $val = Db::name('goods')
            ->where('audit', Mine::GOODS_AUDIT_PASS)
            ->where('state', Mine::GOODS_STATE_UP)
            ->where('id', $goods_id)
            ->value('equities');
        return $val;
    }

    /**
     * 分类商品 详情 收藏
     * */
    public static function getGoodsCollect($user_id, $goods_id)
    {
        $count = Db::name('goods_collect')
            ->where('user_id', $user_id)
            ->where('goods_id', $goods_id)
            ->count();
        return $count > 0;
    }

    /**
     * 分类商品 收藏
     * */
    public static function collectGoods($user_id, $goods_id)
    {
        if (self::getGoodsCollect($user_id, $goods_id)) {
            Db::name('goods_collect')
                ->where('user_id', $user_id)
                ->where('goods_id', $goods_id)
                ->delete();
        } else {
            Db::name('goods_collect')->insert([
                    'user_id' => $user_id,
                    'goods_id' => $goods_id,
                ]);
        }
    }

    /**
     * 分类店铺
     * */
    public static function getStore($id)
    {
        $info = Mine::getStore('id', $id);
        return $info;
    }

    /**
     * 分类店铺 首页
     * */
    public static function getStoreHomeGoodsList($id)
    {
        $list = Db::name('goods')
            ->where('audit', Mine::GOODS_AUDIT_PASS)
            ->where('state', Mine::GOODS_STATE_UP)
            ->where('store_id', $id)
            ->where('store_home', 1)
            ->limit(10)
            ->order('id desc')
            ->field('detail,equities', true)
            ->select();
        return $list;
    }


    /**
     * 分类店铺 全部
     * */
    public static function getStoreAllGoodsList($id, $page, $limit)
    {
        $list = Db::name('goods')
            ->where('audit', Mine::GOODS_AUDIT_PASS)
            ->where('state', Mine::GOODS_STATE_UP)
            ->where('store_id', 'in', $id)
            ->page($page)
            ->limit($limit)
            ->order('id desc')
            ->field('detail,equities', true)
            ->select();
        return $list;
    }

    /**
     * 分类店铺 搜索
     * */
    public static function searchStoreGoods($id, $page, $limit, $word)
    {
        $list = Db::name('goods')
            ->where('audit', Mine::GOODS_AUDIT_PASS)
            ->where('state', Mine::GOODS_STATE_UP)
            ->where('store_id', 'in', $id)
            ->where('name', 'like', "%{$word}%")
            ->page($page)
            ->limit($limit)
            ->order('id desc')
            ->field('detail,equities', true)
            ->select();
        return $list;
    }

    /**
     * 分类店铺 上新
     * */
    public static function getStoreNewGoodsList($id)
    {
        $list = Db::name('goods')
            ->where('audit', Mine::GOODS_AUDIT_PASS)
            ->where('state', Mine::GOODS_STATE_UP)
            ->where('store_id', $id)
            ->where('new', 1)
            ->limit(10)
            ->order('id desc')
            ->field('detail,equities', true)
            ->select();
        return $list;
    }


    /**
     * 分类店铺 列表
     * */
    public static function getStoreList($cate_id, $page, $limit, $sort)
    {
        switch ((int)$sort) {
            case 11:
                $order = 's.groupbuy asc';
                break;
            case 12:
                $order = 's.groupbuy desc';
                break;
            case 21:
                $order = 's.supply asc';
                break;
            case 22:
                $order = 's.supply desc';
                break;
            case 31:
                $order = 'g.delivery asc';
                break;
            case 32:
                $order = 'g.delivery desc';
                break;

            default:
                $order = 'g.id desc';
        }

        $coll = Db::name('goods')->alias('g')
            ->join('fa_store s', 's.id = g.store_id')
            ->where('g.cate_id', $cate_id)
            ->where('g.audit', Mine::GOODS_AUDIT_PASS)
            ->where('g.state', Mine::GOODS_STATE_UP)
            ->where('s.logo', '<>', '')
            ->where('s.name', '<>', '')
            ->where('s.image', '<>', '')
            ->group('g.store_id')
            ->page($page)
            ->limit($limit)
            ->order($order)
            ->field('g.id,g.name,g.image,g.price,g.new,g.hot,g.special,g.discount,g.delivery,g.store_id,s.groupbuy,s.supply,g.delivery')
            ->select();
        //halt($coll);

        if (!empty($coll)) {
            foreach ($coll as $key => $val) {
                $store = Shop::getStore($val['store_id']);
                $coll[$key]['store'] = $store;
            }
        }

        return $coll;
    }

}