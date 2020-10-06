<?php

namespace app\admin\model\cont;

use think\Model;


class Topic extends Model
{

    

    

    // 表名
    protected $name = 'topic';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'cate_name', 'user_name', 'star_num', 'heart_num', 'comment_num'
    ];

    public function getCateNameAttr($value, $data)
    {
        $info = \think\Db::name('topic_cate')->where('id', $data['cate_id'])->find();
        return $info['name'];
    }

    public function getUserNameAttr($value, $data)
    {
        $info = \think\Db::name('user')->where('id', $data['user_id'])->find();
        return $info['nickname'];
    }

    public function getStarNumAttr($value, $data)
    {
        $num = \think\Db::name('topic_star')->where('topic_id', $data['id'])->count();
        return $num;
    }

    public function getHeartNumAttr($value, $data)
    {
        $num = \think\Db::name('topic_heart')->where('topic_id', $data['id'])->count();
        return $num;
    }

    public function getCommentNumAttr($value, $data)
    {
        $num = \think\Db::name('topic_comment')->where('topic_id', $data['id'])->count();
        return $num;
    }







}
