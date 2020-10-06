<?php

namespace app\admin\model\vip;

use think\Model;


class Button extends Model
{

    

    

    // 表名
    protected $name = 'vip_button';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'value_text'
    ];
    

    
    public function getValueList()
    {
        return ['0' => __('Value 0'), '1' => __('Value 1')];
    }


    public function getValueTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['value']) ? $data['value'] : '');
        $list = $this->getValueList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
