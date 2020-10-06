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

class Groupbuy
{

    /**
     * 限时团购 列表
     * */
    public static function getGroupbuyList($page = 1, $limit = 999, $where = [])
    {
        $now = time();
        $list = Db::name('groupbuy')
            ->where($where)
            ->where('status', 1)
            ->where('end_time', '>', $now)
            ->page($page)
            ->limit($limit)
            ->order('id desc')
            ->select();
        return $list;
    }

    /**
     * 限时团购 信息
     * */
    public static function getGroupbuy($groupbuy_id)
    {
        $info = Db::name('groupbuy')->where('id', $groupbuy_id)->find();
        return $info;
    }

    /**
     * 限时团购 信息 通过商品ID获取
     * */
    public static function getGroupbuyByGoodsId($goods_id)
    {
        $info = Db::name('groupbuy')->alias('gb')
            ->join('fa_groupbuy_goods gbg', 'gbg.groupbuy_id = gb.id')
            ->where('gbg.goods_id', $goods_id)
            ->find();
        return $info;
    }

    /**
     * 限时团购 设置
     * */
    public static function setGroupbuy($groupbuy_id, $data)
    {
        $res = Db::name('groupbuy')->where('id', $groupbuy_id)->update($data);
        return $res;
    }

    /**
     * 团购商品 获取
     * */
    public static function getGroupbuyGoods($map)
    {
        $res = Db::name('groupbuy_goods')->where($map)->find();
        return $res;
    }

    /**
     * 团购商品 删除
     * */
    public static function delGroupbuyGoods($map)
    {
        $res = Db::name('groupbuy_goods')->where($map)->delete();
        return $res;
    }

    /**
     * 团购商品 新增
     * */
    public static function addGroupbuyGoods($data)
    {
        $res = Db::name('groupbuy_goods')->insert($data);
        return $res;
    }

    /**
     * 团购商品 列表
     * */
    public static function getGroupbuyGoodsList($page = 1, $limit = 999, $map = [], $order = 'goods_id desc')
    {
        $list = Db::name('groupbuy_goods')
            ->where($map)
            ->page($page)
            ->limit($limit)
            ->order($order)
            ->select();
        return $list;
    }

    /**
     * 团购商品列表 计数
     * */
    public static function getGroupbuyGoodsCount($map = [])
    {
        $count = Db::name('groupbuy_goods')
            ->where($map)
            ->count();
        return $count;
    }

    /**
     * 进行中团购 商品列表
     * */
    public static function getGoingGoodsList($page, $limit)
    {
        $data = [];
        $now = time();
        $groupbuy_id_arr = Db::name('groupbuy')
            ->where('status', 1)
            ->where('end_time', '>', $now)
            ->column('id');

        if (empty($groupbuy_id_arr)) {
            return $data;
        }

        $goods_id_array = Db::name('groupbuy_goods')
            ->where('groupbuy_id', 'in', $groupbuy_id_arr)
            ->column('goods_id');

        if (empty($goods_id_array)) {
            return $data;
        }

        $list = Goods::getGoodsList($page, $limit, [
            'id'    => ['in', $goods_id_array],
            'audit' => Mine::GOODS_AUDIT_PASS,
            'state' => Mine::GOODS_STATE_UP,
        ], 'id,name,image');

        if (empty($list)) {
            return $data;
        }

        foreach ($list as $key => $val) {
            $groupbuy = self::getGroupbuyByGoodsId($val['id']);
            $list[$key]['groupbuy'] = [
                'id'         => $groupbuy['id'],
                'start_time' => $groupbuy['start_time'],
                'end_time'   => $groupbuy['end_time'],
                'countdown'  => self::getCountdown($groupbuy['end_time']),
                'apply'      => self::getApplyCount($groupbuy['id']),
                'image'      => $groupbuy['image'],
                'tag'        => $groupbuy['tag'],
            ];
        }

        return $list;
    }

    /**
     * 进行中团购列表
     * */
    public static function getGoingGroupbuys($page, $limit)
    {
        $now = time();
        $rows = Db::name('groupbuy')
            ->where('status', 1)
            ->where('end_time', '>', $now)
            ->page($page)
            ->limit($limit)
            ->order('id desc')
            ->select();

        if ($rows) {
            foreach ($rows as $key => $val) {
                $rows[$key]['countdown'] = self::getCountdown($val['end_time']);
                $rows[$key]['apply'] = self::getApplyCount($val['id']);
            }
        }

        return $rows;
    }

    /**
     * 进行中团购
     * */
    public static function getGoingGroupbuy($id)
    {
        $now = time();
        $groupbuy = Db::name('groupbuy')
            ->where('id', $id)
//            ->where('end_time', '>', $now)
            ->find();
        if ($groupbuy) {
            $groupbuy['countdown'] = self::getCountdown($groupbuy['end_time']);
            $groupbuy['apply'] = self::getApplyCount($groupbuy['id']);
        }

        return $groupbuy;
    }

    //团购商品列表
    public static function getGoodsListByGroupbuy($groupbuy_id, $page, $limit)
    {
        $rows = Db::name('groupbuy_goods')->alias('gg')
            ->join('goods g', 'g.id=gg.goods_id')
            ->where('gg.groupbuy_id', $groupbuy_id)
            ->page($page)
            ->limit($limit)
            ->field('g.id,g.name,g.image')
            ->select();
        return $rows;
    }

    /**
     * 计算倒计时
     * */
    public static function getCountdown($timestamp)
    {
        $end = self::getDatatimeFromTimestamp($timestamp);
        $now = self::getDatatimeFromTimestamp(time());
        $diff = date_diff($now, $end);

        $ret = [
            'days' => $diff->days,
            'h'    => $diff->h,
            'i'    => $diff->i,
            's'    => $diff->s,
        ];
        return $ret;
    }

    /**
     * 计算倒计时
     * */
    public static function getDatatimeFromTimestamp($timestamp)
    {
        $datetime = date_create(date('Y-m-d H:i:s', $timestamp));
        return $datetime;
    }

    /**
     * 团购申请 计数
     * */
    public static function getApplyCount($groupbuy_id)
    {
        $count = Db::name('groupbuy_apply')
            ->where('groupbuy_id', $groupbuy_id)
            ->count();
        return $count;
    }

    /**
     * 某一团购 店铺列表
     * */
    public static function getStoreList($page, $limit, $groupbuy_id)
    {
        $data = [];

        $store_id_array = Db::name('groupbuy_goods')->alias('gg')
            ->join('goods g', 'g.id = gg.goods_id')
            ->where('gg.groupbuy_id', $groupbuy_id)
            ->where([
                'g.audit' => 2, 'g.state' => 1
            ])
            ->column('gg.store_id');

        if (empty($store_id_array)) {
            return $data;
        }

        $list = Shop::getStoreList($page, $limit, [
            'id' => ['in', $store_id_array]
        ]);

        if (empty($list)) {
            return $data;
        }

        foreach ($list as $key => $val) {
            $goods_id_array = Db::name('groupbuy_goods')
                ->where('store_id', $val['id'])
                ->where('groupbuy_id', $groupbuy_id)
                ->column('goods_id');

            $goods_list = Goods::getGoodsList(1, 999, [
                'id'    => ['in', $goods_id_array],
                'audit' => Mine::GOODS_AUDIT_PASS,
                'state' => Mine::GOODS_STATE_UP,
            ]);

            $list[$key]['goods'] = $goods_list;
        }
        return $list;
    }


    /**
     * 商品详情
     * */
    public static function getGoods($goods_id)
    {
        $goods = Goods::getGoods($goods_id);
        $goods['store'] = Shop::getStore($goods['store_id']);

        $groupbuy = self::getGroupbuyByGoodsId($goods['id']);
        $goods['groupbuy'] = [
            'id'        => $groupbuy['id'],
            'end_time'  => $groupbuy['end_time'],
            'countdown' => self::getCountdown($groupbuy['end_time']),
        ];
        return $goods;
    }

    /**
     * 店铺团购商品列表
     * */
    public static function getStoreGoodsList($store_id, $groupbuy_id)
    {
        $goods_id_array = Db::name('groupbuy_goods')
            ->where('store_id', $store_id)
            ->where('groupbuy_id', $groupbuy_id)
            ->column('goods_id');

        $goods_list = Goods::getGoodsList(1, 999, [
            'id'    => ['in', $goods_id_array],
            'audit' => Mine::GOODS_AUDIT_PASS,
            'state' => Mine::GOODS_STATE_UP,
        ]);

        return $goods_list;
    }


    /**
     * 团购报名
     * */
    public static function applyGroupbuy($realname, $mobile, $cart, $login_id, $groupbuy_id)
    {
        $groupbuy = self::getGroupbuy($groupbuy_id);
        if ($groupbuy['end_time'] < time()) {
            return false;
        }

        $data2 = [];
        $arr = [];
        foreach ($cart as $val) {
            $goods = Goods::getGoods($val['id']);
            if (!empty($goods) && $val['num'] > 0) {
                array_push($arr, [
                    'goods_id'   => $goods['id'],
                    'goods_name' => $goods['name'],
                    'goods_num'  => $val['num'],
                ]);

                $data2[] = [
                    'groupbuy_id' => $groupbuy_id,
                    'store_id'    => $goods['store_id'],
                    'goods_id'    => $goods['id'],
                    'user_id'     => $login_id,
                    'createtime'  => time()
                ];
            }
        }

        if (empty($arr)) {
            return false;
        }

        $aid = $res = Db::name('groupbuy_apply')->insertGetId([
            'groupbuy_id' => $groupbuy_id,
            'store_id'    => $goods['store_id'],
            'goods'       => json_encode($arr, JSON_UNESCAPED_UNICODE),
            'user_id'     => $login_id,
            'realname'    => $realname,
            'mobile'      => $mobile,
            'createtime'  => time(),
        ]);

        if ($res) {
            foreach ($data2 as $key => $val) {
                $data2[$key]['apply_id'] = $aid;
            }
            Db::name('groupbuy_apply_2')->insertAll($data2);
        }

        return $res;
    }


    /**
     * 某店铺团购申请 列表
     * */
    public static function getGroupbuyApplyList($page = 1, $limit = 999, $map = [], $order = 'id desc')
    {
        $list = Db::name('groupbuy_apply')
            ->where($map)
            ->page($page)
            ->limit($limit)
            ->order($order)
            ->select();

        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $new = [];
                $arr = json_decode($val['goods'], true);
                //halt($arr);
                foreach ($arr as $k => $v) {
                    array_push($new, sprintf("[ 商品ID:%s | 商品名称:%s | 商品数量:%s ]",
                        $v['goods_id'], $v['goods_name'], $v['goods_num']));
                }
                //halt($new);
                $list[$key]['goods'] = implode(',', $new);

                $groupbuy = self::getGroupbuy($val['groupbuy_id']);
                $list[$key]['groupbuy_title'] = $groupbuy['title'];
            }
        }

        return $list;
    }

    /**
     * 某店铺团购申请 计数
     * */
    public static function getGroupbuyApplyCount($map = [])
    {
        $count = Db::name('groupbuy_apply')
            ->where($map)
            ->count();
        return $count;
    }


    //团购标题
    public static function getGroupbuyTitle($groupbuy_id)
    {
        return Db::name('groupbuy')->where('id', $groupbuy_id)->value('title');
    }

}