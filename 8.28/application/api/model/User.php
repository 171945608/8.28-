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

class User
{
    const FORBID_NONE      = 0;
    const FORBID_THREE_DAY = 1;
    const FORBID_SEVEN_DAY = 2;
    const FORBID_ONE_MONTH = 3;
    const FORBID_FOREVER   = 4;


    public static function getUser($field, $value)
    {
        return Db::name('user')->where($field, $value)->find();
    }
    public static function getNickname($id)
    {
        return Db::name('user')->where('id', $id)->value('nickname');
    }

    public static function setUser($id, $data)
    {
        return Db::name('user')->where('id', $id)->update($data);
    }

    public static function checkForbid($id)
    {
        $user = self::getUser('id', $id);
        if (in_array($user['forbid_type'], [self::FORBID_NONE])) {
            return true;
        }

        //过封禁期的解禁
        if (in_array($user['forbid_type'], [self::FORBID_THREE_DAY, self::FORBID_SEVEN_DAY, self::FORBID_ONE_MONTH])) {
            if ($user['forbid_end_time'] < time()) {
                self::relieveForbid($id);
                return true;
            }
        }

        return false;
    }

    public static function relieveForbid($id)
    {
        return self::setUser($id, [
            'forbid_type'     => 0,
            'forbid_end_time' => null,
        ]);
    }

    public static function forbidUser($id, $type)
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
        return self::setUser($id, [
            'forbid_type'     => $type,
            'forbid_end_time' => $end,
        ]);
    }

    /**
     * 获取身份认证接收手机号码
     * */
    public static function getIdAuthReceiver()
    {
        $val = Db::name('param')
            ->where('name', 'id_auth_receiver')
            ->value('value');
//        halt($val);
        return $val;
    }

    /**
     * 通过身份认证
     * */
    public static function passIdAuth($user_id)
    {
        $res = self::setUser($user_id, [
            'id_auth' => 2
        ]);
        return $res;
    }

    /**
     * 驳回身份认证
     * */
    public static function rejectIdAuth($user_id)
    {
        $res = self::setUser($user_id, [
            'id_auth'   => 0,
            'realname'  => '',
            'idcard_no' => '',
            'idcard_ps' => null,
            'idcard_rs' => null,
        ]);
        return $res;
    }

    /**
     * 通过VIP认证
     * */
    public static function passVipAuth($user_id)
    {
        $res = self::setUser($user_id, [
            'vip_auth' => 2
        ]);
        return $res;
    }

    /**
     * 驳回VIP认证
     * */
    public static function rejectVipAuth($user_id)
    {
        $res = self::setUser($user_id, [
            'vip_auth' => 0,
            'vipname'  => '',
            'viplink'  => '',
        ]);
        return $res;
    }

    /**
     * 名片模板列表 全部
     * */
    public static function getBcardTemplateList()
    {
        $list = Db::name('bcard_template')->select();
        return $list;
    }

    /**
     * 选择名片模板
     * */
    public static function setBcardTemplate($user_id, $bt_id)
    {
        $res = self::setUser($user_id, [
            'bt_id' => $bt_id,
        ]);
        return $res;
    }

    /**
     * 获取名片数据
     * */
    public static function getBcard($user_id)
    {
        $info = Db::name('bcard')->where('user_id', $user_id)->find();
        return $info;
    }

    /**
     * 获取名片模板
     * */
    public static function getBcardTemplate($user_id)
    {
        $user = self::getUser('id', $user_id);
        $bt_id = $user['bt_id'] > 0 && $user['bt_id'] < 6 ? $user['bt_id'] : 1;
        $template = Db::name('bcard_template')->where('id', $bt_id)->find();
        return $template;
    }

    /**
     * 设置名片数据
     * */
    public static function setBcard($user_id, $company, $position, $name, $mobile, $phone, $address, $business)
    {
        $data = [
            'company'  => $company,
            'position' => $position,
            'name'     => $name,
            'mobile'   => $mobile,
            'phone'    => $phone,
            'address'  => $address,
            'business' => $business,
        ];
        $info = self::getBcard($user_id);
        if (empty($info)) {
            $data['user_id'] = $user_id;
            $res = Db::name('bcard')->insert($data);
        } else {
            $res = Db::name('bcard')->where('user_id', $user_id)->update($data);
        }
        return $res;
    }

    /**
     * 获取店铺地址
     * */
    public static function getStoreAddress($user_id)
    {
        $shop = Db::name('shop')->where('user_id', $user_id)->find();
        if ($shop) {
            $store = Db::name('store')->where('shop_id', $shop['id'])->find();
            return $store['address'];
        }
        return '';
    }

    /**
     * 获取作者的关注列表
     * */
    public static function getFocusList($user_id, $page, $limit, $login_id)
    {
        $list = Db::name('user_follow')
            ->where('follower_id', $user_id)
            ->page($page)
            ->limit($limit)
            ->order('id desc')
            ->select();

        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $user = self::getUser('id', $val['user_id']);
                $follow = (Db::name('user_follow')
                        ->where('user_id', $user['id'])
                        ->where('follower_id', $login_id)
                        ->count()) > 0;
                $list[$key]['user'] = [
                    'id'       => $user['id'],
                    'nickname' => $user['nickname'],
                    'avatar'   => $user['avatar'],
                    'follow'   => $follow,
                ];
            }
        }
        return $list;
    }


    /**
     * 获取粉丝列表
     * */
    public static function getFollowerList($user_id, $page, $limit, $login_id)
    {
        $list = Db::name('user_follow')
            ->where('user_id', $user_id)
            ->page($page)
            ->limit($limit)
            ->order('id desc')
            ->select();

        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $user = self::getUser('id', $val['follower_id']);
                $follow = (Db::name('user_follow')
                        ->where('user_id', $val['follower_id'])
                        ->where('follower_id', $login_id)
                        ->count()) > 0;
                $list[$key]['user'] = [
                    'id'       => $user['id'],
                    'nickname' => $user['nickname'],
                    'avatar'   => $user['avatar'],
                    'follow'   => $follow,
                ];
            }
        }
        return $list;
    }

    /**
     * 关注用户
     * */
    public static function followUser($user_id, $login_id)
    {
        $count = Db::name('user_follow')
            ->where('user_id', $user_id)
            ->where('follower_id', $login_id)
            ->count();

        if ($count > 0) {
            $res = Db::name('user_follow')
                ->where('user_id', $user_id)
                ->where('follower_id', $login_id)
                ->delete();
        } else {
            if ($user_id == $login_id) {
                return false;
            }

            $res = Db::name('user_follow')->insert([
                'user_id'     => $user_id,
                'follower_id' => $login_id,
            ]);
        }
        return $res;
    }

    /**
     * 统计用户关注的人数
     * */
    public static function getUserFollowedStatistic($follower_id)
    {
        $count = Db::name('user_follow')
            ->where('follower_id', $follower_id)
            ->count();
        return $count;
    }

    /**
     * 统计关注用户的人数 即粉丝人数
     * */
    public static function getUserFollowerStatistic($user_id)
    {
        $count = Db::name('user_follow')
            ->where('user_id', $user_id)
            ->count();
        return $count;
    }

    public static function getShop($field, $value)
    {
        $info = Db::name('shop')->where($field, $value)->find();
        return $info;
    }

    /**
     * 是否为商户关联用户
     * */
    public static function is_shop($user_id)
    {
        $shop = self::getShop('user_id', $user_id);
        if (!empty($shop) && $shop['audit_state'] == 30) {
            return true;
        }
        return false;
    }

    /**
     * 是否为粉丝
     * */
    public static function is_follower($user_id, $follower_id)
    {
        $count = Db::name('user_follow')
            ->where('user_id', $user_id)
            ->where('follower_id', $follower_id)
            ->count();
        return $count > 0;
    }

    /**
     * 获取店铺所有者
     * */
    public static function getStoreUser($store_id)
    {
        $info = Db::name('store')
            ->alias('store')
            ->join('fa_shop shop', 'shop.id = store.shop_id')
            ->where('store.id', $store_id)
            ->field('shop.user_id')
            ->find();

        $user = [];
        if (isset($info['user_id'])) {
            $user = User::getUser('id', $info['user_id']);
        }
        return $user;
    }

    /**
     * 列表添加用户数据
     * */
    public static function addUserInfo($list, $field = 'user_id')
    {
        foreach ($list as $key => $val) {
            $user = self::getUser('id', $val[$field]);
            $list[$key]['user'] = [
                'id'       => $user['id'],
                'avatar'   => $user['avatar'],
                'nickname' => $user['nickname'],
            ];
        }
        return $list;
    }


    /**
     * 用户关注列表
     * */
    public static function getFollowList($login_id, $page, $limit)
    {
        $list = Db::name('user_follow')
            ->where('follower_id', $login_id)
            ->page($page)
            ->limit($limit)
            ->order('id desc')
            ->select();

        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $user = self::getUser('id', $val['user_id']);
                $list[$key]['user'] = [
                    'id'       => $user['id'],
                    'nickname' => $user['nickname'],
                    'avatar'   => $user['avatar'],
                    'follow'   => true,
                ];
            }
        }
        return $list;
    }


    /**
     * 删除用户关注
     * */
    public static function delUserFollow($ids, $login_id)
    {
        $res = Db::name('user_follow')
            ->where('user_id', 'in',  $ids)
            ->where('follower_id', $login_id)
            ->delete();
        return $res;
    }


    public static function getMsgToIds()
    {
        $ids = Db::name('user')
            ->where('status', 1)
            ->where('forbid_type', 0)
            ->column('id');
        return $ids;
    }

}