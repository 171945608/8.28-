<?php

namespace app\shop\controller;

use app\api\model\Mine;
use think\Config;
use think\Hook;
use think\Response;
use think\Session;
use think\Validate;

/**
 * 限时团购
 */
class Groupbuy extends Base
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
    public function groupbuyGoods()
    {
        if ($this->request->isPost()) {
            $page = $this->request->param('page');
            $limit = $this->request->param('limit');

            $list = \app\api\model\Goods::getGroupbuyGoodsList($this->store_id, $page, $limit);
            $count = \app\api\model\Groupbuy::getGroupbuyGoodsCount([
                'store_id' => $this->store_id
            ]);
            return $this->getResponse($list, $count);
        }

        return $this->view->fetch();
    }

    /**
     * 团购申请
     * */
    public function groupbuyApply()
    {
        if ($this->request->isPost()) {
            $page = $this->request->param('page');
            $limit = $this->request->param('limit');

            $list = \app\api\model\Groupbuy::getGroupbuyApplyList($page, $limit, [
                'store_id' => $this->store_id
            ]);
            $count = \app\api\model\Groupbuy::getGroupbuyApplyCount([
                'store_id' => $this->store_id
            ]);
            return $this->getResponse($list, $count);
        }

        return $this->view->fetch();
    }

}
