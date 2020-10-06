<?php

namespace app\api\controller;

use app\api\model\CommentReply;
use app\api\model\Cont;
use app\common\controller\Api;
use app\api\model\Video as Model;
use PhpOffice\PhpSpreadsheet\Comment;
use think\Db;

class Video extends Api
{

    protected $noNeedLogin = [
        'getDefaultVideoList', 'getCommentList', 'heartComment', 'searchVideo', 'searchAuthor'
    ];

    protected $noNeedRight = '*';

    /**
     * 发布视频
     * */
    public function addVideo()
    {
        $file = $this->request->param('file');
        $title = $this->request->param('title');
        $content = $this->request->param('content');
        $longitude = $this->request->param('longitude');
        $latitude = $this->request->param('latitude');

        $user = $this->auth->getUser();
        $user->vip_auth != 2 && $this->error('您不是VIP，无权发布视频。');

        $res = Model::addVideo($user->id, $file, $title, $content, $longitude, $latitude);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败，请重试。');
        }
    }


    /**
     * 默认视频 推荐
     * */
    public function getDefaultVideoList()
    {
        $where = [
//            'is_recommended' => 1
        ];
        $list = Model::getVideoList($where, 1, 100);
        $user = $this->auth->getUser();
        $user_id = empty($user) ? 0 : $user->id;
        $list = Model::formatVideoList('default', $list, $user_id);
        $this->success('success', ['list' => $list]);
    }

    /**
     * 点赞视频
     * */
    public function heartVideo()
    {
        $video_id = $this->request->param('id');

        $user = $this->auth->getUser();
        $res = Model::heartVideo($video_id, $user->id);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败，请重试。');
        }
    }


    /**
     * 收藏视频
     * */
    public function starVideo()
    {
        $video_id = $this->request->param('id');

        $user = $this->auth->getUser();
        $res = Model::starVideo($video_id, $user->id);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败，请重试。');
        }
    }


    /**
     * 评论列表
     * */
    public function getCommentList()
    {
        $video_id = $this->request->param('id');
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $list = Model::getCommentList($video_id, $page, $limit);

        $login = $this->auth->getUser();
        $login_id = empty($login) ? 0 : $login->id;
        $list = Model::formatCommentList('common', $list, $video_id, $login_id);

        $total = Model::getCommentCount($video_id);
        $this->success('success', [
            'list' => $list, 'total' => $total
        ]);
    }

    /**
     * 发布评论
     * */
    public function commentVideo()
    {
        $video_id = $this->request->param('id');
        $content = $this->request->param('content');

        $user = $this->auth->getUser();
        $id = $res = Model::commentVideo($content, $video_id, $user->id);

        if ($res) {
            $info = Model::getComment($id, $user->id);
            $info['reply'] = [];
            $this->success('操作成功。', [
                'info' => $info
            ]);
        } else {
            $this->error('操作失败，请重试。');
        }
    }

    /**
     * 点赞评论
     * */
    public function heartComment()
    {
        $comment_id = $this->request->param('id');

        $login = $this->auth->getUser();
        $res = Model::heartComment($comment_id, $login->id);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败，请重试。');
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
        $res = CommentReply::replyComment($comment_id, $content, $login->id, 'video');
        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功。', [
                'info' => CommentReply::getReply($res, $login->id, 'video')
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
        $res = CommentReply::heartReply($reply_id, $login->id, 'video');
        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功。');
        }
    }

    /**
     * 关注作者
     * */
    public function followAuthor()
    {
        $author_id = $this->request->param('id');

        $user = $this->auth->getUser();
        $res = Model::followAuthor($author_id, $user->id);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error('用户无法关注自己');
        }
    }

    /**
     * 作者的关注列表
     * */
    public function getFocusList()
    {
        $author_id = $this->request->param('id');
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $user = $this->auth->getUser();
        $list = \app\api\model\User::getFocusList($author_id, $page, $limit, $user->id);

        $total = \app\api\model\User::getUserFollowedStatistic($author_id);
        $this->success('success', [
            'list' => $list, 'total' => $total
        ]);
    }

    /**
     * 关注作者关注的用户
     * */
    public function followFocus()
    {
        $user_id = $this->request->param('id');

        $user = $this->auth->getUser();
        $res = \app\api\model\User::followUser($user_id, $user->id);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error('用户无法关注自己');
        }
    }

    /**
     * 粉丝列表
     * */
    public function getFollowerList()
    {
        $author_id = $this->request->param('id');
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $user = $this->auth->getUser();
        $list = \app\api\model\User::getFollowerList($author_id, $page, $limit, $user->id);

        $total = \app\api\model\User::getUserFollowerStatistic($author_id);
        $this->success('success', [
            'list' => $list, 'total' => $total
        ]);
    }

    /**
     * 关注粉丝
     * */
    public function followFollower()
    {
        $user_id = $this->request->param('id');

        $user = $this->auth->getUser();
        $res = \app\api\model\User::followUser($user_id, $user->id);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error('用户无法关注自己');
        }
    }

    /**
     * 作者主页
     * */
    public function getAuthor()
    {
        $author_id = $this->request->param('id');

        $user = $this->auth->getUser();
        $info = Model::getAuthorProfile($author_id, $user->id);
        $this->success('success', [
            'info' => $info
        ]);
    }


    /**
     * 作者视频 全部
     * */
    public function getAuthorVideoList()
    {
        $author_id = $this->request->param('id');
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $list = Model::getVideoList(['user_id' => $author_id], $page, $limit);
        $user = $this->auth->getUser();
        $user_id = empty($user) ? 0 : $user->id;
        $list = Model::formatVideoList('author', $list, $user_id);
        $this->success('success', ['list' => $list]);
    }

    /**
     * 作者视频 收藏
     * */
    public function getStarredAuthorVideoList()
    {
        $author_id = $this->request->param('id');
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $user = $this->auth->getUser();
        $video_ids = Db::name('video_star')
            ->where('user_id', $author_id)
            ->column('video_id');

        $where = [
            'id' => ['in', $video_ids],
        ];
        $list = Model::getVideoList($where, $page, $limit);
        $user_id = empty($user) ? 0 : $user->id;
        $list = Model::formatVideoList('author', $list, $user_id);
        $this->success('success', ['list' => $list]);
    }


    /**
     * 视频详情
     * */
    public function getVideo()
    {
        $id = $this->request->param('id');

        $login = $this->auth->getUser();
        $login_id = empty($login) ? 0 : $login->id;

        $info = Model::getVideoDetail($id, $login_id);
        $this->success('success', [
            'info' => $info
        ]);
    }

    /**
     * 视频搜索
     * */
    public function searchVideo()
    {
        $page =  $this->request->param('page');
        $limit =  $this->request->param('limit');
        $word =  $this->request->param('word');

        $list = Model::getVideoList([
            'title' => ['like', "%{$word}%"]
        ], $page, $limit);

        $user = $this->auth->getUser();
        $user_id = empty($user) ? 0 : $user->id;
        $list = Model::formatVideoList('default', $list, $user_id);

        $this->success('success', ['list' => $list]);
    }

    /**
     * 作者搜索
     * */
    public function searchAuthor()
    {
        $page =  $this->request->param('page');
        $limit =  $this->request->param('limit');
        $word =  $this->request->param('word');

        $login = $this->auth->getUser();
        $login_id = empty($login) ? 0 : $login->id;
        $list = Model::searchAuthor($word, $login_id, $page, $limit);

        $this->success('success', ['list' => $list]);
    }

/**
 * 视频足迹
 * */
    public function addVideoFoot()
    {
        $id = $this->request->param('id');

        $login = $this->auth->getUser();
        Model::addVideoFoot($id, $login->id);
        $this->success('success');
    }

}