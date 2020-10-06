<?php

namespace app\api\model;

use think\Db;
use think\Exception;

class Community
{
    /**
     * 分类 列表
     * */
    public static function getCateList()
    {
        $list = Db::name('topic_cate')
            ->order('weigh', 'asc')
            ->select();
        return $list;
    }

    /**
     * 话题 列表 置顶
     * */
    public static function getTopTopicList()
    {
        $list = Db::name('topic')
            ->where('is_top', 1)
            ->order('id', 'desc')
            ->limit(2)
            ->select();
        return $list;
    }

    public static function getFollow($user_id, $login_id)
    {
        if (empty($login_id)) {
            $val = false;
        } else {
            $val = (Db::name('user_follow')
                    ->where('user_id', $user_id)
                    ->where('follower_id', $login_id)
                    ->count()) > 0;
        }
        return $val;
    }

    public static function getStar($topic_id, $login_id)
    {
        if (empty($login_id)) {
            $val = false;
        } else {
            $val = (Db::name('topic_star')
                    ->where('user_id', $login_id)
                    ->where('topic_id', $topic_id)
                    ->count()) > 0;
        }
        return $val;
    }

    public static function getHeart($topic_id, $login_id)
    {
        if (empty($login_id)) {
            $val = false;
        } else {
            $val = (Db::name('topic_heart')
                    ->where('user_id', $login_id)
                    ->where('topic_id', $topic_id)
                    ->count()) > 0;
        }
        return $val;
    }

    /**
     * 置顶列表格式
     * */
    public static function formatTopTopicList($list, $user_id)
    {
        if (!empty($list)) {
            foreach ($list as $key => $val) {

                $list[$key]['star'] = self::getStar($val['id'], $user_id);
                $list[$key]['heart'] = self::getHeart($val['id'], $user_id);

                $user = User::getUser('id', $val['user_id']);
                $list[$key]['user'] = [
                    'id'       => $user['id'],
                    'nickname' => $user['nickname'],
                    'avatar'   => $user['avatar'],
                    'follow'   => self::getFollow($val['user_id'], $user_id),
                ];

                $list[$key]['stat'] = [
                    'star'    => Db::name('topic_star')->where('topic_id', $val['id'])->count(),
                    'heart'   => Db::name('topic_heart')->where('topic_id', $val['id'])->count(),
                    'comment' => Db::name('topic_comment')->where('topic_id', $val['id'])->count(),
                ];

                $cl = Db::name('topic_comment')
                    ->where('topic_id', $val['id'])
                    ->order('id', 'desc')
                    ->limit(5)
                    ->select();
                foreach ($cl as $k => $v) {
                    $cu = User::getUser('id', $v['user_id']);
                    $cl[$k]['user'] = [
                        'id'       => $cu['id'],
                        'nickname' => $cu['nickname'],
                    ];
                }
                $list[$key]['comment'] = $cl;
            }
        }
        return $list;
    }

    /**
     * 话题 列表 热点
     * */
    public static function getHotTopicList()
    {
        $sql = "SELECT tc.topic_id,count(*) as num FROM fa_topic t JOIN fa_topic_comment tc ON tc.topic_id=t.id GROUP BY tc.topic_id ORDER BY num DESC LIMIT 6";
        $stat = Db::query($sql);

        $list = [];
        foreach ($stat as $key => $val) {
            $list[] = self::getTopic($val['topic_id']);
        }
        return $list;
    }

    public static function getTopic($id)
    {
        return Db::name('topic')->where('id', $id)->find();
    }

    /**
     * 话题 列表 推荐
     * */
    public static function getRecommendedTopicList()
    {
        $list = Db::name('topic')
            ->where('is_recommended', 1)
            ->order('id', 'desc')
            ->limit(4)
            ->select();
        return $list;
    }

    public static function formatRecommendedTopicList($list, $user_id)
    {
        foreach ($list as $key => $val) {
            $user = User::getUser('id', $val['user_id']);
            if ($user_id == 0) {
                $follow = false;
            } else {
                $follow = (Db::name('user_follow')
                        ->where('user_id', $val['user_id'])
                        ->where('follower_id', $user_id)
                        ->count()) > 0;
            }
            $list[$key]['user'] = [
                'id'       => $user['id'],
                'nickname' => $user['nickname'],
                'avatar'   => $user['avatar'],
                'follow'   => $follow,
            ];
        }

        return $list;
    }

    /**
     * 话题 列表
     * */
    public static function getTopicList($page, $limit, $where = [])
    {
        $list = Db::name('topic')
            ->where($where)
            ->order('id', 'desc')
            ->page($page)
            ->limit($limit)
            ->select();
        return $list;
    }

    public static function formatTopicList($list, $user_id)
    {
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $list[$key]['star'] = self::getStar($val['id'], $user_id);
                $list[$key]['heart'] = self::getHeart($val['id'], $user_id);

                $user = User::getUser('id', $val['user_id']);
                if ($user_id == 0) {
                    $follow = false;
                } else {
                    $follow = (Db::name('user_follow')
                            ->where('user_id', $val['user_id'])
                            ->where('follower_id', $user_id)
                            ->count()) > 0;
                }
                $list[$key]['user'] = [
                    'id'       => $user['id'],
                    'nickname' => $user['nickname'],
                    'avatar'   => $user['avatar'],
                    'follow'   => $follow,
                ];

                $list[$key]['stat'] = [
                    'star'    => Db::name('topic_star')->where('topic_id', $val['id'])->count(),
                    'heart'   => Db::name('topic_heart')->where('topic_id', $val['id'])->count(),
                    'comment' => Db::name('topic_comment')->where('topic_id', $val['id'])->count(),
                ];
            }
        }

        return $list;
    }

    /**
     * 话题 列表 分类
     * */
    public static function getCateTopicList($id, $page, $limit)
    {
        $list = Db::name('topic')
            ->where('cate_id', $id)
            ->order('id', 'desc')
            ->page($page)
            ->limit($limit)
            ->select();
        return $list;
    }


    /**
     * 话题 详情
     * */
    public static function getTopicB($id, $user_id)
    {
        $info = Db::name('topic')->where('id', $id)->find();

        $info['star'] = self::getStar($info['id'], $user_id);
        $info['heart'] = self::getHeart($info['id'], $user_id);

        $user = User::getUser('id', $info['user_id']);
        if ($user_id == 0) {
            $follow = false;
        } else {
            $follow = (Db::name('user_follow')
                    ->where('user_id', $info['user_id'])
                    ->where('follower_id', $user_id)
                    ->count()) > 0;
            self::incTV($user_id);
        }
        $info['user'] = [
            'id'       => $user['id'],
            'nickname' => $user['nickname'],
            'avatar'   => $user['avatar'],
            'follow'   => $follow,
        ];

        $info['stat'] = [
            'star'    => Db::name('topic_star')->where('topic_id', $info['id'])->count(),
            'heart'   => Db::name('topic_heart')->where('topic_id', $info['id'])->count(),
            'comment' => Db::name('topic_comment')->where('topic_id', $info['id'])->count(),
        ];

        $cl = Db::name('topic_comment')
            ->where('topic_id', $info['id'])
            ->order('id', 'desc')
            ->limit(5)
            ->select();
        foreach ($cl as $k => $v) {
            $cu = User::getUser('id', $v['user_id']);
            $cl[$k]['user'] = [
                'id'       => $cu['id'],
                'nickname' => $cu['nickname'],
            ];
        }
        $info['comment'] = $cl;

        $store = Db::name('store')
            ->alias('store')
            ->join('fa_shop shop', 'shop.id = store.shop_id')
            ->where('shop.user_id', $info['user_id'])
            ->field('store.id,store.name,store.phone,store.logo,store.qualification,store.image,store.shop_id,store.area,store.address,store.brand')
            ->find();
        $info['store'] = $store;
        return $info;
    }

    public static function getCommentHeart($comment_id, $login_id)
    {
        if (empty($login_id)) {
            $val = false;
        } else {
            $val = (Db::name('topic_comment_heart')
                    ->where('user_id', $login_id)
                    ->where('comment_id', $comment_id)
                    ->count()) > 0;
        }
        return $val;
    }

    /**
     * 评论 列表
     * */
    public static function getCommentList($id, $page, $limit, $login_id)
    {
        $list = Db::name('topic_comment')
            ->where('topic_id', $id)
            ->order('id', 'desc')
            ->page($page)
            ->limit($limit)
            ->select();

        foreach ($list as $k => $v) {
            $cu = User::getUser('id', $v['user_id']);
            $list[$k]['user'] = [
                'id'       => $cu['id'],
                'nickname' => $cu['nickname'],
                'avatar'   => $cu['avatar'],
            ];

            $list[$k]['is_heart'] = self::getCommentHeart($v['id'], $login_id);

            $list[$k]['reply'] = CommentReply::getReplyRows($v['id'], $login_id, 'topic');
        }

        return $list;
    }

    public static function getCommentCount($id)
    {
        $count = Db::name('topic_comment')
            ->where('topic_id', $id)
            ->count();
        return $count;
    }

    /**
     * 评论信息
     * */
    public static function getComment($id, $login_id)
    {
        $info = Db::name('topic_comment')
            ->where('id', $id)
            ->find();

        $user = User::getUser('id', $info['user_id']);
        $info['user'] = [
            'id'       => $user['id'],
            'nickname' => $user['nickname'],
            'avatar'   => $user['avatar'],
        ];

        $info['is_heart'] = self::getCommentHeart($info['id'], $login_id);
        return $info;
    }

    /**
     * 评论 点赞
     * */
    public static function heartComment($id, $user_id)
    {
        $count = Db::name('topic_comment_heart')
            ->where('comment_id', $id)
            ->where('user_id', $user_id)
            ->count();

        if ($count > 0) {
            $res_1 = Db::name('topic_comment_heart')
                ->where('comment_id', $id)
                ->where('user_id', $user_id)
                ->delete();

            $res_2 = Db::name('topic_comment')
                ->where('id', $id)
                ->setDec('heart');

        } else {
            $data = [
                'comment_id' => $id,
                'user_id'    => $user_id,
            ];
            $res_1 = Db::name('topic_comment_heart')->insert($data);

            $res_2 = Db::name('topic_comment')
                ->where('id', $id)
                ->setInc('heart');
        }

        return $res_1 && $res_2;
    }


    /**
     * 话题 新增
     * */
    public static function addTopic($content, $images, $cate_id, $user_id)
    {
        $data = [
            'content'    => $content,
            'images'     => $images,
            'cate_id'    => $cate_id,
            'user_id'    => $user_id,
            'createtime' => time()
        ];

        $res = Db::name('topic')->insert($data);
        return $res;
    }

    /**
     * 评论 新增
     * */
    public static function addComment($content, $topic_id, $user_id)
    {
        $data = [
            'content'    => $content,
            'topic_id'   => $topic_id,
            'user_id'    => $user_id,
            'createtime' => time(),
            'heart'      => 0
        ];

        $res = Db::name('topic_comment')->insertGetId($data);
        return $res;
    }

    /**
     * 话题 收藏
     * */
    public static function starTopic($topic_id, $user_id)
    {
        $count = Db::name('topic_star')
            ->where('topic_id', $topic_id)
            ->where('user_id', $user_id)
            ->count();

        if ($count > 0) {
            $res = Db::name('topic_star')
                ->where('topic_id', $topic_id)
                ->where('user_id', $user_id)
                ->delete();
        } else {
            $data = [
                'topic_id' => $topic_id,
                'user_id'  => $user_id,
            ];
            $res = Db::name('topic_star')->insert($data);
        }
        return $res;
    }

    /**
     * 话题 点赞
     * */
    public static function heartTopic($topic_id, $user_id)
    {
        $count = Db::name('topic_heart')
            ->where('topic_id', $topic_id)
            ->where('user_id', $user_id)
            ->count();

        if ($count > 0) {
            $res = Db::name('topic_heart')
                ->where('topic_id', $topic_id)
                ->where('user_id', $user_id)
                ->delete();
        } else {
            $data = [
                'topic_id' => $topic_id,
                'user_id'  => $user_id,
            ];
            $res = Db::name('topic_heart')->insert($data);
        }
        return $res;
    }



    /**
     * 作者 关注
     * */
    public static function followUser($user_id, $follower_id)
    {
        $count = Db::name('user_follow')
            ->where('user_id', $user_id)
            ->where('follower_id', $follower_id)
            ->count();

        if ($count > 0) {
            $res = Db::name('user_follow')
                ->where('user_id', $user_id)
                ->where('follower_id', $follower_id)
                ->delete();
        } else {
            if ($user_id == $follower_id) {
                return false;
            }
            $data = [
                'user_id'     => $user_id,
                'follower_id' => $follower_id,
            ];
            $res = Db::name('user_follow')->insert($data);
        }
        return $res;
    }

    /**
     * 话题 举报
     * */
    public static function reportTopic($content, $topic_id, $user_id)
    {
        $data = [
            'content'    => $content,
            'topic_id'   => $topic_id,
            'user_id'    => $user_id,
            'createtime' => time(),
        ];

        $res = Db::name('topic_report')->insert($data);
        return $res;
    }

    //话题浏览量 增加
    public static function incTV($user_id, $step = 1)
    {
        $res = Db::name('user')
            ->where('id', $user_id)
            ->setInc('tv', $step);
        return $res;
    }

    /**
     * 统计用户收藏
     * */
    public static function countUserStar($user_id)
    {
        $count = Db::name('topic_star')
            ->where('user_id', $user_id)
            ->count('*');
        return $count;
    }


    /**
     * 统计用户关注
     * */
    public static function countUserFollow($user_id)
    {
        $count = Db::name('user_follow')
            ->where('follower_id', $user_id)
            ->count('*');
        return $count;
    }

    /**
     * 统计用户发布
     * */
    public static function countUserTopic($user_id)
    {
        $count = Db::name('topic')
            ->where('user_id', $user_id)
            ->count('*');
        return $count;
    }

    /**
     * 我的收藏 话题列表
     * */
    public static function getStarredTopicIds($user_id)
    {
        $tid_arr = Db::name('topic_star')
            ->where('user_id', $user_id)
            ->column('topic_id');
        return $tid_arr;
    }

    /**
     * 我的收藏 删除话题收藏
     * */
    public static function delTopicStar($ids, $login_id)
    {
        $res = Db::name('topic_star')
            ->where('topic_id', 'in',  $ids)
            ->where('user_id', $login_id)
            ->delete();
        return $res;
    }

    /**
     * 我的点赞 话题列表
     * */
    public static function getHeartedTopicIds($user_id)
    {
        $id_arr = Db::name('topic_heart')
            ->where('user_id', $user_id)
            ->column('topic_id');
        return $id_arr;
    }

    /**
     * 我的点赞 删除话题点赞
     * */
    public static function delTopicHeart($ids, $login_id)
    {
        $res = Db::name('topic_heart')
            ->where('topic_id', 'in',  $ids)
            ->where('user_id', $login_id)
            ->delete();
        return $res;
    }

    /**
     * 我的社区 删除话题
     * */
    public static function delTopic($id, $login_id)
    {
        $res = Db::name('topic')->where('id', $id)->where('user_id', $login_id)->delete();
        Db::name('topic_heart')->where('topic_id', $id)->delete();
        Db::name('topic_star')->where('topic_id', $id)->delete();

        $cid_arr = Db::name('topic_comment')->where('topic_id', $id)->column('id');
        Db::name('topic_comment_heart')->where('comment_id', 'in', $cid_arr)->delete();
        Db::name('topic_comment')->where('topic_id', $id)->delete();
        //halt($res);
        return $res;
    }

}