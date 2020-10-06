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

class Express
{
    /**
     * 物流公司列表
     * */
    public static function getExpressList($page, $limit)
    {
        $list = Db::name('express')
            ->order('id desc')
            ->page($page)
            ->limit($limit)
            ->select();
        return $list;
    }




}