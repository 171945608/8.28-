<?php

namespace app\api\controller\mine;

use app\api\model\Shop;
use app\api\model\User;
use app\api\model\Goods;
use app\api\model\Mine;
use app\common\controller\Api;

/**
 * 我的店铺
 * */
class Store extends Api
{
    protected $noNeedLogin = [''];
    protected $noNeedRight = '*';

    //店铺信息
    public function getStore()
    {
        $login = $this->auth->getUser();

        $store = Shop::getStoreByUserId($login->id);
        $this->success('success', [
            'info' => $store
        ]);
    }

//设置封面
    public function setStoreImage()
    {
        $image = $this->request->param('image');

        $login = $this->auth->getUser();
        $res = Shop::setStoreImage($login->id, $image);

        if (!$res) {
            $this->error('操作失败');
        } else {
            $this->success('操作成功');
        }
    }

    //设置名称
    public function setStoreName()
    {
        $name = $this->request->param('name');

        $login = $this->auth->getUser();
        $res = Shop::setStoreName($login->id, $name);

        if (!$res) {
            $this->error('操作失败');
        } else {
            $this->success('操作成功');
        }
    }

    //设置电话
    public function setStorePhone()
    {
        $phone = $this->request->param('phone');

        $login = $this->auth->getUser();
        $res = Shop::setStorePhone($login->id, $phone);

        if (!$res) {
            $this->error('操作失败');
        } else {
            $this->success('操作成功');
        }
    }

    //设置资质文件
    public function setStoreQualification()
    {
        $qualification = $this->request->param('qualification');

        $login = $this->auth->getUser();
        $res = Shop::setStoreQualification($login->id, $qualification);

        if (!$res) {
            $this->error('操作失败');
        } else {
            $this->success('操作成功');
        }
    }

    //设置LOGO
    public function setStoreLogo()
    {
        $logo = $this->request->param('logo');

        $login = $this->auth->getUser();
        $res = Shop::setStoreLogo($login->id, $logo);

        if (!$res) {
            $this->error('操作失败');
        } else {
            $this->success('操作成功');
        }
    }

    //分组列表
    public function getGroupList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $login = $this->auth->getUser();
        $list = Shop::getGroupList($login->id, $page, $limit);

        $this->success('success', [
            'list' => $list
        ]);
    }


    //新增分组
    public function addGroup()
    {
        $name = $this->request->param('name');

        $login = $this->auth->getUser();
        $iid = Shop::addGroup($login->id, $name);
        $iid < 1 && $this->error('操作失败');

        $info = Shop::getGroup($iid);
        $this->success('操作成功', [
            'info' => $info
        ]);
    }

    //编辑分组
    public function editGroup()
    {
        $id = $this->request->param('id');
        $name = $this->request->param('name');

        $login = $this->auth->getUser();
        $res = Shop::editGroup($login->id, $id, $name);
        !$res && $this->error('操作失败');

        $this->success('操作成功');
    }

    //删除分组
    public function delGroup()
    {
        $id = $this->request->param('id');

        $login = $this->auth->getUser();
        $res = Shop::delGroup($login->id, $id);
        if (!$res) {
            $this->error('操作失败');
        } else {
            $this->success('操作成功');
        }
    }

    //切换分组状态
    public function swGroupStatus()
    {
        $id = $this->request->param('id');

        $login = $this->auth->getUser();
        $res = Shop::swGroupStatus($login->id, $id);
        if (!$res) {
            $this->error('操作失败');
        } else {
            $this->success('操作成功');
        }
    }

}