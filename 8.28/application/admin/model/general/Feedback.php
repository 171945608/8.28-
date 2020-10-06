<?php

namespace app\admin\model\general;

use app\api\model\User;
use think\Model;


class Feedback extends Model
{


    // 表名
    protected $name = 'feedback';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'user_name'
    ];


    public function getUserNameAttr($value, $data)
    {
        $user = User::getUser('id', $data['user_id']);
        $userName = empty($user) ? '' : $user['nickname'];
        return $userName;
    }


}
