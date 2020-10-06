<?php

namespace app\api\model;

use think\Db;
use think\Exception;

class Video
{
    //新增
    public static function addVideo($user_id, $file, $title, $content, $longitude, $latitude)
    {
        $data = self::getVideoData('add', [
            'file' => $file,
            'title' => $title,
            'content' => $content,
            'user_id' => $user_id,
            'location' => '',
            'longitude' => $longitude,
            'latitude' => $latitude,
        ]);
        $res = Db::name('video')->insert($data);
        return $res;
    }

    public static function getVideoData($type, $params)
    {
        switch (strtolower($type)) {
            case 'add':
                $data = [
                    'file' => $params['file'],
                    'title' => $params['title'],
                    'content' => $params['content'],
                    'user_id' => $params['user_id'],
                    'createtime' => time(),
                    'location' => $params['location'],
                    'longitude' => $params['longitude'],
                    'latitude' => $params['latitude'],
                ];
                break;

            default:
                $data = [];
        }
        return $data;
    }

    //列表
    public static function getVideoList($where, $page = 1, $limit = 999, $order = 'id desc')
    {
        $list = Db::name('video')->where($where)
            ->page($page)
            ->limit($limit)
            ->order($order)
            ->select();
        return $list;
    }

    public static function getVideoCount($where)
    {
        $list = Db::name('video')->where($where)
            ->count();
        return $list;
    }


    //信息
    public static function getVideo($video_id)
    {
        $info = Db::name('video')->where('id', $video_id)->find();
        return $info;
    }

    //oss删除
    public static function deleteOssVideo($user_id, $file)
    {
        $shop = Db::name('shop')->where('user_id', $user_id)->find();
        if (!empty($shop) && $shop['access_key_id'] && $shop['access_key_secret'] && $shop['bucket'] && $shop['endpoint']) {
            $accessKeyId = $shop['access_key_id'];
            $accessKeySecret = $shop['access_key_secret'];
            $bucket = $shop['bucket'];
            $endpoint = $shop['endpoint'];
        } else {
            $conf = (config('aliyun'))['auth'];
            $accessKeyId = $conf['AccessKeyId'];
            $accessKeySecret = $conf['AccessKeySecret'];
            $bucket = $conf['bucket'];
            $endpoint = $conf['endpoint'];
        }

        try {
            $ossClient = new \OSS\OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $ossClient->deleteObject($bucket, $file);
        } catch (\OSS\OssException $e) {
            //
        }
    }

    public static function getStar($video_id, $login_id)
    {
        if (empty($login_id)) {
            $val = false;
        } else {
            $val = (Db::name('video_star')
                    ->where('user_id', $login_id)
                    ->where('video_id', $video_id)
                    ->count()) > 0;
        }
        return $val;
    }

    public static function getHeart($video_id, $login_id)
    {
        if (empty($login_id)) {
            $val = false;
        } else {
            $val = (Db::name('video_heart')
                    ->where('user_id', $login_id)
                    ->where('video_id', $video_id)
                    ->count()) > 0;
        }
        return $val;
    }


    public static function formatVideoList($type, $list, $login_id = 0)
    {
        switch (strtolower($type)) {
            case 'default':
                foreach ($list as $key => $val) {
                    $list[$key]['star'] = self::getStar($val['id'], $login_id);
                    $list[$key]['heart'] = self::getHeart($val['id'], $login_id);

                    $user = User::getUser('id', $val['user_id']);
                    $follow = (Db::name('user_follow')
                            ->where('user_id', $user['id'])
                            ->where('follower_id', $login_id)
                            ->count()) > 0;
                    $list[$key]['user'] = [
                        'id' => $user['id'],
                        'avatar' => $user['avatar'],
                        'nickname' => $user['nickname'],
                        'follow' => $follow,
                    ];

                    $list[$key]['stat'] = [
                        'heart' => self::getHeartStatistic($val['id']),
                        'star' => self::getStarStatistic($val['id']),
                        'comment' => self::getCommentStatistic($val['id']),
                    ];
                }
                break;

            case 'author':
                foreach ($list as $key => $val) {
                    $user = User::getUser('id', $val['user_id']);
                    $follow = (Db::name('user_follow')
                            ->where('user_id', $user['id'])
                            ->where('follower_id', $login_id)
                            ->count()) > 0;
                    $list[$key]['user'] = [
                        'id' => $user['id'],
                        'avatar' => $user['avatar'],
                        'nickname' => $user['nickname'],
                        'follow' => $follow,
                    ];

                }
                break;
        }
        return $list;
    }

    public static function getHeartStatistic($video_id)
    {
        $stat = Db::name('video_heart')
            ->where('video_id', $video_id)
            ->count();
        return $stat;
    }

    public static function getStarStatistic($video_id)
    {
        $stat = Db::name('video_star')
            ->where('video_id', $video_id)
            ->count();
        return $stat;
    }

    public static function getCommentStatistic($video_id)
    {
        $stat = Db::name('video_comment')
            ->where('video_id', $video_id)
            ->count();
        return $stat;
    }

    /**
     * 点赞
     * */
    public static function heartVideo($video_id, $user_id)
    {
        $info = Db::name('video_heart')->where('video_id', $video_id)->where('user_id', $user_id)->find();
        if (empty($info)) {
            $res = Db::name('video_heart')->insert([
                'video_id' => $video_id, 'user_id' => $user_id
            ]);
        } else {
            $res = Db::name('video_heart')->where('id', $info['id'])->delete();
        }
        return $res;
    }

    /**
     * 收藏
     * */
    public static function starVideo($video_id, $user_id)
    {
        $info = Db::name('video_star')->where('video_id', $video_id)->where('user_id', $user_id)->find();
        if (empty($info)) {
            $res = Db::name('video_star')->insert([
                'video_id' => $video_id, 'user_id' => $user_id
            ]);
        } else {
            $res = Db::name('video_star')->where('id', $info['id'])->delete();
        }
        return $res;
    }

    /**
     * 评论列表
     * */
    public static function getCommentList($video_id, $page, $limit)
    {
        $list = Db::name('video_comment')
            ->where('video_id', $video_id)
            ->page($page)
            ->limit($limit)
            ->order('id desc')
            ->select();
        return $list;
    }

    public static function getCommentHeart($comment_id, $login_id)
    {
        if (empty($login_id)) {
            $val = false;
        } else {
            $val = (Db::name('video_comment_heart')
                    ->where('user_id', $login_id)
                    ->where('comment_id', $comment_id)
                    ->count()) > 0;
        }
        return $val;
    }

    public static function formatCommentList($type, $list, $video_id, $login_id = 0)
    {
        $video = self::getVideo($video_id);
        switch (strtolower($type)) {
            case 'common':
                foreach ($list as $key => $val) {
                    $user = User::getUser('id', $val['user_id']);
                    $list[$key]['user'] = [
                        'id' => $user['id'],
                        'avatar' => $user['avatar'],
                        'nickname' => $user['nickname'],
                        'is_author' => $user['id'] == $video['user_id'],
                    ];

                    $list[$key]['is_heart'] = self::getCommentHeart($val['id'], $login_id);

                    $list[$key]['reply'] = CommentReply::getReplyRows($val['id'], $login_id, 'video');
                }
                break;
        }
        return $list;
    }

    /**
     * 评论信息
     * */
    public static function getComment($id, $login_id)
    {
        $info = Db::name('video_comment')
            ->where('id', $id)
            ->find();

        $user = User::getUser('id', $info['user_id']);
        $info['user'] = [
            'id' => $user['id'],
            'nickname' => $user['nickname'],
            'avatar' => $user['avatar'],
        ];

        $info['is_heart'] = self::getCommentHeart($info['id'], $login_id);
        return $info;
    }

    /**
     * 评论计数
     * */
    public static function getCommentCount($video_id)
    {
        $count = Db::name('video_comment')
            ->where('video_id', $video_id)
            ->count();
        return $count;
    }

    /**
     * 新增评论
     * */
    public static function commentVideo($content, $video_id, $user_id)
    {
        $res = Db::name('video_comment')->insertGetId([
            'content' => $content,
            'video_id' => $video_id,
            'user_id' => $user_id,
            'createtime' => time(),
            'heart' => 0,
        ]);
        return $res;
    }

    /**
     * 点赞评论
     * */
    public static function heartComment($comment_id, $login_id)
    {
        $count = Db::name('video_comment_heart')
            ->where('comment_id', $comment_id)
            ->where('user_id', $login_id)
            ->count();

        if ($count > 0) {
            $res_1 = Db::name('video_comment_heart')
                ->where('comment_id', $comment_id)
                ->where('user_id', $login_id)
                ->delete();

            $res_2 = Db::name('video_comment')
                ->where('id', $comment_id)
                ->setDec('heart');

        } else {
            $data = [
                'comment_id' => $comment_id,
                'user_id' => $login_id,
            ];
            $res_1 = Db::name('video_comment_heart')->insert($data);

            $res_2 = Db::name('video_comment')
                ->where('id', $comment_id)
                ->setInc('heart');
        }

        return $res_1 && $res_2;
    }

    /**
     * 关注作者
     * */
    public static function followAuthor($author_id, $login_id)
    {
        $count = Db::name('user_follow')
            ->where('user_id', $author_id)
            ->where('follower_id', $login_id)
            ->count();

        if ($count > 0) {
            $res = Db::name('user_follow')
                ->where('user_id', $author_id)
                ->where('follower_id', $login_id)
                ->delete();
        } else {
            if ($author_id == $login_id) {
                return false;
            }

            $res = Db::name('user_follow')->insert([
                'user_id' => $author_id,
                'follower_id' => $login_id,
            ]);
        }
        return $res;
    }


    /**
     * 作者首页
     * */
    public static function getAuthorProfile($author_id, $login_id)
    {
        $author = User::getUser('id', $author_id);
        $follow = (Db::name('user_follow')
                ->where('user_id', $author_id)
                ->where('follower_id', $login_id)
                ->count()) > 0;

        $info = [
            'id' => $author['id'],
            'avatar' => $author['avatar'],
            'nickname' => $author['nickname'],
            'follow' => $follow,
            'stat' => [
                'followed' => User::getUserFollowedStatistic($author['id']),
                'follower' => User::getUserFollowerStatistic($author['id']),
            ],
        ];
        return $info;
    }


    /**
     * 评论详情
     * */
    public static function getVideoDetail($video_id, $login_id = 0)
    {
        $info = self::getVideo($video_id);
        $info['star'] = self::getStar($info['id'], $login_id);
        $info['heart'] = self::getHeart($info['id'], $login_id);

        $user = User::getUser('id', $info['user_id']);
        $follow = (Db::name('user_follow')
                ->where('user_id', $user['id'])
                ->where('follower_id', $login_id)
                ->count()) > 0;
        $info['user'] = [
            'id' => $user['id'],
            'avatar' => $user['avatar'],
            'nickname' => $user['nickname'],
            'follow' => $follow,
        ];

        $info['stat'] = [
            'heart' => self::getHeartStatistic($info['id']),
            'star' => self::getStarStatistic($info['id']),
            'comment' => self::getCommentStatistic($info['id']),
        ];
        return $info;
    }


    //搜索作者
    public static function searchAuthor($word, $login_id = 0, $page = 1, $limit = 999)
    {
        $list = Db::name('video')->alias('v')
            ->join('user u', 'u.id=v.user_id')
            ->where('u.nickname', 'like', "%{$word}%")
            ->group('v.user_id')
            ->page($page)
            ->limit($limit)
            ->order('u.id desc')
            ->field('u.id,u.avatar,u.nickname')
            ->select();

        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $list[$key]['follow'] = User::is_follower($val['id'], $login_id);
            }
        }

        return $list;
    }

//删除视频
    public static function delVideo($id, $user_id)
    {
        $video = Db::name('video')->where('id', $id)->where('user_id', $user_id)->find();
        if (empty($video)) {
            return false;
        }

        $res = Db::name('video')->where('id', $id)->where('user_id', $user_id)->delete();
        if (!$res) {
            return false;
        }

        //oss删除
//        self::deleteOssVideo($video['user_id'], $video['file']);

        Db::name('video_foot')->where('video_id', $id)->delete();
        Db::name('video_heart')->where('video_id', $id)->delete();
        Db::name('video_star')->where('video_id', $id)->delete();

        $cid_arr = Db::name('video_comment')->where('video_id', $id)->column('id');
        Db::name('video_comment_heart')->where('comment_id', 'in', $cid_arr)->delete();
        Db::name('video_comment')->where('video_id', $id)->delete();
        return $res;
    }

//添加足迹
    public static function addVideoFoot($id, $user_id)
    {
        Db::name('video_foot')->where('video_id', $id)->where('user_id', $user_id)->delete();
        Db::name('video_foot')->insert([
            'video_id' => $id,
            'user_id' => $user_id,
        ]);
    }

    /**
     * 视频足迹
     * */
    public static function getVideoFoot($user_id, $page = 1, $limit = 999)
    {
        $ids_arr = Db::name('video_foot')->where('user_id', $user_id)->column('video_id');
        $list = Db::name('video')
            ->where('id', 'in', $ids_arr)
            ->page($page)
            ->limit($limit)
            ->order('id desc')
            ->select();
        return $list;
    }

}