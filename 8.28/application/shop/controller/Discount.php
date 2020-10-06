<?php

namespace app\shop\controller;

use app\api\model\Mine;
use think\Config;
use think\Hook;
use think\Response;
use think\Session;
use think\Validate;

/**
 * 特价商品
 */
class Discount extends Base
{
    protected $store_id = 0;

    public function _initialize()
    {
        parent::_initialize();
        //移除HTML标签
        $this->request->filter('trim,strip_tags,htmlspecialchars');

        $this->store_id = Session::get('store.id');
    }


    /**
     * 团购商品
     * */
    public function discountGoods()
    {
        if ($this->request->isPost()) {
            $page = $this->request->param('page');
            $limit = $this->request->param('limit');


            $list = \app\api\model\Discount::getAllGoodsList($page, $limit, [
                'store_id' => $this->store_id
            ]);
            //halt($list);
            $count = \app\api\model\Discount::getAllGoodsCount([
                'store_id' => $this->store_id
            ]);
            return $this->getResponse($list, $count);
        }

        return $this->view->fetch();
    }



}
