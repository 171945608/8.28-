<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\api\model\Message as Model;
use think\Db;

/**
 * 消息
 */
class Message extends Api
{
    protected $noNeedLogin = [''];

    protected $noNeedRight = '*';

    /**
     * 私聊列表
     */
    public function getChatUserList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $user = $this->auth->getUser();
        $list = Model::getChatUserList($user->id, $page, $limit);
        $this->success('success', [
            'list' => $list
        ]);
    }

    /**
     * 私聊记录
     */
    public function getChatList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $ano_id = $this->request->param('id');

        $user = $this->auth->getUser();
        $list = Model::getChatList($user->id, $ano_id, $page, $limit);
        $this->success('success', [
            'list' => $list
        ]);
    }


    /**
     * 获取用户昵称、头像
     * */
    public function getUserAvatar()
    {
        $id = $this->request->param('id');
        $user = \app\api\model\User::getUser('id', $id);

        $info = [];
        if (!empty($user)) {
            $info = [
                'id' => $user['id'],
                'nickname' => $user['nickname'],
                'avatar' => $user['avatar']
            ];
        }

        $this->success('', [
            'info' => $info
        ]);
    }

    /**
     * 删除私聊列表
     * */
    public function delUserChat()
    {
        $user_id = $this->request->param('user_id');

        $login = $this->auth->getUser();
        $res = Db::name('chat_msg_id')
            ->where('user_id', $login->id)
            ->where('ano_id', $user_id)
            ->delete();

        if (!$res) {
            $this->error("操作失败");
        } else {
            $this->success("操作成功");
        }
    }

}
