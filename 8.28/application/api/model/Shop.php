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

class Shop
{
    public static function getStoreByUserId($user_id)
    {
        $shop = Db::name('shop')->where('user_id', $user_id)->find();
        if (empty($shop)) {
            return [];
        }
        $store = Db::name('store')->where('shop_id', $shop['id'])->find();
        if (empty($store)) {
            return [];
        }

        return $store;
    }

    //店铺名称
    public static function getStoreName($id)
    {
        $res = Db::name('store')->where('id', $id)->value('name');
        return $res;
    }

    public static function getShop($field, $value)
    {
        $info = Db::name('shop')->where($field, $value)->find();
        return $info;
    }

    public static function incLoginTimes($id)
    {
        $res = Db::name('shop')->where('id', $id)->setInc('login_times', 1);
        return $res;
    }

    public static function setPwd($pwd, $id)
    {
        $info = self::getShop('id', $id);
        $password = md5(md5($pwd) . $info['salt']);
        $res = Db::name('shop')->where('id', $id)->update([
            'password' => $password
        ]);
        return $res;
    }

    /**
     * 设置店铺
     * */
    public static function setStore($id, $data)
    {
        $res = Db::name('store')->where('id', $id)->update($data);
        return $res;
    }

    /**
     * 设置店铺封面
     * */
    public static function setStoreImage($user_id, $image)
    {
        $store = self::getStoreByUserId($user_id);
        return self::setStore($store['id'], [
            'image' => $image
        ]);
    }

    /**
     * 设置店铺名称
     * */
    public static function setStoreName($user_id, $name)
    {
        $store = self::getStoreByUserId($user_id);
        return self::setStore($store['id'], [
            'name' => $name
        ]);
    }

    /**
     * 设置店铺电话
     * */
    public static function setStorePhone($user_id, $phone)
    {
        $store = self::getStoreByUserId($user_id);
        return self::setStore($store['id'], [
            'phone' => $phone
        ]);
    }


    /**
     * 设置店铺资质文件
     * */
    public static function setStoreQualification($user_id, $qualification)
    {
        $store = self::getStoreByUserId($user_id);
        return self::setStore($store['id'], [
            'qualification' => $qualification
        ]);
    }

    /**
     * 设置店铺LOGO
     * */
    public static function setStoreLogo($user_id, $logo)
    {
        $store = self::getStoreByUserId($user_id);
        return self::setStore($store['id'], [
            'logo' => $logo
        ]);
    }


    /**
     * 获取分组列表
     * */
    public static function getGroupList($user_id, $page, $limit)
    {
        $store = self::getStoreByUserId($user_id);
        $list = Db::name('goods_group_store')
            ->where('store_id', $store['id'])
            ->page($page)
            ->limit($limit)
            ->order('weigh desc')
            ->select();
        return $list;
    }


    /**
     * 获取分组列表
     * */
    public static function getGroupList2($user_id, $page, $limit)
    {
        $store = self::getStoreByUserId($user_id);
        $list = Db::name('goods_group_store')
            ->where('store_id', $store['id'])
            ->where('status', 1)
            ->page($page)
            ->limit($limit)
            ->order('weigh desc')
            ->select();
        return $list;
    }

    /**
     * 新增分组
     * */
    public static function addGroup($user_id, $name)
    {
        $store = self::getStoreByUserId($user_id);
        $iid = Db::name('goods_group_store')->insertGetId([
                'name' => $name,
                'image' => '',
                'cate_ids' => '',
                'store_id' => $store['id'],
                'weigh' => 0,
                'status' => 1,
            ]);
        return $iid;
    }

    /**
     * 编辑分组
     * */
    public static function editGroup($user_id, $id, $name)
    {
        $store = self::getStoreByUserId($user_id);
        $res = Db::name('goods_group_store')
            ->where('store_id', $store['id'])
            ->where('id', $id)
            ->update([
                'name' => $name
            ]);
        return $res;
    }

    /**
     * 删除分组
     * */
    public static function delGroup($user_id, $id)
    {
        $store = self::getStoreByUserId($user_id);
        $res = Db::name('goods_group_store')
            ->where('store_id', $store['id'])
            ->where('id', $id)
            ->delete();
        return $res;
    }

    /**
     * 切换分组状态
     * */
    public static function swGroupStatus($user_id, $id)
    {
        $info = self::getGroup($id);
        if ($info['status'] == 1) {
            $newStatus = 0;
        } else {
            $newStatus = 1;
        }

        $store = self::getStoreByUserId($user_id);
        $res = Db::name('goods_group_store')
            ->where('store_id', $store['id'])
            ->where('id', $id)
            ->update([
                'status' => $newStatus
            ]);
        return $res;
    }

    /**
     * 获取分组
     * */
    public static function getGroup($id)
    {
        $info = Db::name('goods_group_store')
            ->where('id',$id)
            ->find();
        return $info;
    }


    /**
     * 店铺信息
     * */
    public static function getStore($id)
    {
        $info = Db::name('store')
            ->where('id', $id)
            ->field('about', true)
            ->find();

        if (!empty($info)) {
            $user = Db::name('shop')->alias('s')
                ->join('user u', 'u.id=s.user_id')
                ->where('s.id', $info['shop_id'])
                ->field('u.id,u.avatar,u.nickname')
                ->find();
            $info['user'] = $user;
        }

        return $info;
    }

    /**
     * 店铺信息 关联用户
     * */
    public static function getStoreUser($info, $login_id)
    {
        if ($info) {
            $shop = User::getShop('id', $info['shop_id']);
            if ($shop) {
                $user = User::getUser('id', $shop['user_id']);
                $info['user'] = [
                    'id' => $user['id'],
                    'avatar' => $user['avatar'],
                    'nickname' => $user['nickname'],
                    'follow' => User::is_follower($user['id'], $login_id),
                ];
            }
        }
        return $info;
    }

    /**
     * 关于店铺
     * */
    public static function getStoreAbout($id)
    {
        $val = Db::name('store')
            ->where('id', $id)
            ->value('about');
        return $val;
    }

    /**
     * 店铺公告
     * */
    public static function getStoreNoticeList($id)
    {
        $list = Db::name('store_notice')
            ->where('store_id', $id)
            ->order('weigh', 'asc')
            ->field('content', true)
            ->select();
        return $list;
    }

    /**
     * 店铺公告 详情
     * */
    public static function getStoreNotice($id)
    {
        $info = Db::name('store_notice')
            ->where('id', $id)
            ->find();
        return $info;
    }

    /**
     * 店铺轮播
     * */
    public static function getStoreCarouselList($id)
    {
        $list = Db::name('store_carousel')
            ->where('store_id', $id)
            ->order('weigh', 'asc')
            ->select();
        return $list;
    }

    /**
     * 店铺首页 新品
     * */
    public static function getStoreHomeNewGoodsList($id)
    {
        $list = Db::name('goods')
            ->where('audit', Mine::GOODS_AUDIT_PASS)
            ->where('state', Mine::GOODS_STATE_UP)
            ->where('store_id', $id)
            ->where('new', 1)
            ->limit(3)
            ->order('id desc')
            ->field('detail,equities', true)
            ->select();
        return $list;
    }

    /**
     * 店铺首页 推荐
     * */
    public static function getStoreHomeCommendedGoodsList($id)
    {
        $list = Db::name('goods')
            ->where('audit', Mine::GOODS_AUDIT_PASS)
            ->where('state', Mine::GOODS_STATE_UP)
            ->where('store_id', $id)
            ->where('store_home', 1)
            ->limit(4)
            ->order('id desc')
            ->field('detail,equities', true)
            ->select();
        return $list;
    }

    /**
     * 店铺商品
     * */
    public static function getStoreGoodsList($id, $page, $limit)
    {
        $list = Db::name('goods')
            ->where('audit', Mine::GOODS_AUDIT_PASS)
            ->where('state', Mine::GOODS_STATE_UP)
            ->where('store_id', $id)
            ->page($page)
            ->limit($limit)
            ->order('id desc')
            ->field('detail,equities', true)
            ->select();
        return $list;
    }


    /**
     * 店铺列表
     * */
    public static function getStoreList($page = 1, $limit = 999, $map = [], $field = true, $order = 'id desc')
    {
        $list = Db::name('store')
            ->where($map)
            ->where('logo', '<>', '')
            ->where('name', '<>', '')
            ->where('image', '<>', '')
            ->page($page)
            ->limit($limit)
            ->field($field)
            ->order($order)
            ->select();
        return $list;
    }

    /**
     * 为店铺列表添加字段
     * */
    public static function addFieldForStroeList($list)
    {
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $goods = Goods::getGoodsList(1, 3, [
                    'store_id' => $val['id'],
                    'audit' => Mine::GOODS_AUDIT_PASS,
                    'state' => Mine::GOODS_STATE_UP,
                ]);
                $list[$key]['goods'] = $goods;

                $user = self::getStoreUserByStoreId($val['id']);
                $list[$key]['user_id'] = $user['id'];
            }

        }
        return $list;
    }

    public static function getStoreUserByStoreId($store_id)
    {
        $store = self::getStore($store_id);
        $shop = Db::name('shop')->where('id', $store['shop_id'])->find();
        $user = User::getUser('id', $shop['user_id']);
        return $user;
    }

    /**
     * 店铺搜索
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
     * 增加拼团次数
     * */
    public static function incStoreGroupbuy($store_id)
    {
        $res = Db::name('store')
            ->where('id', $store_id)
            ->setInc('groupbuy', 1);
        return $res;
    }

    /**
     * 增加报价次数
     * */
    public static function incStoreSupply($store_id, $step)
    {
        $res = Db::name('store')
            ->where('id', $store_id)
            ->setInc('supply', $step);
        return $res;
    }

}