<?php

namespace app\admin\model\vip;

use think\Model;


class Goods extends Model
{

    

    

    // 表名
    protected $name = 'vip_goods';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'is_recommended_text', 'goods_name', 'vip_cate_name'
    ];
    

    
    public function getIsRecommendedList()
    {
        return ['0' => __('Is_recommended 0'), '1' => __('Is_recommended 1')];
    }


    public function getIsRecommendedTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_recommended']) ? $data['is_recommended'] : '');
        $list = $this->getIsRecommendedList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getGoodsNameAttr($value, $data)
    {
        $goods = \app\api\model\Goods::getGoods($data['goods_id']);
        $goods_name = !empty($goods) ? $goods['name'] : '';
        return $goods_name;
    }

    public function getVipCateNameAttr($value, $data)
    {
        $info = \app\api\model\Vip::getCate($data['vip_cate_id']);
        return !empty($info) ? $info['name'] : '';
    }


}
