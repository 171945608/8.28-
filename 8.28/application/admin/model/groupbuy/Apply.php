<?php

namespace app\admin\model\groupbuy;

use think\Model;


class Apply extends Model
{

    

    

    // 表名
    protected $name = 'groupbuy_apply';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'is_read_text',
        'groupbuy_title',
        'store_name',
        'goods_data',
        'user_name',
    ];
    

    
    public function getIsReadList()
    {
        return ['0' => __('Is_read 0'), '1' => __('Is_read 1')];
    }


    public function getIsReadTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_read']) ? $data['is_read'] : '');
        $list = $this->getIsReadList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getGroupbuyTitleAttr($value, $data)
    {
        return \app\api\model\Groupbuy::getGroupbuyTitle($data['groupbuy_id']) ?: '';
    }

    public function getStoreNameAttr($value, $data)
    {
        return \app\api\model\Shop::getStoreName($data['groupbuy_id']) ?: '';
    }

    public function getUserNameAttr($value, $data)
    {
        return \app\api\model\User::getNickname($data['user_id']) ?: '';
    }

    public function getGoodsDataAttr($value, $data)
    {
        $goods = json_decode($data['goods'], true);

        $str_arr = [];
        if (is_array($goods)) {
            foreach ($goods as $item) {
                array_push($str_arr, sprintf('[ID:%u | 名称:%s | 数量:%u]', $item['goods_id'], $item['goods_name'], $item['goods_num']));
            }
        }
        $str = implode('，', $str_arr);

        return $str;
    }


}
