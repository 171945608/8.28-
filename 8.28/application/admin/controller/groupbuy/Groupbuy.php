<?php

namespace app\admin\controller\groupbuy;

use app\admin\model\User;
use app\api\model\Goods;
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
class Groupbuy extends Backend
{

    /**
     * Groupbuy模型对象
     * @var \app\admin\model\groupbuy\Groupbuy
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\groupbuy\Groupbuy;
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    //选品
    public function pick($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            //halt($params);
            $goods_id = $params['goods_id'] ? : 0;
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }

                    $goods = Goods::getGoods($goods_id);
                    if ($goods) {
                        $ggoods = Db::name('groupbuy_goods')->where([
                            'groupbuy_id' => $row->id,
                            'goods_id'    => $goods['id'],
                        ])->find();
                        if ($ggoods) {
                            $result = true;
                        } else {
                            $result = Db::name('groupbuy_goods')->insert([
                                'groupbuy_id' => $row->id,
                                'store_id'    => $goods['store_id'],
                                'goods_id'    => $goods['id'],
                            ]);
                        }
                    }

                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    public function getStores()
    {
        $list = Db::name('store')
            ->order('id desc')
            ->field('id as value, name')
            ->select();
        $this->success('', null, $list);
    }

    public function getGoods()
    {
        $store_id = $this->request->param('store_id');
        $list = Db::name('goods')
            ->where('audit', 2)
            ->where('state', 1)
            ->where('store_id', $store_id)
            ->order('id desc')
            ->field('id as value, name')
            ->select();
        $this->success('', null, $list);
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);

                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validateFailException(true)->validate($validate);
                    }
                    $result = $this->model->allowField(true)->save($params);

                    if ($result) {
                        $lastId = Db::name('groupbuy')->max('id');
                        $user_ids = \app\api\model\User::getMsgToIds();
                        $data = [];
                        $now = time();
                        foreach ($user_ids as $user_id) {
                            array_push($data, [
                                'user_id'  => $user_id,
                                'groupbuy_id' =>$lastId,
                                'is_read'  => 0,
                                'time'     => $now,
                            ]);
                        }
                        Db::name('msg_groupbuy')->insertAll($data);
                    }

                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

}
