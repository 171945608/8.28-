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

class Cont
{
    //轮播图常量
    const CAROUSEL_LIMIT = 10;

    //广告位常量
    const POSITION_LIMIT = 5;


    public static function formatActivityList($list)
    {
        foreach ($list as $key => $val) {
            $list[$key]['link'] = 'type=activity&id=' . $val['id'];
        }
        return $list;
    }

    public static function formatAdList($list)
    {
        foreach ($list as $key => $val) {
            $list[$key]['link'] = 'type=ad&id=' . $val['id'];
        }
        return $list;
    }

    public static function formatGoodsList($list)
    {
        foreach ($list as $key => $val) {
            $list[$key]['link'] = 'type=goods&id=' . $val['id'];
        }
        return $list;
    }

    /**
     * 轮播图分组 数量
     * */
    public static function getGroupCarouselCount($group)
    {
        $count = Db::name('carousel')
            ->where('group', $group)
            ->count();
        return $count;
    }

    /**
     * 广告位分组 数量
     * */
    public static function getGroupPositionCount($group)
    {
        $count = Db::name('carousel')
            ->where('group', $group)
            ->count();
        return $count;
    }

    /**
     * 轮播图分组 列表
     * */
    public static function getGroupCarouselList($group)
    {
        $list = Db::name('carousel')
            ->where('group', $group)
            ->order('weigh asc')
            ->select();
        return $list;
    }

    /**
     * 分类轮播图
     * */
    public static function getCarouselListByCateId($cate_id)
    {
        if (!$cate_id) {
            $cate_id = Cate::getDefaultCateId();
        }
        $list = Db::name('carousel')
            ->where('group', 'cate')
            ->where('cate_id', $cate_id)
            ->order('weigh asc')
            ->select();
        return $list;
    }

    /**
     * 广告位分组 列表
     * */
    public static function getGroupPositionList($group)
    {
        $list = Db::name('position')
            ->where('group', $group)
            ->order('weigh asc')
            ->select();
        return $list;
    }

    /**
     * 广告位分组 列表
     * */
    public static function getGroupPositionListByTime($group)
    {
        $now = time();
        $list = Db::name('position')
            ->where('group', $group)
            ->where('start_time', '<', $now)
            ->where('end_time', '>', $now)
            ->order('weigh asc')
            ->select();
        return $list;
    }

    /**
     * 广告详情
     * */
    public static function getAd($id)
    {
        $info = Db::name('advertisement')
            ->where('id', $id)
            ->find();

        if (!empty($info)) {
            $now = time();
            Db::name('advertisement_click')->insert([
                'ad_id' => $info['id'],
                'ad_title' => $info['title'],
                'createtime' => $now,
            ]);
        }
        return $info;
    }

    /**
     * 活动详情
     * */
    public static function getActivity($id)
    {
        $info = Db::name('activity')
            ->where('id', $id)
            ->find();
        return $info;
    }

}