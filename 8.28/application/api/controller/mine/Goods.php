<?php
/**
 * @author 见龙在野
 * @date 2020-08-08
 */

namespace app\api\controller\mine;

use app\api\model\Mine;
use app\api\model\Shop;
use app\common\controller\Api;
use think\Db;
use think\Exception;

/**
 * 我的商品
 * */
class Goods extends Api
{

    protected $noNeedLogin = [''];
    protected $noNeedRight = '*';


    //上架商品列表
    public function getGoodsOnShelf()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $login = $this->auth->getUser();
        $store = Shop::getStoreByUserId($login->id);

        $list = \app\api\model\Goods::getGoodsList($page, $limit, [
            'store_id' => $store['id'],
            'state' => 1,
            'audit' => 2,
        ]);

        $this->success('success', [
            'list' => $list
        ]);
    }

    //商品下架
    public function downShelf()
    {
        $ids = $this->request->param('ids');

        $login = $this->auth->getUser();
        $store = Shop::getStoreByUserId($login->id);

        Db::name('goods')
            ->where('store_id', $store['id'])
            ->where('id', 'in', $ids)
            ->update([
                'state' => 0
            ]);
        $this->success('');
    }


    //下架商品列表 含未审核 或 审核失败
    public function getGoodsDownShelf()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $login = $this->auth->getUser();
        $store = Shop::getStoreByUserId($login->id);

        $list = Db::name('goods')
            ->where('store_id', $store['id'])
            ->where(function ($query) {
                $query->where('state', '<>', 1)->whereOr('audit', '<>', 2);
            })
            ->page($page)
            ->limit($limit)
            ->order('id desc')
            ->select();

        $this->success('success', [
            'list' => $list
        ]);
    }

    //商品上架
    public function upShelf()
    {
        $ids = $this->request->param('ids');

        $login = $this->auth->getUser();
        $store = Shop::getStoreByUserId($login->id);

        $ids = explode(',', $ids);
        foreach ($ids as $id) {
            $goods = Db::name('goods')
                ->where('store_id', $store['id'])
                ->where('id', $id)
                ->find();

            if (!empty($goods)) {
                if ($goods['audit'] == 2) {
                    $update = [
                        'state' => 1
                    ];
                } else {
                    $update = [
                        'state' => 1,
                        'audit' => 1,
                        'audit_msg' => ''
                    ];
                }
                Db::name('goods')->where('id', $id)->update($update);
            }
        }
        $this->success('');
    }

    //商品删除
    public function delGoods()
    {
        $ids = $this->request->param('ids');

        $login = $this->auth->getUser();
        $store = Shop::getStoreByUserId($login->id);

        $res = Mine::deleteGoods($store['id'], $ids);
        if (!$res) {
            $this->error('操作失败');
        } else {
            $this->success('操作成功');
        }
    }

    //取消审核
    public function cancelAudit()
    {
        $id = $this->request->param('id');

        $login = $this->auth->getUser();
        $store = Shop::getStoreByUserId($login->id);

        Db::name('goods')
            ->where('store_id', $store['id'])
            ->where('id', $id)
            ->update([
                'audit' => 0,
                'audit_msg' => ''
            ]);
        $this->success('');
    }

    //商品分类
    public function getCates()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $login = $this->auth->getUser();
        $store = Shop::getStoreByUserId($login->id);

        $list = Db::name('goods_cate_system')
            ->page($page)
            ->limit($limit)
            ->order('weigh desc')
            ->select();
        $this->success('', [
            'list' => $list
        ]);
    }

    //商品信息
    public function getGoods()
    {
        $id = $this->request->param('id');

        $login = $this->auth->getUser();
        $store = Shop::getStoreByUserId($login->id);

        $goods = Db::name('goods')
            ->where('store_id', $store['id'])
            ->where('id', $id)
            ->find();

        $this->success('', [
            'info' => $goods
        ]);
    }

    //编辑商品
    public function editGoods()
    {
        $id = $this->request->param('id');
        $image = $this->request->param('image');
        $images = $this->request->param('images');
        $name = $this->request->param('name');
        $cate_id = $this->request->param('cate_id');
        $group_ids = $this->request->param('group_ids');
        $detail = $_POST['detail'];
        $video = $this->request->param('video');
        $price = $this->request->param('price');

        $login = $this->auth->getUser();
        $store = Shop::getStoreByUserId($login->id);
        $cate = Db::name('goods_cate_system')->where('id', $cate_id)->find();
        Db::startTrans();
        try {
            Db::name('goods')
                ->where('id', $id)
                ->where('store_id', $store['id'])
                ->update([
                    'name' => $name,
                    'image' => $image,
                    'images' => $images,
                    'video' => $video,
                    'cate_id' => $cate['id'],
                    'cate_name' => $cate['name'],
                    'group_ids' => $group_ids,
                    'price' => $price,
                    'oprice' => $price * 1.1,
                    'detail' => $detail,
                ]);

            Db::commit();
            $this->success('操作成功');
        } catch (Exception $e) {
            Db::rollback();
            $this->error('操作失败');
        }
    }

    //新增商品
    public function addGoods()
    {
        $images = $this->request->param('images');
        $image = $this->request->param('image');
        $name = $this->request->param('name');
        $cate_id = $this->request->param('cate_id');
        $group_ids = $this->request->param('group_ids');
        $detail = $_POST['detail'];
        $video = $this->request->param('video');
        $price = $this->request->param('price');

        $login = $this->auth->getUser();
        $store = Shop::getStoreByUserId($login->id);
        $cate = Db::name('goods_cate_system')->where('id', $cate_id)->find();
        $data = [
            'name' => $name,
            'image' => $image,
            'images' => $images,
            'video' => $video,
            'cate_id' => $cate['id'],
            'cate_name' => $cate['name'],
            'group_ids' => $group_ids,
            'price' => $price,
            'oprice' => $price * 1.1,
            'detail' => $detail,
            'equities' => '',
            'delivery' => 3,
            'new' => 0,
            'hot' => 0,
            'special' => 0,
            'audit' => 1,
            'audit_msg' => '',
            'state' => 0,
            'store_id' => $store['id'],
            'home' => 0,
            'cate' => 0,
            'discount' => 0,
            'store_home' => 0,
        ];

        $res = Db::name('goods')->insert($data);
        if (!$res) {
            $this->error("操作失败");
        } else {
            $this->success("操作成功");
        }
    }

    //分组列表
    public function getGroupList2()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $login = $this->auth->getUser();
        $list = Shop::getGroupList2($login->id, $page, $limit);

        $this->success('success', [
            'list' => $list
        ]);
    }


}