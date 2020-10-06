<?php

namespace app\shop\controller;

use app\api\model\Mine;
use app\api\model\Shop;
use think\Config;
use think\Hook;
use think\Response;
use think\Session;
use think\Validate;

/**
 * Video
 */
class Video extends Base
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
     * 视频列表
     * */
    public function videos()
    {
        if ($this->request->isPost()) {
            $page = $this->request->param('page');
            $limit = $this->request->param('limit');

            $user = Shop::getStoreUserByStoreId($this->store_id);
            $list = \app\api\model\Video::getVideoList([
                'user_id' => $user['id']
            ],$page, $limit);

            //halt($list);
            $count = \app\api\model\Video::getVideoCount([
                'user_id' => $user['id']
            ]);
            return $this->getResponse($list, $count);
        }

        return $this->view->fetch();
    }


    /**
     * 删除视频
     * */
    public function delVideo()
    {
        if ($this->request->isPost()) {
            $id = $this->request->param('id');

            $user = Shop::getStoreUserByStoreId($this->store_id);
            $res = \app\api\model\Video::delVideo($id, $user['id']);

            if (!$res) {
                $this->error("操作失败，请重新尝试。");
            } else {
                $this->success("操作成功");
            }


        }

    }

    /**
     * 播放视频
     * */
    public function playVideo()
    {
        $id = $this->request->param('id');
        $video = \app\api\model\Video::getVideo($id);
        $this->assign('info', $video);
        return $this->view->fetch();
    }



}
