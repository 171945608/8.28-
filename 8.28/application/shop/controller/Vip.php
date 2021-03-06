<?php

namespace app\shop\controller;

use app\api\model\Mine;
use think\Config;
use think\Hook;
use think\Response;
use think\Session;
use think\Validate;

/**
 * VIP
 */
class Vip extends Base
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
     * 商品列表
     * */
    public function goods()
    {
        if ($this->request->isPost()) {
            $page = $this->request->param('page');
            $limit = $this->request->param('limit');

            $list = \app\api\model\Vip::getGoodsList($page, $limit, [
                'store_id' => $this->store_id
            ]);
            $list = \app\api\model\Vip::addFieldForList($list);
            //halt($list);
            $count = \app\api\model\Vip::getGoodsCount([
                'store_id' => $this->store_id
            ]);
            return $this->getResponse($list, $count);
        }

        return $this->view->fetch();
    }



}
