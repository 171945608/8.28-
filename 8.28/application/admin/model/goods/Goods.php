<?php

namespace app\admin\model\goods;

use think\Model;


class Goods extends Model
{

    

    

    // 表名
    protected $name = 'goods';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'audit_text',
        'state_text'
    ];
    

    
    public function getAuditList()
    {
        return ['1' => __('Audit 1'), '2' => __('Audit 2'), '3' => __('Audit 3')];
    }

    public function getStateList()
    {
        return ['1' => __('State 1'), '0' => __('State 0')];
    }


    public function getAuditTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['audit']) ? $data['audit'] : '');
        $list = $this->getAuditList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStateTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['state']) ? $data['state'] : '');
        $list = $this->getStateList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
