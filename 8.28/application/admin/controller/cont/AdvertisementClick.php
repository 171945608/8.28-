<?php

namespace app\admin\controller\cont;

use app\common\controller\Backend;
use think\Db;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class AdvertisementClick extends Backend
{
    
    /**
     * AdvertisementClick模型对象
     * @var \app\admin\model\cont\AdvertisementClick
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\cont\AdvertisementClick;

    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }

        // 广告总计 点击总计
        $totalAd = Db::name('advertisement')->where([])->count();
        $totalClick = Db::name('advertisement_click')->where([])->count();

        // 图表
        $ad_arr = Db::name('advertisement')->column('title');

        $pieData = [];
        $stat_arr = [];
        foreach ($ad_arr as $k => $v) {
            $stat = Db::name('advertisement_click')
                ->where('ad_title', $v)
                ->count();
            $stat_arr[$k] = $stat;
            array_push($pieData, [
                'value' => $stat, 'name' => $v
            ]);
        }
        //halt($pieData);

        $this->view->assign([
            'totalAd'   => $totalAd,
            'totalClick' => $totalClick,

            'ad' => implode(',', array_map(function($ad) {
                return "'{$ad}'";
            }, $ad_arr)),
            'stat' => implode(',', $stat_arr),
            'pieData' => json_encode($pieData),
        ]);

        return $this->view->fetch();
    }
}
