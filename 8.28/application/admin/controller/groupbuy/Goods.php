<?php

namespace app\admin\controller\groupbuy;

use app\common\controller\Backend;
use Exception;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Goods extends Backend
{
    
    /**
     * Goods模型对象
     * @var \app\admin\model\groupbuy\Goods
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\groupbuy\Goods;

    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    public function index($ids = null)
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $total = Db::name('goods')->alias('g')
                ->join('groupbuy_goods gg', 'gg.goods_id=g.id')
                ->where('gg.groupbuy_id', $ids)
                ->count();

            $list = Db::name('goods')->alias('g')
                ->join('groupbuy_goods gg', 'gg.goods_id=g.id')
                ->join('store s', 's.id=g.store_id')
                ->where('gg.groupbuy_id', $ids)
                ->order('g.id desc')
                ->limit($offset, $limit)
                ->field('gg.id,g.name,g.image,s.name as store_name')
                ->select();

            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        $this->assignconfig('ids', $ids);
        return $this->view->fetch();
    }

}
