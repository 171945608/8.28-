<?php

namespace app\admin\model\shop;

use think\Model;


class Shop extends Model
{

    

    

    // 表名
    protected $name = 'shop';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'logintime_text',
        'audit_state_text'
    ];
    

    
    public function getAuditStateList()
    {
        return ['10' => __('Audit_state 10'), '20' => __('Audit_state 20'), '30' => __('Audit_state 30')];
    }


    public function getLogintimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['logintime']) ? $data['logintime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getAuditStateTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['audit_state']) ? $data['audit_state'] : '');
        $list = $this->getAuditStateList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setLogintimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
