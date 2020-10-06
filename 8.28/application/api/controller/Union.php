<?php

namespace app\api\controller;

use app\api\model\Cont;
use app\api\model\WorkSupply;
use app\api\model\WorkFind;
use app\common\controller\Api;

/**
 * 消息
 */
class Union extends Api
{
    protected $noNeedLogin = [
        'getCarouselList', 'getWorkSupplyList', 'getWorkFindList'
    ];

    protected $noNeedRight = '*';

    /**
     * 顶部轮播
     * */
    public function getCarouselList()
    {
        $list = Cont::getGroupCarouselList('union');
        $this->success('success', ['list' => $list]);
    }

    /**
     * 供活列表
     * */
    public function getWorkSupplyList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $list = WorkSupply::getWorkSupplyList($page, $limit, [
            'end_time' => ['>', time()]
        ]);
        $this->success('success', [
            'list' => \app\api\model\User::addUserInfo($list)
        ]);
    }


    /**
     * 找活列表
     * */
    public function getWorkFindList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $list = WorkFind::getWorkFindList($page, $limit, $where = [
            'begin_time' => ['>', time()],
        ]);
        $this->success('success', [
            'list' => \app\api\model\User::addUserInfo($list)
        ]);
    }
}
