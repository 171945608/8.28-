<?php
/**
 * @author 见龙在野
 * @date 2020-08-15
 */

namespace app\api\model;

/*
 * 评论回复
 *
 * */
use think\Config;
use think\Db;

class CommentReply
{
    protected static $tableTopic           = 'topic';
    protected static $tableTopicComment    = 'topic_comment';
    protected static $tableTopicReply      = 'topic_comment_reply';
    protected static $tableTopicReplyHeart = 'topic_comment_reply_heart';

    protected static $tableVideo           = 'video';
    protected static $tableVideoComment    = 'video_comment';
    protected static $tableVideoReply      = 'video_comment_reply';
    protected static $tableVideoReplyHeart = 'video_comment_reply_heart';

    protected static function getTableName($table_type, $action_type, $is_full = 0)
    {
        switch ($table_type) {
            case 'topic':
                switch ($action_type) {
                    case "reply":
                        $tableName = self::$tableTopicReply;
                        break;
                    case "heart":
                        $tableName = self::$tableTopicReplyHeart;
                        break;
                    case "comment":
                        $tableName = self::$tableTopicComment;
                        break;
                    case "work":
                        $tableName = self::$tableTopic;
                        break;
                }

                break;

            case 'video':
                switch ($action_type) {
                    case "reply":
                        $tableName = self::$tableVideoReply;
                        break;
                    case "heart":
                        $tableName = self::$tableVideoReplyHeart;
                        break;
                    case "comment":
                        $tableName = self::$tableVideoComment;
                        break;
                    case "work":
                        $tableName = self::$tableVideo;
                        break;
                }

                break;
        }

        $table_prefix = Config::get('database.prefix');
        $is_full && $tableName = "{$table_prefix}$tableName";
        return $tableName;
    }

    //回复评论
    public static function replyComment($comment_id, $content, $login_id, $table_type)
    {
        $login = User::getUser('id', $login_id);
        $now = time();
        $data = [
            'comment_id'    => $comment_id,
            'content'        => $content,
            'user_id'       => $login['id'],
            'user_avatar'   => $login['avatar'],
            'user_nickname' => $login['nickname'],
            'createtime'    => $now,
        ];

        return Db::name(self::getTableName($table_type, 'reply'))->insertGetId($data);
    }

    //点赞回复
    public static function heartReply($reply_id, $login_id, $table_type)
    {
        $tableName = self::getTableName($table_type, 'heart');
        $arr = [
            'reply_id' => $reply_id,
            'user_id'  => $login_id,
        ];
        $heart = Db::name($tableName)->where($arr)->find();

        if (empty($heart)) {
            $res = Db::name($tableName)->insert($arr);
        } else {
            $res = Db::name($tableName)->where($arr)->delete();
        }

        return $res;
    }

    //获取回复列表
    public static function getReplyRows($comment_id, $login_id, $table_type)
    {
        $tableName = self::getTableName($table_type, 'reply');
        $rows = Db::name($tableName)->alias('r')
            ->join(sprintf('%s %s', self::getTableName($table_type, 'heart', 1), 'rh'), "rh.reply_id = r.id and rh.user_id = r.user_id", 'left')
            ->where('r.comment_id', $comment_id)
            ->order('r.id desc')
            ->field('r.id, r.content, r.user_id, r.user_avatar, r.user_nickname, r.comment_id, r.createtime, rh.reply_id')
            ->select();
        //halt($rows);

        $list = [];
        foreach ($rows as $row) {
            array_push($list, [
                'id'            => $row['id'],
                'content'       => $row['content'],
                'user_id'       => $row['user_id'],
                'user_avatar'   => $row['user_avatar'],
                'user_nickname' => $row['user_nickname'],
                'is_author'     => self::getAuthorIdByCommentId($row['comment_id'], $table_type) == $row['user_id'] ? 1 : 0,
                'heart_num'     => self::getReplyHeartCount($row['id'], $table_type),
                'is_heart'      => empty($row['reply_id']) ? 0 : 1,
                'createtime'    => $row['createtime'],
            ]);
        }
        return $list;
    }

    //获取回复信息
    public static function getReply($reply_id, $login_id, $table_type)
    {
        $tableName = self::getTableName($table_type, 'reply');
        $row = Db::name($tableName)->alias('r')
            ->join(sprintf('%s %s', self::getTableName($table_type, 'heart', 1), 'rh'), "rh.reply_id = r.id and rh.user_id = r.user_id", 'left')
            ->where('r.id', $reply_id)
            ->field('r.id, r.content, r.user_id, r.user_avatar, r.user_nickname, r.comment_id, r.createtime, rh.reply_id')
            ->find();

        $info = [
            'id'            => $row['id'],
            'content'       => $row['content'],
            'user_id'       => $row['user_id'],
            'user_avatar'   => $row['user_avatar'],
            'user_nickname' => $row['user_nickname'],
            'is_author'     => self::getAuthorIdByCommentId($row['comment_id'], $table_type) == $row['user_id'] ? 1 : 0,
            'heart_num'     => self::getReplyHeartCount($row['id'], $table_type),
            'is_heart'      => empty($row['reply_id']) ? 0 : 1,
            'createtime'    => $row['createtime'],
        ];
        return $info;
    }

    //根据评论ID获取作者ID
    public static function getAuthorIdByCommentId($comment_id, $table_type)
    {
        $tableName = self::getTableName($table_type, 'comment');
        $table_type == 'topic' && $work_id = 'topic_id';
        $table_type == 'video' && $work_id = 'video_id';
        $author_id = Db::name($tableName)->alias('c')
            ->join(sprintf('%s %s', self::getTableName($table_type, 'work', 1), 'w'), "w.id = c.{$work_id}")
            ->where('c.id', $comment_id)
            ->value('w.user_id');

        empty($author_id) && $author_id = 0;
       // halt($author_id);
        return $author_id;
    }

    //统计回复点赞数
    public static function getReplyHeartCount($reply_id, $table_type)
    {
        $tableName = self::getTableName($table_type, 'heart');
        $count = Db::name($tableName)
            ->where('reply_id', $reply_id)
            ->count();

        return $count;
    }

}