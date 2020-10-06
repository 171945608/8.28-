<?php

namespace app\api\controller\mine;

use app\api\model\Goods;
use app\api\model\Mine;
use app\common\controller\Api;
use app\api\model\Community;

/**
 * 我的点赞
 * */
class Heart extends Api
{
    protected $noNeedLogin = [''];
    protected $noNeedRight = '*';

    //话题列表
    public function getTopicList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $login = $this->auth->getUser();
        $tid_arr = Community::getHeartedTopicIds($login->id);
//        halt($tid_arr);

        $list = \app\api\model\Community::getTopicList($page, $limit, [
            'id' => ['in', $tid_arr]
        ]);

        $list = \app\api\model\Community::formatTopicList($list, $login->id);

        $this->success('success', [
            'list' => $list
        ]);
    }

    //删除话题点赞
    public function delTopicHeart()
    {
        $ids = $this->request->param('ids');

        $login = $this->auth->getUser();
        $res = \app\api\model\Community::delTopicHeart($ids, $login->id);

        if (!$res) {
            $this->error("操作失败，请重新尝试。");
        } else {
            $this->success("操作成功");
        }
    }




}