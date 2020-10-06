<?php
/**
 * @author 见龙在野
 * @date 2020-08-08
 */

namespace app\api\controller\msg;

use app\api\model\Goods;
use app\api\model\Shop;
use app\common\controller\Api;
use think\Db;

/**
 * 特价消息
 * */
class Discount extends Api
{
    protected $noNeedLogin = [''];
    protected $noNeedRight = '*';

    public function getDiscounts()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $login = $this->auth->getUser();
        $list = Db::name('msg_discount')->alias('md')
            ->join('fa_discount_goods dg', 'dg.goods_id = md.goods_id')
            ->where('md.user_id', $login->id)
            ->page($page)
            ->limit($limit)
            ->order('md.id desc')
            ->select();

        if (empty($list)) {
            $this->success("", [
                'list' => $list
            ]);
        }

        $data = [];
        foreach ($list as $key => $val) {
            $goods = Goods::getGoods($val['goods_id']);//dump($goods);
            $user = Shop::getStoreUserByStoreId($goods['store_id']);
            $data[] = [
                'id'             => $goods['id'],
                'name'           => $goods['name'],
                'image'          => $goods['image'],
                'oprice'         => $goods['oprice'],
                'discount_price' => $val['discount_price'],
                'msg_id'         => $val['id'],
                'time'           => $val['time'],
                'is_read'        => $val['is_read'],
                'user'           => [
                    'id'       => $user['id'],
                    'nickname' => $user['nickname'],
                    'avatar'   => $user['avatar'],
                ]
            ];
        }

        Db::name('msg_discount')->where('user_id', $login->id)->update(['is_read' => 1]);
        //halt($list);
        $this->success("", [
            'list' => $data
        ]);
    }

    public function delMsg()
    {
        $msg_id = $this->request->param('msg_id');
        $res = Db::name('msg_discount')->where('id', $msg_id)->delete();
        if (!$res) {
            $this->error("操作失败");
        } else {
            $this->success("操作成功");
        }
    }

}