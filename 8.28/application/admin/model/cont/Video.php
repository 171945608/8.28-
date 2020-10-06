<?php

namespace app\admin\model\cont;

use think\Model;


class Video extends Model
{

    

    

    // 表名
    protected $name = 'video';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'is_recommended_text'
    ];
    

    
    public function getIsRecommendedList()
    {
        return ['1' => __('Is_recommended 1'), '0' => __('Is_recommended 0')];
    }


    public function getIsRecommendedTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_recommended']) ? $data['is_recommended'] : '');
        $list = $this->getIsRecommendedList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
