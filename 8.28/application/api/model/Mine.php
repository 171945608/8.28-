<?php
/**
 * Created by PhpStorm.
 * User: 18660
 * Date: 2020/6/21
 * Time: 6:37
 */

namespace app\api\model;

use function fast\e;
use fast\Random;
use think\Db;
use think\Exception;
use think\Config;

class Mine
{
    //商户账号封禁
    const FORBID_NONE      = 0;
    const FORBID_THREE_DAY = 1;
    const FORBID_SEVEN_DAY = 2;
    const FORBID_ONE_MONTH = 3;
    const FORBID_FOREVER   = 4;

    //商户账号审核
    const AUDIT_STATE_COMMIT  = 10;
    const AUDIT_STATE_ERROR   = 20;
    const AUDIT_STATE_SUCCESS = 30;

    //店铺轮播限制
    const STORE_CAROUSEL_LIMIT = 10;
    //店铺公告限制
    const STORE_NOTICE_LIMIT = 10;

    //商品状态
    const GOODS_STATE_UP   = 1;
    const GOODS_STATE_DOWN = 0;

    //商品审核
    const GOODS_AUDIT_COMMIT = 1;
    const GOODS_AUDIT_PASS   = 2;
    const GOODS_AUDIT_REJECT = 3;

    /**
     * 经营类目 列表
     * */
    public static function getBusinessCateList($page, $limit)
    {
        return Db::name('business_cate')
            ->page($page)
            ->limit($limit)
            ->order('id desc')
            ->select();
    }

    /**
     * 经营类目 信息
     */
    public static function getBusinessCate($id)
    {
        return Db::name('business_cate')->where('id', $id)->find();
    }

    /**
     * 商家入驻 创建
     * */
    public static function createShop($business_cate_id, $link_man, $link_mobile, $link_email, $idcard_ps, $idcard_rs, $business_license, $qualification, $user_id, $company, $address)
    {
        $shop = self::getShop('user_id', $user_id);
        $business_cate = self::getBusinessCate($business_cate_id);
        $now = time();
        $salt = Random::alnum();
        $password = md5(md5('kuaicaibao') . $salt);
        $data = [
            'username' => $link_mobile,
            'nickname' => $link_man,
            'password' => $password,
            'salt' => $salt,
            'avatar' => 'https://kuaicaibao.oss-cn-beijing.aliyuncs.com/default/user_avatar.png',
            'email' => $link_email,
            'loginfailure' => 0,
            'logintime' => null,
            'loginip' => null,
            'createtime' => $now,
            'updatetime' => $now,
            'status' => 1,
            'business_cate_id' => $business_cate['id'],
            'business_cate_name' => $business_cate['name'],
            'idcard_ps' => $idcard_ps,
            'idcard_rs' => $idcard_rs,
            'business_license' => $business_license,
            'qualification' => $qualification,
            'audit_state' => self::AUDIT_STATE_COMMIT,
            'audit_msg' => '',
            'user_id' => $user_id,
            'forbid_type' => self::FORBID_NONE,
            'forbid_end_time' => null,
            'company' => $company,
            'address' => $address,
        ];

        //halt($shop);
        if (empty($shop)) {
            $res = Db::name('shop')->insert($data);
            if ($res) {
                return [
                    'code' => 1,
                    'msg' => '操作成功',
                ];
            } else {
                return [
                    'code' => 0,
                    'msg' => '操作失败，请重试。',
                ];
            }

        } else if ($shop['audit_state'] != self::AUDIT_STATE_ERROR) {
            return [
                'code' => 0,
                'msg' => '已经提交申请',
            ];
        } else {
            $res = self::setShop($shop['id'], $data);
            if ($res) {
                return [
                    'code' => 1,
                    'msg' => '操作成功',
                ];
            } else {
                return [
                    'code' => 0,
                    'msg' => '操作失败，请重试。',
                ];
            }
        }
        return $res;
    }

    /**
     * 商家入驻 获取
     * */
    public static function getShop($field, $value)
    {
        return Db::name('shop')->where($field, $value)->find();
    }

    /**
     * 商家入驻 设置
     * */
    public static function setShop($id, $data)
    {
        return Db::name('shop')->where('id', $id)->update($data);
    }

    /**
     * 商家登录 检查封禁
     * */
    public static function checkForbid($id)
    {
        $shop = self::getShop('id', $id);
        if (in_array($shop['forbid_type'], [self::FORBID_NONE])) {
            return true;
        }

        //过封禁期的解禁
        if (in_array($shop['forbid_type'], [self::FORBID_THREE_DAY, self::FORBID_SEVEN_DAY, self::FORBID_ONE_MONTH])) {
            if ($shop['forbid_end_time'] < time()) {
                self::relieveForbid($id);
                return true;
            }
        }

        return false;
    }

    /**
     * 商家登录 解除封禁
     * */
    public static function relieveForbid($id)
    {
        return self::setShop($id, [
            'forbid_type' => 0,
            'forbid_end_time' => null,
        ]);
    }

    /**
     * 商家管理 封禁账号
     * */
    public static function forbidShop($id, $type)
    {
        $now = time();
        switch ((int)$type) {
            case 1:
                $end = $now + 60 * 60 * 24 * 3;
                break;

            case 2:
                $end = $now + 60 * 60 * 24 * 7;
                break;

            case 3:
                $end = $now + 60 * 60 * 24 * 30;
                break;

            case 4:
                $end = 2147483647;
                break;
        }
        return self::setShop($id, [
            'forbid_type' => $type,
            'forbid_end_time' => $end,
        ]);
    }

    /**
     * 商家管理 初始店铺
     * */
    public static function initStore($shop_id)
    {
        $count = Db::name('store')->where('shop_id', $shop_id)->count();
        if ($count == 0) {
            $shop = self::getShop('id', $shop_id);
            Db::name('store')->insert([
                'name' => $shop['nickname'] . '的小店',
                'phone' => $shop['username'],
                'logo' => 'https://kuaicaibao.oss-cn-beijing.aliyuncs.com/default/store_logo.png',
                'image' => 'https://kuaicaibao.oss-cn-beijing.aliyuncs.com/default/store_cover.jpg',
                'area' => $shop['address'],
                'shop_id' => $shop_id,
                'createtime' => time(),
            ]);
        }
    }

    /**
     * 店铺管理 获取
     * */
    public static function getStore($field, $value)
    {
        $info = Db::name('store')->where($field, $value)->find();

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
     * 店铺管理 设置
     * */
    public static function setStore($id, $data)
    {
        return Db::name('store')->where('id', $id)->update($data);
    }

    /**
     * 店铺管理 轮播 列表
     * */
    public static function getStoreCarouselList($store_id, $page, $limit)
    {
        $list = Db::name('store_carousel')
            ->where('store_id', $store_id)
            ->order('weigh asc')
            ->page($page)
            ->limit($limit)
            ->select();
        return $list;
    }

    /**
     * 店铺管理 轮播 数量
     * */
    public static function getStoreCarouselCount($store_id)
    {
        $count = Db::name('store_carousel')
            ->where('store_id', $store_id)
            ->count();
        return $count;
    }

    /**
     * 店铺管理 轮播 新增
     * */
    public static function addStoreCarousel($store_id, $data)
    {
        if (self::getStoreCarouselCount($store_id) >= self::STORE_CAROUSEL_LIMIT) {
            return false;
        }

        $data['store_id'] = $store_id;
        return Db::name('store_carousel')->insert($data);
    }

    /**
     * 店铺管理 轮播 信息
     * */
    public static function getStoreCarousel($store_id, $carousel_id)
    {
        $info = Db::name('store_carousel')
            ->where('store_id', $store_id)
            ->where('id', $carousel_id)
            ->find();
        return $info;
    }

    /**
     * 店铺管理 轮播 编辑
     * */
    public static function editStoreCarousel($store_id, $carousel_id, $data)
    {
        return Db::name('store_carousel')
            ->where('store_id', $store_id)
            ->where('id', $carousel_id)
            ->update($data);
    }

    /**
     * 店铺管理 轮播 删除
     * */
    public static function deleteStoreCarousel($store_id, $ids)
    {
        return Db::name('store_carousel')
            ->where('store_id', $store_id)
            ->where('id', 'in', $ids)
            ->delete();
    }

    /**
     * 商品管理 分类 列表
     * */
    public static function getGoodsCateList($store_id, $page, $limit)
    {
        $list = Db::name('goods_cate_store')
            ->where('store_id', $store_id)
            ->order('weigh asc')
            ->page($page)
            ->limit($limit)
            ->select();
        return $list;
    }

    /**
     * 商品管理 分类 数量
     * */
    public static function getGoodsCateCount($store_id)
    {
        $count = Db::name('goods_cate_store')
            ->where('store_id', $store_id)
            ->count();
        return $count;
    }

    /**
     * 商品管理 分类 平台分类列表
     * */
    public static function getSystemGoodsCateList()
    {
        $list = Db::name('goods_cate_system')
            ->order('weigh asc')
            ->select();
        return $list;
    }

    /**
     * 商品管理 分类 新增
     * */
    public static function addGoodsCate($store_id, $data)
    {
        $system_cate = Db::name('goods_cate_system')
            ->where('id', $data['system_id'])
            ->find();
        $data['system_name'] = $system_cate['name'];
        $data['store_id'] = $store_id;
        return Db::name('goods_cate_store')->insert($data);
    }

    /**
     * 商品管理 分类 获取
     * */
    public static function getGoodsCate($store_id, $cate_id)
    {
        $info = Db::name('goods_cate_store')
            ->where('store_id', $store_id)
            ->where('id', $cate_id)
            ->find();
        return $info;
    }

    /**
     * 商品管理 分类 编辑
     * */
    public static function editGoodsCate($store_id, $cate_id, $data)
    {
        $system_cate = Db::name('goods_cate_system')
            ->where('id', $data['system_id'])
            ->find();
        $data['system_name'] = $system_cate['name'];
        return Db::name('goods_cate_store')
            ->where('store_id', $store_id)
            ->where('id', $cate_id)
            ->update($data);
    }

    /**
     * 商品管理 分类 删除
     * */
    public static function deleteGoodsCate($store_id, $ids)
    {
        return Db::name('goods_cate_store')
            ->where('store_id', $store_id)
            ->where('id', 'in', $ids)
            ->delete();
    }

    /**
     * 商品管理 分组 店铺分类列表
     * */
    public static function getStoreGoodsCateList($store_id)
    {
        $list = Db::name('goods_cate_store')
            ->where('store_id', $store_id)
            ->order('weigh asc')
            ->select();
        return $list;
    }

    /**
     * 商品管理 分组 新增
     * */
    public static function addGoodsGroup($store_id, $data)
    {
        $data['store_id'] = $store_id;
        return Db::name('goods_group_store')->insert($data);
    }

    /**
     * 商品管理 分组 列表
     * */
    public static function getGoodsGroupList($store_id, $page, $limit)
    {
        $list = Db::name('goods_group_store')
            ->where('store_id', $store_id)
            ->order('weigh asc')
            ->page($page)
            ->limit($limit)
            ->select();
        return $list;
    }

    /**
     * 商品管理 分组 列表 格式化
     * */
    public static function formatGoodsGroupList($list)
    {
        foreach ($list as $key => $val) {
            $list[$key]['cate_names'] = self::getGoodsCateNames($val['cate_ids']);
        }
        return $list;
    }

    /**
     * 商品管理 分组 列表 格式化 id2name
     * */
    protected static function getGoodsCateNames(string $ids)
    {
        $names = Db::name('goods_cate_store')
            ->where('id', 'in', $ids)
            ->column('name');
        return implode(',', $names);
    }

    /**
     * 商品管理 分组 数量
     * */
    public static function getGoodsGroupCount($store_id)
    {
        $count = Db::name('goods_group_store')
            ->where('store_id', $store_id)
            ->count();
        return $count;
    }

    /**
     * 商品管理 分组 获取
     * */
    public static function getGoodsGroup($store_id, $group_id)
    {
        $info = Db::name('goods_group_store')
            ->where('store_id', $store_id)
            ->where('id', $group_id)
            ->find();
        return $info;
    }

    /**
     * 商品管理 分组 编辑
     * */
    public static function editGoodsGroup($store_id, $group_id, $data)
    {
        return Db::name('goods_group_store')
            ->where('store_id', $store_id)
            ->where('id', $group_id)
            ->update($data);
    }

    /**
     * 商品管理 分组 切换状态
     * */
    public static function switchGoodsGroupStatus($store_id, $group_id)
    {
        $info = Db::name('goods_group_store')
            ->where('store_id', $store_id)
            ->where('id', $group_id)
            ->find();

        return Db::name('goods_group_store')
            ->where('store_id', $store_id)
            ->where('id', $group_id)
            ->update(['status' => $info['status'] == 1 ? 0 : 1]);
    }

    /**
     * 商品管理 分组 删除
     * */
    public static function deleteGoodsGroup($store_id, $ids)
    {
        return Db::name('goods_group_store')
            ->where('store_id', $store_id)
            ->where('id', 'in', $ids)
            ->delete();
    }

    /**
     * 商品管理 待审列表
     * */
    public static function getGoodsForAuditList($store_id, $page, $limit)
    {
        $list = Db::name('goods')
            ->where('store_id', $store_id)
            ->where('audit', self::GOODS_AUDIT_COMMIT)
            ->order('id desc')
            ->page($page)
            ->limit($limit)
            ->select();
        return $list;
    }

    /**
     * 商品管理 待审列表 数量
     * */
    public static function getGoodsForAuditCount($store_id)
    {
        $count = Db::name('goods')
            ->where('store_id', $store_id)
            ->where('audit', self::GOODS_AUDIT_COMMIT)
            ->count();
        return $count;
    }

    /**
     * 商品管理 驳回列表
     * */
    public static function getGoodsAuditRejectList($store_id, $page, $limit)
    {
        $list = Db::name('goods')
            ->where('store_id', $store_id)
            ->where('audit', self::GOODS_AUDIT_REJECT)
            ->order('id desc')
            ->page($page)
            ->limit($limit)
            ->select();
        return $list;
    }

    /**
     * 商品管理 驳回列表 数量
     * */
    public static function getGoodsAuditRejectCount($store_id)
    {
        $count = Db::name('goods')
            ->where('store_id', $store_id)
            ->where('audit', self::GOODS_AUDIT_REJECT)
            ->count();
        return $count;
    }

    /**
     * 商品管理 上架列表
     * */
    public static function getUpGoodsList($store_id, $page, $limit)
    {
        $list = Db::name('goods')
            ->where('store_id', $store_id)
            ->where('audit', self::GOODS_AUDIT_PASS)
            ->where('state', self::GOODS_STATE_UP)
            ->order('id desc')
            ->page($page)
            ->limit($limit)
            ->select();
        return $list;
    }

    /**
     * 商品管理 上架列表 数量
     * */
    public static function getUpGoodsCount($store_id)
    {
        $count = Db::name('goods')
            ->where('store_id', $store_id)
            ->where('audit', self::GOODS_AUDIT_PASS)
            ->where('state', self::GOODS_STATE_UP)
            ->count();
        return $count;
    }

    /**
     * 商品管理 下架列表
     * */
    public static function getDownGoodsList($store_id, $page, $limit)
    {
        $list = Db::name('goods')
            ->where('store_id', $store_id)
            ->where('audit', self::GOODS_AUDIT_PASS)
            ->where('state', self::GOODS_STATE_DOWN)
            ->order('id desc')
            ->page($page)
            ->limit($limit)
            ->select();
        return $list;
    }

    /**
     * 商品管理 上架列表 数量
     * */
    public static function getDownGoodsCount($store_id)
    {
        $count = Db::name('goods')
            ->where('store_id', $store_id)
            ->where('audit', self::GOODS_AUDIT_PASS)
            ->where('state', self::GOODS_STATE_DOWN)
            ->count();
        return $count;
    }

    /**
     * 商品管理 商品 列表 格式化
     * */
    public static function formatGoodsList($list)
    {
        $audit_arr = [
            self::GOODS_AUDIT_COMMIT => '上架提交',
            self::GOODS_AUDIT_PASS => '审核通过',
            self::GOODS_AUDIT_REJECT => '审核驳回',
        ];
        foreach ($list as $key => $val) {
            $list[$key]['group_names'] = self::getGoodsGroupNames($val['group_ids']);
            $list[$key]['audit_text'] = key_exists($val['audit'], $audit_arr) ? $audit_arr[$val['audit']] : '';
        }
        return $list;
    }

    /**
     * 商品管理 商品 列表 格式化 id2name
     * */
    protected static function getGoodsGroupNames(string $ids)
    {
        $names = Db::name('goods_group_store')
            ->where('id', 'in', $ids)
            ->column('name');
        return implode(',', $names);
    }

    /**
     * 商品管理 商品 可用分组列表
     * */
    public static function getUsableStoreGoodsGroupList($store_id)
    {
        $list = Db::name('goods_group_store')
            ->where('store_id', $store_id)
            ->where('status', 1)
            ->order('weigh asc')
            ->select();
        return $list;
    }

    /**
     * 商品管理 分组 平台分类列表
     * */
    public static function getGoodsCateListForPlatform()
    {
        $list = Db::name('goods_cate_system')
            ->order('weigh asc')
            ->select();
        return $list;
    }

    /**
     * 商品管理 商品 新增
     * */
    public static function addGoods($store_id, $data)
    {
        $cate = Db::name('goods_cate_system')
            ->where('id', $data['cate_id'])
            ->find();
        $data['cate_name'] = $cate['name'];
        $data['quotations'] = 0;
        $data['new'] = 0;
        $data['hot'] = 0;
        $data['special'] = 0;
        $data['audit'] = self::GOODS_AUDIT_COMMIT;
        $data['audit_msg'] = '';

        $data['store_id'] = $store_id;
        $data['uid'] = md5(microtime(true));
        return Db::name('goods')->insert($data);
    }

    /**
     * 商品管理 商品 获取
     * */
    public static function getGoods($store_id, $goods_id)
    {
        $info = Db::name('goods')
            ->where('store_id', $store_id)
            ->where('id', $goods_id)
            ->find();
        return $info;
    }

    /**
     * 商品管理 商品 编辑
     * */
    public static function editGoods($store_id, $goods_id, $data)
    {
        $cate = Db::name('goods_cate_system')
            ->where('id', $data['cate_id'])
            ->find();
        $data['cate_name'] = $cate['name'];
        return Db::name('goods')
            ->where('store_id', $store_id)
            ->where('id', $goods_id)
            ->update($data);
    }

    /**
     * 商品管理 商品 切换状态
     * */
    public static function switchGoodsState($store_id, $goods_id)
    {
        $info = Db::name('goods')
            ->where('store_id', $store_id)
            ->where('id', $goods_id)
            ->find();

        return Db::name('goods')
            ->where('store_id', $store_id)
            ->where('id', $goods_id)
            ->update(['state' => $info['state'] == 1 ? 0 : 1]);
    }

    /**
     * 商品管理 商品 店铺首页
     * */
    public static function switchStoreHome($store_id, $goods_id)
    {
        $info = Db::name('goods')
            ->where('store_id', $store_id)
            ->where('id', $goods_id)
            ->find();

        return Db::name('goods')
            ->where('store_id', $store_id)
            ->where('id', $goods_id)
            ->update(['store_home' => $info['store_home'] == 1 ? 0 : 1]);
    }

    /**
     * 商品管理 商品 新品
     * */
    public static function switchGoodsNew($store_id, $goods_id)
    {
        $info = Db::name('goods')
            ->where('store_id', $store_id)
            ->where('id', $goods_id)
            ->find();

        return Db::name('goods')
            ->where('store_id', $store_id)
            ->where('id', $goods_id)
            ->update(['new' => $info['new'] == 1 ? 0 : 1]);
    }

    /**
     * 商品管理 商品 热销
     * */
    public static function switchGoodsHot($store_id, $goods_id)
    {
        $info = Db::name('goods')
            ->where('store_id', $store_id)
            ->where('id', $goods_id)
            ->find();

        return Db::name('goods')
            ->where('store_id', $store_id)
            ->where('id', $goods_id)
            ->update(['hot' => $info['hot'] == 1 ? 0 : 1]);
    }

    /**
     * 商品管理 商品 特供
     * */
    public static function switchGoodsSpecial($store_id, $goods_id)
    {
        $info = Db::name('goods')
            ->where('store_id', $store_id)
            ->where('id', $goods_id)
            ->find();

        return Db::name('goods')
            ->where('store_id', $store_id)
            ->where('id', $goods_id)
            ->update(['special' => $info['special'] == 1 ? 0 : 1]);
    }

    /**
     * 商品管理 商品 优惠
     * */
    public static function switchGoodsDiscount($store_id, $goods_id)
    {
        $info = Db::name('goods')
            ->where('store_id', $store_id)
            ->where('id', $goods_id)
            ->find();

        return Db::name('goods')
            ->where('store_id', $store_id)
            ->where('id', $goods_id)
            ->update(['discount' => $info['discount'] == 1 ? 0 : 1]);
    }

    /**
     * 商品管理 商品 删除
     * */
    public static function deleteGoods($store_id, $ids)
    {
        Db::startTrans();
        try {
            Db::name('goods')
                ->where('store_id', $store_id)
                ->where('id', 'in', $ids)
                ->delete();

            Db::name('discount_goods')
                ->where('store_id', $store_id)
                ->where('goods_id', 'in', $ids)
                ->delete();

            Db::name('goods_collect')
                ->where('goods_id', 'in', $ids)
                ->delete();

            Db::name('groupbuy_goods')
                ->where('store_id', $store_id)
                ->where('goods_id', 'in', $ids)
                ->delete();

            Db::name('vip_goods')
                ->where('store_id', $store_id)
                ->where('goods_id', 'in', $ids)
                ->delete();

            Db::commit();
            return true;
        } catch (Exception $e) {

            Db::rollback();
        }
        return false;
    }

    /**
     * 商品管理 商品 设置
     * */
    public static function setGoods($id, $data)
    {
        return Db::name('goods')->where('id', $id)->update($data);
    }


    /**
     * 店铺公告 列表
     * */
    public static function getStoreNoticeList($store_id, $page, $limit)
    {
        $list = Db::name('store_notice')
            ->where('store_id', $store_id)
            ->order('weigh asc')
            ->page($page)
            ->limit($limit)
            ->select();
        return $list;
    }

    /**
     * 店铺公告 数量
     * */
    public static function getStoreNoticeCount($store_id)
    {
        $count = Db::name('store_notice')
            ->where('store_id', $store_id)
            ->count();
        return $count;
    }

    /**
     * 店铺公告 新增
     * */
    public static function addStoreNotice($store_id, $data)
    {
        if (self::getStoreNoticeCount($store_id) >= self::STORE_NOTICE_LIMIT) {
            return false;
        }

        $data['store_id'] = $store_id;
        return Db::name('store_notice')->insert($data);
    }

    /**
     * 店铺公告 获取
     * */
    public static function getStoreNotice($store_id, $id)
    {
        $info = Db::name('store_notice')
            ->where('store_id', $store_id)
            ->where('id', $id)
            ->find();
        return $info;
    }

    /**
     * 店铺公告 编辑
     * */
    public static function editStoreNotice($store_id, $id, $data)
    {
        return Db::name('store_notice')
            ->where('store_id', $store_id)
            ->where('id', $id)
            ->update($data);
    }

    /**
     * 店铺公告 删除
     * */
    public static function deleteStoreNotice($store_id, $ids)
    {
        return Db::name('store_notice')
            ->where('store_id', $store_id)
            ->where('id', 'in', $ids)
            ->delete();
    }

}