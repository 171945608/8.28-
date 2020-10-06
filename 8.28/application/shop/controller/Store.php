<?php

namespace app\shop\controller;

use app\api\model\Mine;
use app\api\model\Shop;
use think\Config;
use think\Hook;
use think\Response;
use think\Session;
use think\Validate;

/**
 * 店铺管理
 */
class Store extends Base
{
    protected $store_id    = 0;
    protected $login_times = 999;

    public function _initialize()
    {
        parent::_initialize();
        //移除HTML标签
        $this->request->filter('trim,strip_tags,htmlspecialchars');
        $this->store_id = session('store.id');
    }

    public function getLoginTimes()
    {
        if ($this->request->isPost()) {
            $shop_id = session('shop.id');
            $shop = Shop::getShop('id', $shop_id);
            $loginTimes = empty($shop) ? 999 : $shop['login_times'];

            $loginTimes == 0 && Shop::incLoginTimes($shop_id);
            $this->result([
                'loginTimes' => $loginTimes
            ], 1);
        }
    }

    /**
     * 基本资料
     * */
    public function chgPwd()
    {
        if ($this->request->isPost()) {
            $oldPwd = $this->request->param('oldPwd');
            $newPwd = $this->request->param('newPwd');
            $rePwd = $this->request->param('rePwd');

            $pwdReg = '^\w{6,30}$';
            if (!Validate::regex($oldPwd, $pwdReg)) {
                $this->error('旧的密码格式错误');
            }

            if (!Validate::regex($newPwd, $pwdReg)) {
                $this->error('新的密码格式错误');
            }

            $newPwd !== $rePwd && $this->error('重复密码与新的密码不一致');

            $shop_id = Session::get('shop.id');
            $shop = Shop::getShop('id', $shop_id);
            if (md5(md5($oldPwd) . $shop['salt']) <> $shop['password']) {
                $this->error('旧的密码错误');
            }

            $res = Shop::setPwd($newPwd, $shop_id);
            if (!$res) {
                $this->error('操作失败');
            } else {
                $this->success('操作成功');
            }
        }

        return $this->view->fetch();
    }

    /**
     * 基本资料
     * */
    public function index()
    {
        if ($this->request->isPost()) {
            $name = $this->request->param('name');
            $phone = $this->request->param('phone');
            $logo = $this->request->param('logo');
            $qualification = $this->request->param('qualification');
            $image = $this->request->param('image');

            $res = Mine::setStore($this->store_id, [
                'name' => $name,
                'phone' => $phone,
                'logo' => $logo,
                'qualification' => $qualification,
                'image' => $image,
                'area' => $this->request->param('area'),
                'address' => $this->request->param('address'),
                'brand' => $this->request->param('brand'),
                'business' => $this->request->param('business'),
                'tag' => $this->request->param('tag'),
            ]);
            if (!$res) {
                $this->error('操作失败');
            } else {
                $this->success('操作成功');
            }
        }

        $store = Mine::getStore('id', $this->store_id);
        $this->assign('store', $store);
        return $this->view->fetch();
    }

    /**
     * 关于我们
     * */
    public function about()
    {
        if ($this->request->isPost()) {

            $res = Mine::setStore($this->store_id, [
                'about' => $_POST['about'],
            ]);
            if (!$res) {
                $this->error('操作失败');
            } else {
                $this->success('操作成功');
            }
        }

        $info = Mine::getStore('id', $this->store_id);
        $this->assign('info', $info);
        return $this->view->fetch();
    }

    /**
     * 店铺轮播 列表
     * */
    public function carousel()
    {
        if ($this->request->isPost()) {
            $page = $this->request->param('page');
            $limit = $this->request->param('limit');

            $list = Mine::getStoreCarouselList($this->store_id, $page, $limit);
            $count = Mine::getStoreCarouselCount($this->store_id);
            return $this->getResponse($list, $count);
        }

        return $this->view->fetch();
    }

    /**
     * 店铺轮播 添加
     * */
    public function addCarousel()
    {
        if ($this->request->isPost()) {
            $res = Mine::addStoreCarousel($this->store_id, [
                'image' => $this->request->param('image'),
                'link' => $_POST['link'],
                'weigh' => $this->request->param('weigh'),
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
     * 店铺轮播 编辑
     * */
    public function editCarousel()
    {
        $id = $this->request->param('id');
        if ($this->request->isPost()) {
            $res = Mine::editStoreCarousel($this->store_id, $id, [
                'image' => $this->request->param('image'),
                'link' => $_POST['link'],
                'weigh' => $this->request->param('weigh'),
            ]);
            if (!$res) {
                $this->error('操作失败');
            } else {
                $this->success('操作成功');
            }
        }

        $carousel = Mine::getStoreCarousel($this->store_id, $id);
        $this->assign('carousel', $carousel);
        return $this->view->fetch();
    }

    /**
     * 店铺轮播 删除
     * */
    public function deleteCarousel()
    {
        $ids = $this->request->param('ids');
        if (empty($ids)) {
            $this->error('请选择操作参数');
        }

        $ids = explode(',', $ids);
        $res = Mine::deleteStoreCarousel($this->store_id, $ids);
        if (!$res) {
            $this->error('操作失败');
        } else {
            $this->success('操作成功');
        }
    }


    /**
     * 店铺公告 列表
     * */
    public function notice()
    {
        if ($this->request->isPost()) {
            $page = $this->request->param('page');
            $limit = $this->request->param('limit');

            $list = Mine::getStoreNoticeList($this->store_id, $page, $limit);
            $count = Mine::getStoreNoticeCount($this->store_id);
            return $this->getResponse($list, $count);
        }

        return $this->view->fetch();
    }

    /**
     * 店铺公告 新增
     * */
    public function addNotice()
    {
        if ($this->request->isPost()) {
            $res = Mine::addStoreNotice($this->store_id, [
                'title' => $this->request->param('title'),
                'content' => $_POST['content'],
                'weigh' => $this->request->param('weigh'),
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
     * 店铺公告 编辑
     * */
    public function editNotice()
    {
        $id = $this->request->param('id');
        if ($this->request->isPost()) {
            //halt($_POST);
            $res = Mine::editStoreNotice($this->store_id, $id, [
                'title' => $this->request->param('title'),
                'content' => $_POST['content'],
                'weigh' => $this->request->param('weigh'),
            ]);
            if (!$res) {
                $this->error('操作失败');
            } else {
                $this->success('操作成功');
            }
        }

        $info = Mine::getStoreNotice($this->store_id, $id);
        $this->assign('info', $info);
        return $this->view->fetch();
    }

    /**
     * 店铺公告 删除
     * */
    public function deleteNotice()
    {
        $ids = $this->request->param('ids');
        if (empty($ids)) {
            $this->error('请选择操作参数');
        }

        $ids = explode(',', $ids);
        $res = Mine::deleteStoreNotice($this->store_id, $ids);
        if (!$res) {
            $this->error('操作失败');
        } else {
            $this->success('操作成功');
        }
    }

}
