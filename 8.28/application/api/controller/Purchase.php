<?php

namespace app\api\controller;

use app\api\model\Cont;
use app\common\controller\Api;
use PhpOffice\PhpSpreadsheet\Helper\Sample;
use think\Db;
use think\Exception;


/**
 * 采购报价
 */
class Purchase extends Api
{
    protected $noNeedLogin = [
        'getCarouselList', 'getPurchaseCateList', 'getPurchaseList', 'getPurchaseListByCate', 'downloadPurchaseInvoice'
    ];

    protected $noNeedRight = '*';

    /**
     * 是否商家
     * */
    public function isShop()
    {
        $user = $this->auth->getUser();
        $this->success('success', [
            'info' => [
                'is_shop' => \app\api\model\User::is_shop($user->id),
                'id_auth' => $user->id_auth == 2 ? true : false,
                'vip_auth' => $user->vip_auth == 2 ? true : false,
            ]
        ]);
    }

    /**
     * 顶部轮播
     * */
    public function getCarouselList()
    {
        $list = Cont::getGroupCarouselList('purchase');
        $this->success('success', ['list' => $list]);
    }

    /**
     * 分类列表
     * */
    public function getPurchaseCateList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $list = \app\api\model\Purchase::getPurchaseCateList($page, $limit);
        $this->success('success', [
            'list' => $list
        ]);
    }

    /**
     * 报价列表
     * */
    public function getPurchaseList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $list = \app\api\model\Purchase::getPurchaseList($page, $limit);
        $this->success('success', [
            'list' => $list
        ]);
    }

    /**
     * 报价列表 分类
     * */
    public function getPurchaseListByCate()
    {
        $cate_id = $this->request->param('cate_id');
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $list = \app\api\model\Purchase::getPurchaseList($page, $limit, [
            'cate_id' => $cate_id
        ]);
        $this->success('success', [
            'list' => $list
        ]);
    }

    /**
     * 报价详情
     * */
    public function getPurchase()
    {
        $id = $this->request->param('id');

        $user = $this->auth->getUser();
        if ($user->vip_auth != 2) {
            $this->error('非VIP无法查看详情');
        }

        if (!\app\api\model\User::is_shop($user->id)) {
            $this->error('非企业用户无法查看详情');
        }

        $info = \app\api\model\Purchase::getPurchase($id);
        $this->success('success', [
            'info' => $info
        ]);
    }

    /**
     * 下载采购单
     * */
    public function downloadPurchaseInvoice()
    {
        $id = $this->request->param('id');

        $purchase = \app\api\model\Purchase::getPurchaseForDownload($id);
        $spreadsheet = \app\api\model\Purchase::getDownloadSheet($purchase['title'], $purchase['cate_name'],
            $purchase['item'], $purchase['company'], $purchase['linkman'], $purchase['link_phone']);

        // MIME 协议，文件的类型，不设置，会默认html
        header('Content-Type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        // MIME 协议的扩展
        header("Content-Disposition:attachment;filename=purchase_{$id}.xlsx");
        // 缓存控制
        header('Cache-Control:max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        // php://output 它是一个只写数据流, 允许你以 print 和 echo一样的方式写入到输出缓冲区。
        $writer->save('php://output');
        exit;
    }

    /**
     * 上传报价单
     * */
    public function uploadSupplyInvoice()
    {
        $id = $this->request->param('id'); //采购ID
        $file = $this->request->file('file');
        if (empty($file)) {
            $this->error("上传文件为空");
        }

        $user = $this->auth->getUser();
        if ($user->vip_auth != 2) {
            $this->error('非VIP无法查看详情');
        }

        if (!\app\api\model\User::is_shop($user->id)) {
            $this->error('非企业用户无法查看详情');
        }

        $fileInfo = $file->getInfo();
        $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix = $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';
        if ($suffix != 'xlsx') {
            $this->error("上传文件格式错误");
        }
//        halt($fileInfo);

        $fileData = \app\api\model\Purchase::getUploadPurchaseInvoiceData($fileInfo['tmp_name']);
        if ($fileData === false) {
            $this->error("数据格式错误");
        }

        Db::startTrans();
        try {
            $purchase = $fileData['purchase'];

            $user = $this->auth->getUser();
            $row = [
                'title' => $purchase['title'],
                'cate_name' => $purchase['cate_name'],
                'company' => $purchase['company'],
                'linkman' => $purchase['linkman'],
                'link_phone' => $purchase['link_phone'],
                'item_num' => $purchase['item_num'],
                'goods_num' => $purchase['goods_num'],
                'purchase_id' => $id,
                'createtime' => time(),
                'user_id' => $user->id,
                's_company' => $purchase['s_company'],
                's_linkman' => $purchase['s_linkman'],
                's_link_phone' => $purchase['s_link_phone'],
            ];

            $id = Db::name('purchase_supply')->insertGetId($row);
            foreach ($fileData['item'] as $item) {
                $row = [
                    'supply_id' => $id,
                    'item_no' => $item['item_no'],
                    'goods_name' => $item['goods_name'],
                    'goods_spec' => $item['goods_spec'],
                    'goods_unit' => $item['goods_unit'],
                    'goods_num' => $item['goods_num'],
                    'goods_price' => $item['goods_price'],
                    'item_price' => $item['item_price'],
                    'goods_param' => $item['goods_param'],
                    'remark' => $item['remark'],
                ];
                Db::name('purchase_supply_item')->insert($row);
            }

            //添加报价次数
            $store = \app\api\model\Shop::getStoreByUserId($user->id);
            \app\api\model\Shop::incStoreSupply($store['id'], $purchase['goods_num']);
            Db::commit();
            $this->success('操作成功');
        } catch (Exception $e) {
            Db::rollback();
            $this->error('操作失败，请重新操作。');
        }
    }

    /**
     * 上传报价单2 数据格式错误返回code99
     * */
    public function uploadSupplyInvoice2()
    {
        $id = $this->request->param('id'); //采购ID
        $file = $this->request->file('file');
        if (empty($file)) {
            $this->error("上传文件为空");
        }

        $user = $this->auth->getUser();
        if ($user->vip_auth != 2) {
            $this->error('非VIP无法查看详情');
        }

        if (!\app\api\model\User::is_shop($user->id)) {
            $this->error('非企业用户无法查看详情');
        }

        $fileInfo = $file->getInfo();
        $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix = $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';
        if ($suffix != 'xlsx') {
            $this->error("上传文件格式错误");
        }
//        halt($fileInfo);

        $fileData = \app\api\model\Purchase::getUploadPurchaseInvoiceData($fileInfo['tmp_name']);
        if ($fileData === false) {
            $this->result('数据格式错误', null, 99);
        }

        Db::startTrans();
        try {
            $purchase = $fileData['purchase'];

            $user = $this->auth->getUser();
            $row = [
                'title' => $purchase['title'],
                'cate_name' => $purchase['cate_name'],
                'company' => $purchase['company'],
                'linkman' => $purchase['linkman'],
                'link_phone' => $purchase['link_phone'],
                'item_num' => $purchase['item_num'],
                'goods_num' => $purchase['goods_num'],
                'purchase_id' => $id,
                'createtime' => time(),
                'user_id' => $user->id,
                's_company' => $purchase['s_company'],
                's_linkman' => $purchase['s_linkman'],
                's_link_phone' => $purchase['s_link_phone'],
            ];

            $id = Db::name('purchase_supply')->insertGetId($row);
            foreach ($fileData['item'] as $item) {
                $row = [
                    'supply_id' => $id,
                    'item_no' => $item['item_no'],
                    'goods_name' => $item['goods_name'],
                    'goods_spec' => $item['goods_spec'],
                    'goods_unit' => $item['goods_unit'],
                    'goods_num' => $item['goods_num'],
                    'goods_price' => $item['goods_price'],
                    'item_price' => $item['item_price'],
                    'goods_param' => $item['goods_param'],
                    'remark' => $item['remark'],
                ];
                Db::name('purchase_supply_item')->insert($row);
            }

            //添加报价次数
            $store = \app\api\model\Shop::getStoreByUserId($user->id);
            \app\api\model\Shop::incStoreSupply($store['id'], $purchase['goods_num']);
            Db::commit();
            $this->success('操作成功');
        } catch (Exception $e) {
            Db::rollback();
            $this->error('操作失败，请重新操作。');
        }
    }


}
