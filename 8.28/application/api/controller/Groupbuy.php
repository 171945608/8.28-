<?php

namespace app\api\controller;

use app\api\model\Groupbuy as Model;
use app\api\model\Cont;
use app\common\controller\Api;


/**
 * 限时团购
 */
class Groupbuy extends Api
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';


    /**
     * 顶部轮播
     */
    public function getCarouselList()
    {
        $list = Cont::getGroupCarouselList('groupbuy');
        $this->success('success', ['list' => $list]);
    }

    /**
     * 商品列表
     */
//    public function getGoodsList()
//    {
//        $page = $this->request->param('page');
//        $limit = $this->request->param('limit');
//
//        $list = Model::getGoingGoodsList($page, $limit);
//        $this->success('success', [
//            'list' => $list
//        ]);
//    }

    /**
     * 活动列表
     */
    public function getGroupbuyList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $list = Model::getGoingGroupbuys($page, $limit);
        $this->success('success', [
            'list' => $list
        ]);
    }

    //报名页商品列表
    public function getGroupbuyGoodsList()
    {
        $groupbuy_id = $this->request->param('groupbuy_id');
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $list = Model::getGoodsListByGroupbuy($groupbuy_id, $page, $limit);
        $this->success('success', [
            'list' => $list,
            'info' => ['id' => $groupbuy_id]
        ]);
    }

    /**
     * 活动详情
     */
    public function getGroupbuy()
    {
        $groupbuy_id = $this->request->param('groupbuy_id');

        $info = Model::getGoingGroupbuy($groupbuy_id);
        $this->success('success', [
            'info' => $info
        ]);
    }

    /**
     * 店铺列表
     */
//    public function getStoreList()
//    {
//        $page = $this->request->param('page');
//        $limit = $this->request->param('limit');
//        $groupbuy_id = $this->request->param('groupbuy_id');
//
//        $list = Model::getStoreList($page, $limit, $groupbuy_id);
//        $info = \app\api\model\Groupbuy::getGroupbuy($groupbuy_id);
//        $this->success('success', [
//            'list' => $list,
//            'info' => $info,
//        ]);
//    }

    /**
     * 商品详情
     */
//    public function getGoods()
//    {
//        $goods_id = $this->request->param('goods_id');
//        $info = Model::getGoods($goods_id);
//        $this->success('success', [
//            'info' => $info
//        ]);
//    }


    /**
     * 店铺团购商品列表
     */
//    public function getStoreGoodsList()
//    {
//        $store_id = $this->request->param('store_id');
//        $groupbuy_id = $this->request->param('groupbuy_id');
//
//        $list = Model::getStoreGoodsList($store_id, $groupbuy_id);
//        $this->success('success', [
//            'list' => $list,
//            'info' => ['id' => $groupbuy_id]
//        ]);
//    }

    /**
     * 团购报名
     */
    public function applyGroupbuy()
    {
        $realname = $this->request->param('realname');
        $mobile = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');

        if (!\app\common\library\Sms::check($mobile, $captcha, 'groupbuy')) {
            $this->error("短信码错误");
        }

        $groupbuy_id = $this->request->param('groupbuy_id');
        $cart = $_POST['cart'];
        $cart = json_decode($cart, true);
        if (empty($cart)) {
            $this->error("商品数据错误");
        }

        $login = $this->auth->getUser();
        $res = Model::applyGroupbuy($realname, $mobile, $cart, $login->id, $groupbuy_id);

        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败，请重试。');
        }
    }

}
