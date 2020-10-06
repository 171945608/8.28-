<?php

namespace app\api\controller\mine;

use app\api\model\Goods;
use app\api\model\Mine;
use app\common\controller\Api;
use app\api\model\Community;

/**
 * 我的收藏
 * */
class Star extends Api
{
    protected $noNeedLogin = [''];
    protected $noNeedRight = '*';

    //话题列表
    public function getTopicList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $login = $this->auth->getUser();
        $tid_arr = Community::getStarredTopicIds($login->id);
//        halt($tid_arr);

        $list = \app\api\model\Community::getTopicList($page, $limit, [
            'id' => ['in', $tid_arr]
        ]);

        $list = \app\api\model\Community::formatTopicList($list, $login->id);

        $this->success('success', [
            'list' => $list
        ]);
    }

    //删除话题收藏
    public function delTopicStar()
    {
        $ids = $this->request->param('ids');

        $login = $this->auth->getUser();
        $res = \app\api\model\Community::delTopicStar($ids, $login->id);

        if (!$res) {
            $this->error("操作失败，请重新尝试。");
        } else {
            $this->success("操作成功");
        }
    }


//商品列表
    public function getGoodsList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $login = $this->auth->getUser();
        $gid_arr = Goods::getStarredGoodsIds($login->id);
//        halt($tid_arr);

        $list = Goods::getGoodsList($page, $limit, [
            'id' => ['in', $gid_arr],
            'audit' => Mine::GOODS_AUDIT_PASS,
            'state' => Mine::GOODS_STATE_UP,
        ]);

        $list = Goods::addFieldForList($list, $login->id);
        $this->success('success', [
            'list' => $list
        ]);
    }

    //删除商品收藏
    public function delGoodsStar()
    {
        $ids = $this->request->param('ids');

        $login = $this->auth->getUser();
        $res = Goods::delGoodsStar($ids, $login->id);

        if (!$res) {
            $this->error("操作失败，请重新尝试。");
        } else {
            $this->success("操作成功");
        }
    }


}