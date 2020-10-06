<?php

namespace app\api\controller;

use app\api\model\Shop as Model;
use app\api\model\Cont;
use app\common\controller\Api;


/**
 * 商家模块
 */
class Shop extends Api
{
    protected $noNeedLogin = [
        'getStore', 'getStoreAbout', 'getStoreNoticeList', 'getStoreNotice', 'getStoreCarouselList', 'getStoreHomeNewGoodsList'
        , 'getStoreHomeCommendedGoodsList', 'getStoreGoodsList', 'getTopicList', 'getVideoList', 'getStoreList'
    ];
    protected $noNeedRight = '*';

    /**
     * 店铺列表
     */
    public function getStoreList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $list = Model::getStoreList($page, $limit);
        $list = Model::addFieldForStroeList($list);

        $this->success('success', [
            'list' => $list
        ]);
    }


    /**
     * 店铺信息
     */
    public function getStore()
    {
        $id = $this->request->param('id');
        $info = Model::getStore($id);

        $user = $this->auth->getUser();
        $info = Model::getStoreUser($info, empty($user) ? 0 : $user->id);
        $this->success('success', ['info' => $info]);
    }

    /**
     * 关于店铺
     */
    public function getStoreAbout()
    {
        $id = $this->request->param('id');
        $val = Model::getStoreAbout($id);
        $this->success('success', ['val' => $val]);
    }

    /**
     * 店铺公告
     * */
    public function getStoreNoticeList()
    {
        $id = $this->request->param('id');
        $list = Model::getStoreNoticeList($id);
        $this->success('success', ['list' => $list]);
    }

    /**
     * 店铺公告 详情
     * */
    public function getStoreNotice()
    {
        $id = $this->request->param('id');
        $info = Model::getStoreNotice($id);
        $this->success('success', ['info' => $info]);
    }

    /**
     * 店铺轮播
     * */
    public function getStoreCarouselList()
    {
        $id = $this->request->param('id');
        $list = Model::getStoreCarouselList($id);
        $this->success('success', ['list' => $list]);
    }

    /**
     * 店铺首页 新品
     * */
    public function getStoreHomeNewGoodsList()
    {
        $id = $this->request->param('id');
        $list = Model::getStoreHomeNewGoodsList($id);
        $this->success('success', ['list' => $list]);
    }

    /**
     * 店铺首页 推荐
     * */
    public function getStoreHomeCommendedGoodsList()
    {
        $id = $this->request->param('id');
        $list = Model::getStoreHomeCommendedGoodsList($id);
        $this->success('success', ['list' => $list]);
    }

    /**
     * 店铺商品
     * */
    public function getStoreGoodsList()
    {
        $id = $this->request->param('id');
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $list = Model::getStoreGoodsList($id, $page, $limit);
        $this->success('success', ['list' => $list]);
    }


    /**
     * 话题列表
     * */
    public function getTopicList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $store_id = $this->request->param('store_id');

        $user = \app\api\model\User::getStoreUser($store_id);
        $list = \app\api\model\Community::getTopicList($page, $limit, [
            'user_id' => $user['id']
        ]);
        $user = $this->auth->getUser();
        $list = \app\api\model\Community::formatTopicList($list, empty($user) ? 0 : $user->id);
        $this->success('success', ['list' => $list]);
    }

    /**
     * 视频列表
     * */
    public function getVideoList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $store_id = $this->request->param('store_id');

        $user = \app\api\model\User::getStoreUser($store_id);
        $list = \app\api\model\Video::getVideoList(['user_id' => $user['id']], $page, $limit);
        $user = $this->auth->getUser();
        $user_id = empty($user) ? 0 : $user->id;
        $list = \app\api\model\Video::formatVideoList('default', $list, $user_id);
        $this->success('success', ['list' => $list]);
    }

    /**
     * 商铺关注
     * */
    public function followUser()
    {
        $user_id = $this->request->param('user_id');

        $user = $this->auth->getUser();
        $res = \app\api\model\User::followUser($user_id, $user->id);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error('用户无法关注自己');
        }
    }

    /**
     * 店铺搜索
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

}
