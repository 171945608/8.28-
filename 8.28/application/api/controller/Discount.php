<?php

namespace app\api\controller;

use app\api\model\Discount as Model;
use app\api\model\Cont;
use app\common\controller\Api;


/**
 * 特价专区
 */
class Discount extends Api
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';


    /**
     * 顶部轮播
     */
    public function getCarouselList()
    {
        $list = Cont::getGroupCarouselList('discount');
        $this->success('success', ['list' => $list]);
    }

    /**
     * 分类列表
     */
    public function getDiscountCateList()
    {
        $list = Model::getCateList();
        $this->success('success', [
            'list' => $list,
        ]);
    }

    /**
     * 商品列表 无分类
     */
    public function getGoodsList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $list = Model::getGoodsList($page, $limit);
        $this->success('success', ['list' => $list]);
    }

    /**
     * 商品列表 分类
     */
    public function getGoodsListByCate()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $discount_cate_id = $this->request->param('discount_cate_id');

        $list = Model::getGoodsList($page, $limit, [
            'discount_cate_id' => $discount_cate_id
        ]);
        $this->success('success', ['list' => $list]);
    }


    /**
     * 商品详情
     */
    public function getGoods()
    {
        $goods_id = $this->request->param('goods_id');
        $info = Model::getGoods($goods_id);
        $this->success('success', [
            'info' => $info
        ]);
    }

}
