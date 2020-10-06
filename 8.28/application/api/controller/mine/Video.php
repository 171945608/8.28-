<?php

namespace app\api\controller\mine;

use app\api\model\User;
use app\api\model\Goods;
use app\api\model\Mine;
use app\common\controller\Api;

/**
 * 我的视频
 * */
class Video extends Api
{
    protected $noNeedLogin = [''];
    protected $noNeedRight = '*';

    //视频列表
    public function getVideoList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $login = $this->auth->getUser();
        $list = \app\api\model\Video::getVideoList([
            'user_id' => $login->id
        ], $page, $limit);

        $list = \app\api\model\Video::formatVideoList('default', $list, $login->id);
        $this->success('success', [
            'list' => $list
        ]);
    }

    //删除视频
    public function delVideo()
    {
        $id = $this->request->param('id');

        $login = $this->auth->getUser();
        $res = \app\api\model\Video::delVideo($id, $login->id);

        if (!$res) {
            $this->error("操作失败，请重新尝试。");
        } else {
            $this->success("操作成功");
        }
    }

    //视频足迹
    public function getVideoFoot()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $login = $this->auth->getUser();
        $list = \app\api\model\Video::getVideoFoot($login->id, $page, $limit);
        $list = \app\api\model\Video::formatVideoList('default', $list, $login->id);
        $this->success('success', [
            'list' => $list
        ]);
    }


}