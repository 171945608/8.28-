<?php
/**
 * @author 见龙在野
 * @date 2020-08-08
 */

namespace app\api\controller\mine;

use app\api\model\Goods;
use app\api\model\Shop;
use app\common\controller\Api;
use think\Db;

/**
 * 我的团购
 * */
class Groupbuy extends Api
{
    protected $noNeedLogin = [''];
    protected $noNeedRight = '*';

    //我报名的团购活动
    public function getGroupbuys()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $login = $this->auth->getUser();

        $list = Db::name('groupbuy_apply')->alias('ga')
            ->join('fa_groupbuy g', 'g.id=ga.groupbuy_id')
            ->where('ga.user_id', $login->id)
            ->page($page)
            ->limit($limit)
            ->order('ga.id desc')
            ->field('g.id,g.title,g.tag,g.image,g.images,g.start_time,g.end_time,g.status,g.createtime,ga.createtime as time,ga.id as apply_id')
            ->select();

        if ($list) {
            foreach ($list as $key => $val) {
                $list[$key]['countdown'] = \app\api\model\Groupbuy::getCountdown($val['end_time']);
                $list[$key]['apply'] = \app\api\model\Groupbuy::getApplyCount($val['id']);
            }
        }

        //halt($list);
        $this->success("", [
            'list' => $list
        ]);
    }

    //我报名的团购商品
//    public function getGroupbuysWithGoods()
//    {
//        $apply_id = $this->request->param('apply_id');
//
//        $apply = Db::name('groupbuy_apply')
//            ->where('id', $apply_id)
//            ->find();
//
//        $store = Shop::getStore($apply['store_id']);
//
//        $goods = json_decode($apply['goods'], true);
//        $goods_id_array = [];
//        foreach ($goods as $val) {
//            array_push($goods_id_array, $val['goods_id']);
//        }
//
//        $goods_list = Goods::getGoodsList(1, 999, [
//            'id' => ['in', $goods_id_array],
//        ]);
//        $store['goods'] = $goods_list;
//
//        $this->success("", [
//            'list' => [
//                $store
//            ]
//        ]);
//    }

}