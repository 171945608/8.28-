<?php

namespace app\admin\model\carousel;

use think\Model;


class Cate extends Model
{

    

    

    // 表名
    protected $name = 'carousel';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'cate_name'
    ];
    

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }


    public function getCateNameAttr($value, $data)
    {
        return \app\api\model\Cate::getCateName($data['cate_id']) ?: '';
    }






}
