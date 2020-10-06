<?php
/**
 * @author 见龙在野
 * @date 2020-08-08
 */

namespace app\api\controller\msg;

use app\api\model\User;
use app\api\model\Goods;
use app\api\model\Shop;
use app\common\controller\Api;
use think\Db;

/**
 * 采购消息
 * */
class Purchase extends Api
{
    protected $noNeedLogin = [''];
    protected $noNeedRight = '*';

    public function getPurchases()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $login = $this->auth->getUser();
        $list = Db::name('msg_purchase')->alias('mp')
            ->join('fa_purchase p', 'p.id = mp.purchase_id')
            ->where('mp.user_id', $login->id)
            ->page($page)
            ->limit($limit)
            ->order('mp.id desc')
            ->field('mp.id,mp.purchase_id,mp.time,mp.is_read,p.title,p.company,p.item_num,p.goods_num,p.user_id')
            ->select();

        if (empty($list)) {
            $this->success("", [
                'list' => $list
            ]);
        }

        $data = [];
        foreach ($list as $key => $val) {
            $user = User::getUser('id', $val['user_id']);
            $data[] = [
                'id'        => $val['purchase_id'],
                'title'     => $val['title'],
                'company'   => $val['company'],
                'item_num'  => $val['item_num'],
                'goods_num' => $val['goods_num'],
                'msg_id'    => $val['id'],
                'time'      => $val['time'],
                'is_read'   => $val['is_read'],
                'user'      => [
                    'id'       => $user['id'],
                    'nickname' => $user['nickname'],
                    'avatar'   => $user['avatar'],
                ]
            ];
        }

        Db::name('msg_purchase')->where('user_id', $login->id)->update(['is_read' => 1]);
        //halt($list);
        $this->success("", [
            'list' => $data
        ]);
    }

    public function delMsg()
    {
        $msg_id = $this->request->param('msg_id');
        $res = Db::name('msg_purchase')->where('id', $msg_id)->delete();
        if (!$res) {
            $this->error("操作失败");
        } else {
            $this->success("操作成功");
        }
    }

}