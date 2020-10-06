<?php

namespace app\admin\model\user;

use think\Model;


class IdAuth extends Model
{

    

    

    // 表名
    protected $name = 'user';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'prevtime_text',
        'logintime_text',
        'jointime_text',
        'forbid_type_text',
        'forbid_end_time_text',
        'id_auth_text',
        'vip_auth_text'
    ];
    

    
    public function getForbidTypeList()
    {
        return ['0' => __('Forbid_type 0'), '1' => __('Forbid_type 1'), '2' => __('Forbid_type 2'), '3' => __('Forbid_type 3'), '4' => __('Forbid_type 4')];
    }

    public function getIdAuthList()
    {
        return ['0' => __('Id_auth 0'), '1' => __('Id_auth 1'), '2' => __('Id_auth 2')];
    }

    public function getVipAuthList()
    {
        return ['0' => __('Vip_auth 0'), '1' => __('Vip_auth 1'), '2' => __('Vip_auth 2')];
    }


    public function getPrevtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['prevtime']) ? $data['prevtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getLogintimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['logintime']) ? $data['logintime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getJointimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['jointime']) ? $data['jointime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getForbidTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['forbid_type']) ? $data['forbid_type'] : '');
        $list = $this->getForbidTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getForbidEndTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['forbid_end_time']) ? $data['forbid_end_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getIdAuthTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['id_auth']) ? $data['id_auth'] : '');
        $list = $this->getIdAuthList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getVipAuthTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['vip_auth']) ? $data['vip_auth'] : '');
        $list = $this->getVipAuthList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setPrevtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setLogintimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setJointimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setForbidEndTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
