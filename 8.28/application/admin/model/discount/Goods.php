<?php

namespace app\admin\model\discount;

use think\Model;


class Goods extends Model
{

    

    

    // 表名
    protected $name = 'discount_goods';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'audit_status_text', 'goods_name', 'discount_cate_name'
    ];
    

    
    public function getAuditStatusList()
    {
        return ['0' => __('Audit_status 0'), '1' => __('Audit_status 1')];
    }


    public function getAuditStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['audit_status']) ? $data['audit_status'] : '');
        $list = $this->getAuditStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getGoodsNameAttr($value, $data)
    {
        $goods = \app\api\model\Goods::getGoods($data['goods_id']);
        $goods_name = !empty($goods) ? $goods['name'] : '';
        return $goods_name;
    }

    public function getDiscountCateNameAttr($value, $data)
    {
        $info = \app\api\model\Discount::getDiscountCate($data['discount_cate_id']);
        return !empty($info) ? $info['name'] : '';
    }


}
