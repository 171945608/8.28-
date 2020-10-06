<?php

namespace app\api\controller;

use app\api\model\Search as Model;
use app\common\controller\Api;


/**
 * 搜索
 */
class Search extends Api
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';


    /**
     * 预置关键词
     */
    public function getPreparedKeywords()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $group = $this->request->param('group');

        $list = Model::getKeywordsList($page, $limit, [
            'group' => $group
        ]);

        $this->success('success', [
            'list' => $list
        ]);
    }

    /**
     * 热搜关键词
     */
    public function getHotKeywords()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $group = $this->request->param('group');

        $list = Model::getSearchListSortedByTimes($page, $limit, [
            'group' => $group
        ]);

        $this->success('success', [
            'list' => $list
        ]);
    }

    /**
     * 商品搜索
     * */
    public function searchGoods()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $word = $this->request->param('word');

        $list = Model::searchGoods($page, $limit, $word);
        $this->success('success', [
            'list' => $list
        ]);
    }


    /**
     * 供应商搜索
     * */
    public function searchStore()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $word = $this->request->param('word');

        $list = Model::searchStore($page, $limit, $word);
        $this->success('success', [
            'list' => $list
        ]);
    }


    /**
     * 限时团购搜索
     * */
    public function searchGroupbuy()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $word = $this->request->param('word');

        $list = Model::searchGroupbuy($page, $limit, $word);
        $this->success('success', [
            'list' => $list
        ]);
    }

    /**
     * 招标竞价搜索
     * */
    public function searchPurchase()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $word = $this->request->param('word');

        $list = Model::searchPurchase($page, $limit, $word);
        $this->success('success', [
            'list' => $list
        ]);
    }

    /**
     * 特价尾货搜索
     * */
    public function searchDiscount()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $word = $this->request->param('word');

        $list = Model::searchDiscount($page, $limit, $word);
        $this->success('success', [
            'list' => $list
        ]);
    }


    /**
     * 工人联盟搜索
     * */
    public function searchUnion()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $word = $this->request->param('word');

        $list = Model::searchUnion($page, $limit, $word);
        $this->success('success', [
            'list' => $list
        ]);
    }


    /**
     * 物流公司搜索
     * */
    public function searchExpress()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $word = $this->request->param('word');

        $list = Model::searchExpress($page, $limit, $word);
        $this->success('success', [
            'list' => $list
        ]);
    }


    /**
     * VIP搜索
     * */
    public function searchVip()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $word = $this->request->param('word');

        $list = Model::searchVip($page, $limit, $word);
        $this->success('success', [
            'list' => $list
        ]);
    }

    /**
     * 话题搜索
     * */
    public function searchTopic()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $word = $this->request->param('word');

        $login = $this->auth->getUser();
        $list = Model::searchTopic($page, $limit, $word, $login ? $login->id : 0);
        $this->success('success', [
            'list' => $list
        ]);
    }

}
