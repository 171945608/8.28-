<?php

namespace app\shop\controller;

use app\api\model\Discount;
use app\api\model\Groupbuy;
use app\api\model\Mine;
use app\api\model\Vip;
use think\Config;
use think\Hook;
use think\Response;
use think\Session;
use think\Validate;

/**
 * 商品管理
 */
class Goods extends Base
{
    protected $store_id = 0;

    public function _initialize()
    {
        parent::_initialize();
        //移除HTML标签
        $this->request->filter('trim,strip_tags,htmlspecialchars');

        $this->store_id = Session::get('store.id');
    }


    /**
     * 商品分类 列表
     * */
    public function cate()
    {
        if ($this->request->isPost()) {
            $page = $this->request->param('page');
            $limit = $this->request->param('limit');

            $list = Mine::getGoodsCateList($this->store_id, $page, $limit);
            $count = Mine::getGoodsCateCount($this->store_id);
            return $this->getResponse($list, $count);
        }

        return $this->view->fetch();
    }

    /**
     * 商品分类 新增
     * */
    public function addCate()
    {

        if ($this->request->isPost()) {
            $res = Mine::addGoodsCate($this->store_id, [
                'name'      => $this->request->param('name'),
                'weigh'     => $this->request->param('weigh'),
                'image'     => $this->request->param('image'),
                'system_id' => $this->request->param('system_id'),
            ]);
            if (!$res) {
                $this->error('操作失败');
            } else {
                $this->success('操作成功');
            }
        }

        $this->assign('system_cates', Mine::getSystemGoodsCateList());
        return $this->view->fetch();
    }

    /**
     * 商品分类 编辑
     * */
    public function editCate()
    {
        $id = $this->request->param('id');
        if ($this->request->isPost()) {
            $res = Mine::editGoodsCate($this->store_id, $id, [
                'name'      => $this->request->param('name'),
                'weigh'     => $this->request->param('weigh'),
                'image'     => $this->request->param('image'),
                'system_id' => $this->request->param('system_id'),
            ]);
            if (!$res) {
                $this->error('操作失败');
            } else {
                $this->success('操作成功');
            }
        }

        $this->assign('system_cates', Mine::getSystemGoodsCateList());
        $cate = Mine::getGoodsCate($this->store_id, $id);
        $this->assign('cate', $cate);
        return $this->view->fetch();
    }

    /**
     * 商品分类 删除
     * */
    public function deleteCate()
    {
        $ids = $this->request->param('ids');
        if (empty($ids)) {
            $this->error('请选择操作参数');
        }

        $ids = explode(',', $ids);
        $res = Mine::deleteGoodsCate($this->store_id, $ids);
        if (!$res) {
            $this->error('操作失败');
        } else {
            $this->success('操作成功');
        }
    }

    /**
     * 商品分组 列表
     * */
    public function group()
    {
        if ($this->request->isPost()) {
            $page = $this->request->param('page');
            $limit = $this->request->param('limit');

            $list = Mine::getGoodsGroupList($this->store_id, $page, $limit);
            $count = Mine::getGoodsGroupCount($this->store_id);
            return $this->getResponse($list, $count);
        }

        return $this->view->fetch();
    }

    /**
     * 商品分组 新增
     * */
    public function addGroup()
    {
        if ($this->request->isPost()) {
            $status = $this->request->param('status', false) ? 1 : 0;
            $res = Mine::addGoodsGroup($this->store_id, [
                'name'   => $this->request->param('name'),
                'image'  => $this->request->param('image'),
                'weigh'  => $this->request->param('weigh'),
                'status' => $status,
            ]);
            if (!$res) {
                $this->error('操作失败');
            } else {
                $this->success('操作成功');
            }
        }
        return $this->view->fetch();
    }

    /**
     * 商品分组 编辑
     * */
    public function editGroup()
    {
        $id = $this->request->param('id');
        if ($this->request->isPost()) {

            $status = $this->request->param('status', false) ? 1 : 0;
            $res = Mine::editGoodsGroup($this->store_id, $id, [
                'name'   => $this->request->param('name'),
                'image'  => $this->request->param('image'),
                'weigh'  => $this->request->param('weigh'),
                'status' => $status,
            ]);
            if (!$res) {
                $this->error('操作失败');
            } else {
                $this->success('操作成功');
            }
        }

        $info = Mine::getGoodsGroup($this->store_id, $id);
        $this->assign('info', $info);
        return $this->view->fetch();
    }

    /**
     * 商品分组 状态切换
     * */
    public function switchStatus()
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('请选择操作参数');
        }

        $res = Mine::switchGoodsGroupStatus($this->store_id, $id);
        if (!$res) {
            $this->error('操作失败');
        } else {
            $this->success('操作成功');
        }
    }

    /**
     * 商品分组 删除
     * */
    public function deleteGroup()
    {
        $ids = $this->request->param('ids');
        if (empty($ids)) {
            $this->error('请选择操作参数');
        }

        $ids = explode(',', $ids);
        $res = Mine::deleteGoodsGroup($this->store_id, $ids);
        if (!$res) {
            $this->error('操作失败');
        } else {
            $this->success('操作成功');
        }
    }

    /**
     * 商品管理 等待审核
     * */
    public function forAudit()
    {
        if ($this->request->isPost()) {
            $page = $this->request->param('page');
            $limit = $this->request->param('limit');

            $list = Mine::getGoodsForAuditList($this->store_id, $page, $limit);
            $list = Mine::formatGoodsList($list);
            $count = Mine::getGoodsForAuditCount($this->store_id);
            return $this->getResponse($list, $count);
        }

        return $this->view->fetch();
    }

    /**
     * 商品管理 审核驳回
     * */
    public function auditReject()
    {
        if ($this->request->isPost()) {
            $page = $this->request->param('page');
            $limit = $this->request->param('limit');

            $list = Mine::getGoodsAuditRejectList($this->store_id, $page, $limit);
            $list = Mine::formatGoodsList($list);
            $count = Mine::getGoodsAuditRejectCount($this->store_id);
            return $this->getResponse($list, $count);
        }

        return $this->view->fetch();
    }

    /**
     * 商品管理 上架列表
     * */
    public function upGoods()
    {
        if ($this->request->isPost()) {
            $page = $this->request->param('page');
            $limit = $this->request->param('limit');

            $list = Mine::getUpGoodsList($this->store_id, $page, $limit);
            $list = Mine::formatGoodsList($list);
            $count = Mine::getUpGoodsCount($this->store_id);
            return $this->getResponse($list, $count);
        }

        return $this->view->fetch();
    }

    /**
     * 商品管理 下架列表
     * */
    public function downGoods()
    {
        if ($this->request->isPost()) {
            $page = $this->request->param('page');
            $limit = $this->request->param('limit');

            $list = Mine::getDownGoodsList($this->store_id, $page, $limit);
            $list = Mine::formatGoodsList($list);
            $count = Mine::getDownGoodsCount($this->store_id);
            return $this->getResponse($list, $count);
        }

        return $this->view->fetch();
    }

    /**
     * 商品管理 新增
     * */
    public function addGoods()
    {
        if ($this->request->isPost()) {
            //halt($this->request->post());
            $group_ids = $this->request->param('group_ids/a');
            empty($group_ids) && $group_ids = '';
            !empty($group_ids) && $group_ids = implode(',', array_keys($group_ids));

            $state = $this->request->param('state', false) ? 1 : 0;
            $res = Mine::addGoods($this->store_id, [
                'name'       => $this->request->param('name'),
                'image'      => $this->request->param('image'),
                'images'     => $this->request->param('images'),
                'video'      => $this->request->param('video'),
                'cate_id'    => $this->request->param('cate_id'),
                'group_ids'  => $group_ids,
                'price'      => $this->request->param('price'),
                'oprice'     => $this->request->param('oprice'),
                'detail'     => $_POST['detail'],
                'equities'   => '',
                'delivery'   => $this->request->param('delivery'),
                'quotations' => $this->request->param('weigh'),
                'state'      => $state,
            ]);
            if (!$res) {
                $this->error('操作失败');
            } else {
                $this->success('操作成功');
            }
        }

        $this->assign('cates', Mine::getGoodsCateListForPlatform());
        $this->assign('groups', Mine::getUsableStoreGoodsGroupList($this->store_id));
        return $this->view->fetch();
    }

    /**
     * 商品管理 编辑
     * */
    public function editGoods()
    {
        $id = $this->request->param('id');
        if ($this->request->isPost()) {
            $group_ids = $this->request->param('group_ids/a');
            empty($group_ids) && $group_ids = '';
            !empty($group_ids) && $group_ids = implode(',', array_keys($group_ids));

            $state = $this->request->param('state', false) ? 1 : 0;
            $res = Mine::editGoods($this->store_id, $id, [
                'name'      => $this->request->param('name'),
                'image'     => $this->request->param('image'),
                'images'    => $this->request->param('images'),
                'video'     => $this->request->param('video'),
                'cate_id'   => $this->request->param('cate_id'),
                'group_ids' => $group_ids,
                'price'     => $this->request->param('price'),
                'oprice'    => $this->request->param('oprice'),
                'detail'    => $_POST['detail'],
                'equities'  => '',
                'delivery'  => $this->request->param('delivery'),
                'state'     => $state,
            ]);
            if (!$res) {
                $this->error('操作失败');
            } else {
                $this->success('操作成功');
            }
        }

        $this->assign('cates', Mine::getGoodsCateListForPlatform());
        $this->assign('groups', Mine::getUsableStoreGoodsGroupList($this->store_id));
        $info = Mine::getGoods($this->store_id, $id);
        $this->assign('info', $info);
        return $this->view->fetch();
    }

    /**
     * 商品管理 上下架
     * */
    public function switchState()
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('请选择操作参数');
        }

        $res = Mine::switchGoodsState($this->store_id, $id);
        if (!$res) {
            $this->error('操作失败');
        } else {
            $this->success('操作成功');
        }
    }

    /**
     * 商品管理 首页
     * */
    public function switchStoreHome()
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('请选择操作参数');
        }

        $res = Mine::switchStoreHome($this->store_id, $id);
        if (!$res) {
            $this->error('操作失败');
        } else {
            $this->success('操作成功');
        }
    }


    /**
     * 商品管理 新品
     * */
    public function switchNew()
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('请选择操作参数');
        }

        $res = Mine::switchGoodsNew($this->store_id, $id);
        if (!$res) {
            $this->error('操作失败');
        } else {
            $this->success('操作成功');
        }
    }

    /**
     * 商品管理 热销
     * */
    public function switchHot()
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('请选择操作参数');
        }

        $res = Mine::switchGoodsHot($this->store_id, $id);
        if (!$res) {
            $this->error('操作失败');
        } else {
            $this->success('操作成功');
        }
    }

    /**
     * 商品管理 特供
     * */
    public function switchSpecial()
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('请选择操作参数');
        }

        $res = Mine::switchGoodsSpecial($this->store_id, $id);
        if (!$res) {
            $this->error('操作失败');
        } else {
            $this->success('操作成功');
        }
    }

    /**
     * 商品管理 优惠
     * */
    public function switchDiscount()
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('请选择操作参数');
        }

        $res = Mine::switchGoodsDiscount($this->store_id, $id);
        if (!$res) {
            $this->error('操作失败');
        } else {
            $this->success('操作成功');
        }
    }

    /**
     * 商品管理 删除
     * */
    public function deleteGoods()
    {
        $ids = $this->request->param('ids');
        if (empty($ids)) {
            $this->error('请选择操作参数');
        }

        $ids = explode(',', $ids);
        $res = Mine::deleteGoods($this->store_id, $ids);
        if (!$res) {
            $this->error('操作失败');
        } else {
            $this->success('操作成功');
        }
    }


    /**
     * 参加团购
     * */
    public function groupbuy()
    {
        $id = $this->request->param('id');
        if ($this->request->isPost()) {
            $groupbuy_id = $this->request->param('groupbuy_id');
            if (empty($groupbuy_id)) {
                $this->error('请选择限时团购');
            }

            $res = \app\api\model\Goods::joinGroupbuy($id, $groupbuy_id);
            if (!$res) {
                $this->error('操作失败');
            } else {
                $this->success('操作成功');
            }
        }

        $this->assign('groupbuy_list', Groupbuy::getGroupbuyList());
        $info = Mine::getGoods($this->store_id, $id);
        $this->assign('info', $info);
        return $this->view->fetch();
    }

    public function getGroupbuy()
    {
        $id = $this->request->param('id');
        $info = Groupbuy::getGroupbuy($id);
        if (!$info) {
            $this->result(null, 0, '操作失败');
        } else {
            $info['start_time'] = date('Y-m-d H:i:s', $info['start_time']);
            $info['end_time'] = date('Y-m-d H:i:s', $info['end_time']);
            $this->result([
                'info' => $info
            ], 1);
        }
    }

    /**
     * 参加特价
     * */
    public function discount()
    {
        $id = $this->request->param('id');
        if ($this->request->isPost()) {
            $discount_cate_id = $this->request->param('discount_cate_id');
            if (empty($discount_cate_id)) {
                $this->error('请选择特价分类');
            }

            $discount_price = (float)$this->request->param('discount_price');
            if ($discount_price <= 0) {
                $this->error('请设置特价价格');
            }

            $res = \app\api\model\Goods::joinDiscount($id, $discount_cate_id, $discount_price);
            if (!$res) {
                $this->error('操作失败');
            } else {
                $this->success('操作成功，请尽快联系平台缴纳费用。');
            }
        }

        $this->assign('discount_cate_list', Discount::getCateList());
        $info = Mine::getGoods($this->store_id, $id);
        $this->assign('info', $info);
        return $this->view->fetch();
    }

    /**
     * 参加VIP
     * */
    public function vip()
    {
        $id = $this->request->param('id');
        if ($this->request->isPost()) {
            $vip_cate_id = $this->request->param('vip_cate_id');
            if (empty($vip_cate_id)) {
                $this->error('请选择VIP分类');
            }

            $vip_price = (float)$this->request->param('vip_price');
            if ($vip_price <= 0) {
                $this->error('请设置VIP价格');
            }

            $res = \app\api\model\Goods::joinVip($id, $vip_cate_id, $vip_price);
            if (!$res) {
                $this->error('操作失败');
            } else {
                $this->success('操作成功');
            }
        }

        $this->assign('vip_cate_list', Vip::getCateList());
        $info = Mine::getGoods($this->store_id, $id);
        $this->assign('info', $info);
        return $this->view->fetch();
    }


}
