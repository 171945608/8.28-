<?php

namespace app\api\controller;

use app\api\model\Cate as Model;
use app\api\model\Cont;
use app\common\controller\Api;


/**
 * 分类模块
 */
class Cate extends Api
{
    protected $noNeedLogin = [
        'getCateGoodsList', 'getCateStoreList', 'getCateCarouselList', 'getCateList', 'getGoodsList', 'getGoods',
        'getStore', 'getStoreHomeGoodsList', 'getStoreAllGoodsList', 'getStoreNewGoodsList', 'getGoodsEquities',
        'searchStoreGoods', 'getStoreList'
    ];
    protected $noNeedRight = '*';


    /**
     * 默认商品
     */
    public function getCateGoodsList()
    {
        $list = Model::getCateGoodsList();
        $this->success('success', ['list' => $list]);
    }

    /**
     * 默认店铺
     */
    public function getCateStoreList()
    {
        $list = Model::getCateStoreList();
        $this->success('success', ['list' => $list]);
    }

    /**
     * 顶部轮播
     */
    public function getCateCarouselList()
    {
        $cate_id = $this->request->param('cate_id');
        $list = Cont::getCarouselListByCateId($cate_id);
        $this->success('success', ['list' => $list]);
    }

    /**
     * 平台分类 列表
     */
    public function getCateList()
    {
        $list = Model::getCateList();
        $this->success('success', ['list' => $list]);
    }

    /**
     * 分类商品 详情
     */
    public function getGoods()
    {
        $id = $this->request->param('id');
        $info = Model::getGoods($id);
        $this->success('success', ['info' => $info]);
    }

    /**
     * 分类商品 详情 收藏
     */
    public function getGoodsCollect()
    {
        $id = $this->request->param('id');
        $user = $this->auth->getUser();
        $val = Model::getGoodsCollect($user->id, $id);
        $this->success('success', ['val' => $val]);
    }


    /**
     * 分类商品 详情 服务
     */
    public function getGoodsEquities()
    {
        $id = $this->request->param('id');
        $val = Model::getGoodsEquities($id);
        $this->success('success', ['val' => $val]);
    }

    /**
     * 分类商品 收藏
     */
    public function collectGoods()
    {
        $id = $this->request->param('id');
        $user = $this->auth->getUser();
        Model::collectGoods($user->id, $id);
        $this->success('success');
    }

    /**
     * 分类店铺
     */
    public function getStore()
    {
        $id = $this->request->param('id');
        $info = Model::getStore($id);
        $this->success('success', ['info' => $info]);
    }


    /**
     * 分类店铺 首页
     */
    public function getStoreHomeGoodsList()
    {
        $id = $this->request->param('id');
        $list = Model::getStoreHomeGoodsList($id);
        $this->success('success', ['list' => $list]);
    }

    /**
     * 分类店铺 全部
     */
    public function getStoreAllGoodsList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $id = $this->request->param('id');

        $list = Model::getStoreAllGoodsList($id, $page, $limit);
        $this->success('success', ['list' => $list]);
    }

    /**
     * 分类店铺 上新
     */
    public function getStoreNewGoodsList()
    {
        $id = $this->request->param('id');
        $list = Model::getStoreNewGoodsList($id);
        $this->success('success', ['list' => $list]);
    }


    /**
     * 分类店铺 搜索
     */
    public function searchStoreGoods()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $id = $this->request->param('id');
        $word = $this->request->param('word');

        $list = Model::searchStoreGoods($id, $page, $limit, $word);
        $this->success('success', ['list' => $list]);
    }



    /**
     * 分类商品 列表
     */
    public function getGoodsList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $cate_id = $this->request->param('cate_id');
        $sort = $this->request->param('sort', 0);

        $list = Model::getGoodsList($cate_id, $page, $limit, (int)$sort);
        $list = Model::formatGoodsListOne($list);
        $this->success('success', ['list' => $list]);
    }


    /**
     * 分类店铺 列表
     */
    public function getStoreList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $cate_id = $this->request->param('cate_id');
        $sort = $this->request->param('sort', 0);

        $list = Model::getStoreList($cate_id, $page, $limit, (int)$sort);
        $list = Model::formatGoodsListOne($list);
        $this->success('success', ['list' => $list]);
    }


}
