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
 * 团购消息
 * */
class Groupbuy extends Api
{
    protected $noNeedLogin = [''];
    protected $noNeedRight = '*';

    //团购消息列表
    public function getGroupbuys()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $login = $this->auth->getUser();
        $list = Db::name('msg_groupbuy')->alias('mg')
            ->join('fa_groupbuy g', 'g.id = mg.groupbuy_id')
            ->where('mg.user_id', $login->id)
            ->page($page)
            ->limit($limit)
            ->order('mg.id desc')
            ->field('g.id,g.title,g.tag,g.image,g.images,g.start_time,g.end_time,g.status,g.createtime,mg.id as msg_id,mg.time')
            ->select();

        if ($list) {
            foreach ($list as $key => $val) {
                $list[$key]['countdown'] = \app\api\model\Groupbuy::getCountdown($val['end_time']);
                $list[$key]['apply'] = \app\api\model\Groupbuy::getApplyCount($val['id']);
            }
        }

        Db::name('msg_groupbuy')->where('user_id', $login->id)->update(['is_read' => 1]);

        //halt($list);
        $this->success("", [
            'list' => $list
        ]);
    }

    public function delMsg()
    {
        $msg_id = $this->request->param('msg_id');
        $res = Db::name('msg_groupbuy')->where('id', $msg_id)->delete();
        if (!$res) {
            $this->error("操作失败");
        } else {
            $this->success("操作成功");
        }
    }


}