<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Config;
use think\Db;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        //四总 总用户 总会员 总vip 总店铺
        $totalUser = Db::name('user')->where([])->count();
        $totalMember = Db::name('user')->where(['id_auth' => 2])->count();
        $totalVip = Db::name('user')->where(['vip_auth' => 2])->count();
        $totalStore = Db::name('store')->where([])->count();

        //四商 总商品数 团购商品数 特价商品数 vip商品数
        $goodsCount = Db::name('goods')
            ->where(['audit' => 2, 'state' => 1])
            ->count();

        $groupbuyGoodsCount = Db::name('groupbuy_goods')->alias('gg')
            ->join('goods g', 'g.id = gg.goods_id')
            ->where([
                'g.audit'  => 2,
                'g.state' => 1
            ])
            ->count();

        $discountGoodsCount = Db::name('discount_goods')->alias('dg')
            ->join('goods g', 'g.id = dg.goods_id')
            ->where([
                'g.audit'  => 2,
                'g.state' => 1
            ])
            ->count();

        $vipGoodsCount = Db::name('vip_goods')->alias('vg')
            ->join('goods g', 'g.id = vg.goods_id')
            ->where([
                'g.audit'  => 2,
                'g.state' => 1
            ])
            ->count();

        //会员 七日统计
        $date_arr = [];
        $ts = strtotime(date('Y-m-d'));
        for ($i = 0; $i < 7; $i++) {
            $ts = strtotime("-1 day", $ts);
            array_push($date_arr, date('Y-m-d', $ts));
        }
        sort($date_arr);
        //halt($date_arr);

        $pieData = [];
        $stat_arr = [];
        foreach ($date_arr as $k => $v) {
            $sts = strtotime($v);
            $ets = strtotime("+1 day", $sts);
            $stat = Db::name('user')
                ->where('createtime', 'between', "{$sts}, {$ets}")
                ->count();
            $stat_arr[$k] = $stat;
            array_push($pieData, [
                'value' => $stat, 'name' => $v
            ]);
        }
        //halt($pieData);

        $this->view->assign([
            'totalUser'   => $totalUser,
            'totalMember' => $totalMember,
            'totalVip'    => $totalVip,
            'totalStore'  => $totalStore,

            'goodsCount'         => $goodsCount,
            'groupbuyGoodsCount' => $groupbuyGoodsCount,
            'discountGoodsCount' => $discountGoodsCount,
            'vipGoodsCount'      => $vipGoodsCount,

            'date' => implode(',', array_map(function($date) {
                return "'{$date}'";
            }, $date_arr)),
            'stat' => implode(',', $stat_arr),
            'pieData' => json_encode($pieData),
        ]);

        return $this->view->fetch();
    }

}
