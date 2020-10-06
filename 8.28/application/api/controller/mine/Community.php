<?php

namespace app\api\controller\mine;

use app\api\model\User;
use app\api\model\Goods;
use app\api\model\Mine;
use app\common\controller\Api;

/**
 * 我的社区
 * */
class Community extends Api
{
    protected $noNeedLogin = [''];
    protected $noNeedRight = '*';

    //话题列表
    public function getTopicList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $login = $this->auth->getUser();
        $list = \app\api\model\Community::getTopicList($page, $limit, [
            'user_id' => $login->id,
        ]);

        $list = \app\api\model\Community::formatTopTopicList($list, $login->id);
        $this->success('success', [
            'list' => $list
        ]);
    }

    //删除话题
    public function delTopic()
    {
        $id = $this->request->param('id');

        $login = $this->auth->getUser();
        $res = \app\api\model\Community::delTopic($id, $login->id);

        if (!$res) {
            $this->error("操作失败，请重新尝试。");
        } else {
            $this->success("操作成功");
        }
    }


}