<?php

namespace app\api\controller\mine;

use app\api\model\User;
use app\api\model\Goods;
use app\api\model\Mine;
use app\common\controller\Api;
use app\api\model\Community;

/**
 * 我的关注
 * */
class Follow extends Api
{
    protected $noNeedLogin = [''];
    protected $noNeedRight = '*';

    //关注列表
    public function getFollowList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $login = $this->auth->getUser();
        $list = User::getFollowList($login->id, $page, $limit);

        $this->success('success', [
            'list' => $list
        ]);
    }

    //删除话题收藏
    public function delUserFollow()
    {
        $ids = $this->request->param('ids');

        $login = $this->auth->getUser();
        $res = User::delUserFollow($ids, $login->id);

        if (!$res) {
            $this->error("操作失败，请重新尝试。");
        } else {
            $this->success("操作成功");
        }
    }


}