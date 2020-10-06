<?php

namespace app\api\controller;

use app\api\model\Home as Model;
use app\api\model\Cont;
use app\common\controller\Api;
use think\Db;


/**
 * 首页模块
 */
class Home extends Api
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';


    /**
     * 首页轮播图 顶部
     */
    public function getHomeOneCarouselList () {
        $list = Cont::getGroupCarouselList('home_one');
        $this->success('success', ['list' => $list]);
    }

    /**
     * 首页轮播图 底部
     */
    public function getHomeTwoCarouselList () {
        $list = Cont::getGroupCarouselList('home_two');
        $this->success('success', ['list' => $list]);
    }

    /**
     * 首页广告位 上部
     */
    public function getHomeOnePositionList () {
        $list = Cont::getGroupPositionListByTime('home_one');
        $this->success('success', ['list' => $list]);
    }

    /**
     * 首页广告位 下部
     */
    public function getHomeTwoPositionList () {
        $list = Cont::getGroupPositionListByTime('home_two');
        $this->success('success', ['list' => $list]);
    }

    /**
     * 模块入口
     */
    public function getModuleEntryList() {
        $list = Model::getModuleEntryList();
        $this->success('success', ['list' => $list]);
    }

    /**
     * 首页分类
     */
    public function getHomeGoodsCateList() {
        $list = Model::getHomeGoodsCateList();
        $this->success('success', ['list' => $list]);
    }

    /**
     * 首页商品
     */
    public function getHomeGoodsList() {
        $list = Model::getHomeGoodsList();
        $list = \app\api\model\Cate::formatGoodsListOne($list);
        $this->success('success', ['list' => $list]);
    }

    /**
     * 广告详情
     */
    public function getAd() {
        $id = $this->request->param('id');
        $info = Cont::getAd($id);
        $this->success('success', ['info' => $info]);
    }

    /**
     * 活动详情
     */
    public function getActivity() {
        $id = $this->request->param('id');
        $info = Cont::getActivity($id);
        $this->success('success', ['info' => $info]);
    }


    /**
     * 话题列表
     * */
    public function getTopicList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $list = \app\api\model\Community::getTopicList($page, $limit, [
            'is_home' => 1
        ]);
        $user = $this->auth->getUser();
        $list = \app\api\model\Community::formatTopicList($list, empty($user) ? 0 : $user->id);
        $this->success('success', ['list' => $list]);
    }


    public function getMsgCount()
    {
        $login = $this->auth->getUser();
        if (empty($login)) {
            $this->success('', [
                'count' => 0
            ]);
        }

        $map = [
            'user_id' => $login->id,
            'is_read' => 0
        ];
        $c1 = Db::name('msg_groupbuy')->where($map)->count();
        $c2 = Db::name('msg_discount')->where($map)->count();
        $c3 = Db::name('msg_purchase')->where($map)->count();
        $this->success('', [
            'count' => $c1 + $c2 + $c3
        ]);
    }
}
