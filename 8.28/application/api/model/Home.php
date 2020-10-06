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

class Home
{
    /**
     * 模块入口
     * */
    public static function getModuleEntryList()
    {
        $list = Db::name('entry')
            ->order('id asc')
            ->select();
        return $list;
    }

    /**
     * 首页分类
     * */
    public static function getHomeGoodsCateList()
    {
        $list = Db::name('goods_cate_system')
            ->where('home', 1)
            //->limit(16 - 3)
            ->order('weigh asc')
            ->select();
        return $list;
    }

    /**
     * 首页商品
     * */
    public static function getHomeGoodsList()
    {
        $list = Db::name('goods')
            ->where('audit', Mine::GOODS_AUDIT_PASS)
            ->where('state', Mine::GOODS_STATE_UP)
            ->where('home', 1)
            ->limit(4)
            ->order('id desc')
            ->field('detail,equities', true)
            ->select();
        return $list;
    }


}