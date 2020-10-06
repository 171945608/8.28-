<?php

namespace app\api\controller;

use app\api\model\Vip as Model;
use app\api\model\Cont;
use app\common\controller\Api;


/**
 * VIP
 */
class Vip extends Api
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';

    /**
     * 顶部轮播
     */
    public function getTopCarouselList()
    {
        $list = Cont::getGroupCarouselList('vip_top');
        $this->success('success', [
            'list' => $list
        ]);
    }

    /**
     * 中部轮播
     */
    public function getCenterCarouselList()
    {
        $list = Cont::getGroupCarouselList('vip_center');
        $this->success('success', [
            'list' => $list
        ]);
    }

    /**
     * 分类列表
     */
    public function getCateList()
    {
        $list = Model::getCateList();
        $this->success('success', [
            'list' => $list,
        ]);
    }

    /**
     * 商品列表 推荐
     */
    public function getRecommendedGoodsList()
    {
        $list = Model::getGoodsList(1, 4, [
            'is_recommended' => 1
        ]);
        $list = Model::addFieldForList($list);
        $this->success('success', [
            'list' => $list
        ]);
    }

    /**
     * 商品列表 全部
     */
    public function getGoodsList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $list = Model::getGoodsList($page, $limit);
        $list = Model::addFieldForList($list);
        $this->success('success', [
            'list' => $list
        ]);
    }

    /**
     * 商品列表 分类
     */
    public function getGoodsListByCate()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $vip_cate_id = $this->request->param('vip_cate_id');

        $list = Model::getGoodsList($page, $limit, [
            'vip_cate_id' => $vip_cate_id
        ]);
        $list = Model::addFieldForList($list);
        $this->success('success', [
            'list' => $list
        ]);
    }


    /**
     * 商品详情
     */
    public function getGoods()
    {
        $goods_id = $this->request->param('goods_id');

        $info = Model::getGoods($goods_id);
        $info = Model::addFieldForInfo($info);
        $this->success('success', [
            'info' => $info
        ]);
    }

}
