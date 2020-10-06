<?php

namespace app\api\model;

use think\Db;
use think\Exception;
use think\Config;

/**
 * 搜索
 * */
class Search
{
    /**
     * 关键词 列表
     * */
    public static function getKeywordsList($page = 1, $limit = 999, $map = [], $order = 'id desc')
    {
        $list = Db::name('search_keywords')
            ->where($map)
            ->page($page)
            ->limit($limit)
            ->order($order)
            ->select();
        return $list;
    }

    /**
     * 热搜 列表
     * */
    public static function getSearchListSortedByTimes($page = 1, $limit = 999, $map = [])
    {
        $list = Db::name('search')
            ->where($map)
            ->page($page)
            ->limit($limit)
            ->order('times desc')
            ->select();
        return $list;
    }

    /**
     * 新增搜索
     * */
    public static function addSearch($data)
    {
        $res = Db::name('search')->insert($data);
        return $res;
    }

    /**
     * 获取搜索
     * */
    public static function getSearch($map)
    {
        $info = Db::name('search')->where($map)->find();
        return $info;
    }

    /**
     * 增加搜索次数
     * */
    public static function incSearchTimes($map)
    {
        $res = Db::name('search')->where($map)->setInc('times', 1);
        return $res;
    }

    /**
     * 搜索记录 新增
     * */
    public static function addSearchLog($word, $group)
    {
        if (!$word) {
            return false;
        }
        $info = self::getSearch([
            'group' => $group,
            'word' => $word,
        ]);

        if (empty($info)) {
            self::addSearch([
                'word' => $word,
                'group' => $group,
                'times' => 1,
                'createtime' => time(),
            ]);
        } else {
            self::incSearchTimes([
                'id' => $info['id']
            ]);
        }
    }

    /**
     * 商品搜索
     * */
    public static function searchGoods($page = 1, $limit = 999, $word = '')
    {
        //添加搜索记录
        self::addSearchLog($word, 'goods');

        $list = Goods::getGoodsList($page, $limit, [
            'name' => ['like', "%{$word}%"],
            'audit' => Mine::GOODS_AUDIT_PASS,
            'state' => Mine::GOODS_STATE_UP,
        ]);

        $list = Goods::addFieldForList($list);
        return $list;
    }

    /**
     * 供应商搜索
     * */
    public static function searchStore($page = 1, $limit = 999, $word = '')
    {
        //添加搜索记录
        self::addSearchLog($word, 'store');

        $list = Shop::getStoreList($page, $limit, [
            'name' => ['like', "%{$word}%"],
        ]);

        $list = Shop::addFieldForStroeList($list);
        return $list;
    }

    /**
     * 限时团购搜索
     * */
    public static function searchGroupbuy($page = 1, $limit = 999, $word = '')
    {
        //添加搜索记录
        self::addSearchLog($word, 'groupbuy');

        $now = time();
        $rows = Db::name('groupbuy')
            ->where('status', 1)
            ->where('end_time', '>', $now)
            ->where('title', 'like', "%{$word}%")
            ->page($page)
            ->limit($limit)
            ->order('id desc')
            ->select();

        if ($rows) {
            foreach ($rows as $key => $val) {
                $rows[$key]['countdown'] = Groupbuy::getCountdown($val['end_time']);
                $rows[$key]['apply'] = Groupbuy::getApplyCount($val['id']);
            }
        }

        return $rows;
    }


    /**
     * 招标竞价搜索
     * */
    public static function searchPurchase($page = 1, $limit = 999, $word = '')
    {
        //添加搜索记录
        self::addSearchLog($word, 'purchase');

        $list = Db::name('purchase')
            ->where('title', 'like', "%{$word}%")
            ->page($page)
            ->limit($limit)
            ->order('id desc')
            ->select();

        foreach ($list as $key => $val) {
            $user = User::getUser('id', $val['user_id']);
            $list[$key]['user'] = [
                'id'       => $user['id'],
                'avatar'   => $user['avatar'],
                'nickname' => $user['nickname'],
            ];
        }
        return $list;
    }

    /**
     * 特价商品搜索
     * */
    public static function searchDiscount($page = 1, $limit = 999, $word = '')
    {
        //添加搜索记录
        self::addSearchLog($word, 'discount');

        $list = Db::name('goods')->alias('g')
            ->join('discount_goods dg', 'dg.goods_id=g.id')
            ->where('dg.audit_status', 1)
            ->where('g.name', 'like', "%{$word}%")
            ->page($page)
            ->limit($limit)
            ->field('g.id,g.name,g.image,g.oprice,g.new,g.hot,g.special,g.store_id,dg.discount_price')
            ->order('g.id desc')
            ->select();

        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $list[$key]['store'] = Shop::getStore($val['store_id']);
            }
        }

        return $list;
    }

    /**
     * 工友联盟搜索
     * */
    public static function searchUnion($page = 1, $limit = 999, $word = '')
    {
        //添加搜索记录
        self::addSearchLog($word, 'union');

        $list = Db::name('work_supply')
            ->where("content", 'like', "%{$word}%")
            ->page($page)
            ->limit($limit)
            ->order('id desc')
            ->select();

        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $user = User::getUser('id', $val['user_id']);
                $list[$key]['user'] = [
                    'id' => $user['id'],
                    'nickname' => $user['nickname'],
                    'avatar' => $user['avatar'],
                ];
            }
        }
        return $list;
    }

    /**
     * 物流公司搜索
     * */
    public static function searchExpress($page = 1, $limit = 999, $word = '')
    {
        //添加搜索记录
        self::addSearchLog($word, 'express');

        $list = Db::name('express')
            ->where("company", 'like', "%{$word}%")
            ->order('id desc')
            ->page($page)
            ->limit($limit)
            ->select();
        return $list;
    }


    /**
     * vip搜索
     * */
    public static function searchVip($page = 1, $limit = 999, $word = '')
    {
        //添加搜索记录
        self::addSearchLog($word, 'vip');

        $list = Db::name('goods')->alias('g')
            ->join('vip_goods vg', 'vg.goods_id=g.id')
            ->where('g.name', 'like', "%{$word}%")
            ->page($page)
            ->limit($limit)
            ->field('g.id,g.name,g.image,g.oprice,g.new,g.hot,g.special,g.store_id,vg.vip_price')
            ->order('g.id desc')
            ->select();

        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $list[$key]['store'] = Shop::getStore($val['store_id']);
            }
        }

        return $list;
    }


    /**
     * 话题搜索
     * */
    public static function searchTopic($page = 1, $limit = 999, $word = '', $login_id)
    {
        //添加搜索记录
        self::addSearchLog($word, 'topic');

        $list = Community::getTopicList($page, $limit, [
            'content' => ['like', "%{$word}%"]
        ]);

        $list = Community::formatTopicList($list, empty($login_id) ? 0 : $login_id);
        return $list;
    }

}