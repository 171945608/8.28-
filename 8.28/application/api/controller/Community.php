<?php

namespace app\api\controller;

use app\api\model\CommentReply;
use app\api\model\Cont;
use app\common\controller\Api;
use app\api\model\Community as Model;

class Community extends Api
{

    protected $noNeedLogin = [
        'getCateList', 'getTopTopicList', 'getHotTopicList', 'getCarouselList', 'getRecommendedTopicList',
        'getCommentList', 'getTopic', 'getTopicList', 'getCateTopicList', 'heartComment'
    ];

    protected $noNeedRight = '*';

    /**
     * 分类 列表
     * */
    public function getCateList()
    {
        $list = Model::getCateList();
        $this->success('success', ['list' => $list]);

    }

    /**
     * 话题 列表 置顶
     * */
    public function getTopTopicList()
    {
        $list = Model::getTopTopicList();
        $user = $this->auth->getUser();
        $list = Model::formatTopTopicList($list, empty($user) ? 0 : $user->id);
        $this->success('success', ['list' => $list]);
    }

    /**
     * 话题 列表 热点
     * */
    public function getHotTopicList()
    {
        $list = Model::getHotTopicList();
        $this->success('success', ['list' => $list]);
    }

    /**
     * 轮播 列表
     * */
    public function getCarouselList()
    {
        $list = Cont::getGroupCarouselList('community');
        $this->success('success', ['list' => $list]);
    }

    /**
     * 话题 列表 推荐
     * */
//    public function getRecommendedTopicList()
//    {
//        $list = Model::getRecommendedTopicList();
//        $user = $this->auth->getUser();
//        $list = Model::formatRecommendedTopicList($list, empty($user) ? 0 : $user->id);
//        $this->success('success', ['list' => $list]);
//    }

    /**
     * 话题 详情
     * */
    public function getTopic()
    {
        $id = $this->request->param('id');
        $user = $this->auth->getUser();
        $info = Model::getTopicB($id, empty($user) ? 0 : $user->id);
        $this->success('success', ['info' => $info]);
    }


    /**
     * 评论 列表
     * */
    public function getCommentList()
    {
        $id = $this->request->param('id');
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $login = $this->auth->getUser();
        $login_id = empty($login) ? 0 : $login->id;
        $list = Model::getCommentList($id, $page, $limit, $login_id);
        $total = Model::getCommentCount($id);
        $this->success('success', [
            'list' => $list, 'total' => $total
        ]);
    }

    /**
     * 评论 点赞
     * */
    public function heartComment()
    {
        $id = $this->request->param('id');

        $user = $this->auth->getUser();
        $res = Model::heartComment($id, $user->id);
        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功。');
        }
    }

    /**
     * 回复评论
     * */
    public function replyComment()
    {
        $comment_id = $this->request->param('comment_id');
        $content = $this->request->param('content');

        if (empty($comment_id) || empty($content)) {
            $this->error("参数不全");
        }

        $login = $this->auth->getUser();
        $res = CommentReply::replyComment($comment_id, $content, $login->id, 'topic');
        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功。', [
                'info' => CommentReply::getReply($res, $login->id, 'topic')
            ]);
        }
    }

    /**
     * 点赞回复
     * */
    public function heartReply()
    {
        $reply_id = $this->request->param('reply_id');

        if (empty($reply_id)) {
            $this->error("参数不全");
        }

        $login = $this->auth->getUser();
        $res = CommentReply::heartReply($reply_id, $login->id, 'topic');
        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功。');
        }
    }

    /**
     * 话题 新增
     * */
    public function addTopic()
    {
        $content = $this->request->param('content');
        $images = $this->request->param('images');
        $cate_id = $this->request->param('cate_id');
        $user = $this->auth->getUser();
        $res = Model::addTopic($content, $images, $cate_id, $user->id);
        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功。');
        }
    }

    /**
     * 评论 新增
     * */
    public function addComment()
    {
        $content = $this->request->param('content');
        $topic_id = $this->request->param('id');
        $user = $this->auth->getUser();
        $id = $res = Model::addComment($content, $topic_id, $user->id);
        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $info = Model::getComment($id, $user->id);
            $info['reply'] = [];
            $this->success('操作成功。', [
                'info' => $info
            ]);
        }
    }

    /**
     * 话题 收藏/取消
     * */
    public function starTopic()
    {
        $topic_id = $this->request->param('id');
        $user = $this->auth->getUser();
        $res = Model::starTopic($topic_id, $user->id);
        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功。');
        }
    }

    /**
     * 话题 点赞/取消
     * */
    public function heartTopic()
    {
        $topic_id = $this->request->param('id');
        $user = $this->auth->getUser();
        $res = Model::heartTopic($topic_id, $user->id);
        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功。');
        }
    }

    /**
     * 作者 关注/取消
     * */
    public function followUser()
    {
        $user_id = $this->request->param('id');
        $user = $this->auth->getUser();
        $res = Model::followUser($user_id, $user->id);
        if (!$res) {
            $this->error('用户无法关注自己');
        } else {
            $this->success('操作成功。');
        }
    }

    /**
     * 话题 举报
     * */
    public function reportTopic()
    {
        $topic_id = $this->request->param('id');
        $content = $this->request->param('content');
        $user = $this->auth->getUser();
        $res = Model::reportTopic($content, $topic_id, $user->id);
        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功。');
        }
    }

    /**
     * 话题 列表
     * */
    public function getTopicList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $list = Model::getTopicList($page, $limit);
        $user = $this->auth->getUser();
        $list = Model::formatTopicList($list, empty($user) ? 0 : $user->id);
        $this->success('success', ['list' => $list]);
    }

    /**
     * 话题 列表 分类
     * */
    public function getCateTopicList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $id = $this->request->param('id');
        $list = Model::getCateTopicList($id, $page, $limit);
        $user = $this->auth->getUser();
        $list = Model::formatTopicList($list, empty($user) ? 0 : $user->id);
        $this->success('success', ['list' => $list]);
    }

}