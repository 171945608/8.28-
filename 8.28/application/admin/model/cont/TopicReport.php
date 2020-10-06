<?php

namespace app\admin\model\cont;

use app\api\model\Community;
use app\api\model\User;
use think\Model;


class TopicReport extends Model
{

    

    

    // 表名
    protected $name = 'topic_report';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'topic_cont', 'user_name'
    ];
    
    public function getTopicContAttr($value, $data)
    {
        $topic = Community::getTopic($data['topic_id']);
        $ret = !empty($topic) && isset($topic['content']) ? $topic['content'] : '';
        return $ret;
    }

    public function getUserNameAttr($value, $data)
    {
        $user = User::getUser('id', $data['user_id']);
        $ret = !empty($user) && isset($user['nickname']) ? $user['nickname'] : '';
        return $ret;
    }




}
