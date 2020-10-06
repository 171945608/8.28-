<?php

namespace app\admin\model\search;

use think\Model;


class Keywords extends Model
{

    

    

    // 表名
    protected $name = 'search_keywords';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'group_text'
    ];
    

    
    public function getGroupList()
    {
        return [
            'goods' => __('Group goods'),
            'store' => __('Group store'),
            'groupbuy' => __('Group groupbuy'),
            'purchase' => __('Group purchase'),
            'discount' => __('Group discount'),
            'union' => __('Group union'),
            'express' => __('Group express'),
            'vip' => __('Group vip'),
            'topic' => __('Group topic')
        ];
    }


    public function getGroupTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['group']) ? $data['group'] : '');
        $list = $this->getGroupList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
