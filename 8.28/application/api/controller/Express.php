<?php

namespace app\api\controller;

use app\api\model\Cont;
use app\api\model\Delivery;
use app\common\controller\Api;
use app\api\model\Express as Model;

/**
 * 消息
 */
class Express extends Api
{
    protected $noNeedLogin = [
        'getCarouselList', 'getExpressList', 'getDeliveryList'
    ];

    protected $noNeedRight = '*';

    /**
     * 顶部轮播
     * */
    public function getCarouselList()
    {
        $list = Cont::getGroupCarouselList('express');
        $this->success('success', ['list' => $list]);
    }

    /**
     * 物流公司列表
     * */
    public function getExpressList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $list = Model::getExpressList($page, $limit);
        $this->success('success', [
            'list' => $list
        ]);
    }


    /**
     * 发货列表
     * */
    public function getDeliveryList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $list = Delivery::getDeliveryList($page, $limit, $where = [
            'delivery_time' => ['>', time()],
        ]);
        $this->success('success', [
            'list' => \app\api\model\User::addUserInfo($list)
        ]);
    }
}
