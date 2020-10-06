<?php
/**
 * Created by PhpStorm.
 * User: 18660
 * Date: 2020/6/21
 * Time: 6:37
 */

namespace app\api\model;

use think\Db;
use think\Exception;
use think\Config;

class Purchase
{
    /**
     * 分类列表
     * */
    public static function getPurchaseCateList($page, $limit)
    {
        $list = Db::name('purchase_cate')
            ->page($page)
            ->limit($limit)
            ->order('weigh asc')
            ->select();
        //halt($list);
        return $list;
    }

    /**
     * 分类名称
     * */
    public static function getCateName($id)
    {
        $val = Db::name('purchase_cate')
            ->where('id', $id)
            ->value('name');
        return $val;
    }

    /**
     * 分类信息
     * */
    public static function getCate($field, $value)
    {
        $info = Db::name('purchase_cate')
            ->where($field, $value)
            ->find();
        return $info;
    }

    /**
     * 下载表格文件
     * */
    public static function getDownloadSheet($title, $cate_name, $data,
                                            $p_company = '', $p_linkman = '', $p_link_phone = '',
                                            $s_company = '', $s_linkman = '', $s_link_phone = '')
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        //设置标题 分类
        $sheet->mergeCells("A1:H1");
        $sheet->getCell("A1")->setValue($title);
        $sheet->getCell("I1")->setValue($cate_name);

        //设置采购信息栏
        $sheet->getCell("A2")->setValue("采购单位");
        $sheet->mergeCells("B2:D2");
        $sheet->getCell("B2")->setValue($p_company);
        $sheet->getCell("E2")->setValue("联系人");
        $sheet->getCell("F2")->setValue($p_linkman);
        $sheet->getCell("G2")->setValue("联系电话");
        $sheet->mergeCells("H2:I2");
        $sheet->getCell("H2")->setValue($p_link_phone);

        //设置标题行
        $sheet->setCellValue('A3', '序号');
        $sheet->setCellValue('B3', '物料名称');
        $sheet->setCellValue('C3', '规格型号');
        $sheet->setCellValue('D3', '单位');
        $sheet->setCellValue('E3', '数量');
        $sheet->setCellValue('F3', '单价（元）');
        $sheet->setCellValue('G3', '金额（元）');
        $sheet->setCellValue('H3', '参数');
        $sheet->setCellValue('I3', '备注');

        //填充数据
        $sheet->fromArray($data, null, 'A4');

        //设置报价信息栏
        $ri = 3 + count($data) + 1;
        $sheet->getCell("A{$ri}")->setValue("报价单位");
        $sheet->mergeCells("B{$ri}:D{$ri}");
        $sheet->getCell("B{$ri}")->setValue($s_company);
        $sheet->getCell("E{$ri}")->setValue("联系人");
        $sheet->getCell("F{$ri}")->setValue($s_linkman);
        $sheet->getCell("G{$ri}")->setValue("联系电话");
        $sheet->mergeCells("H{$ri}:I{$ri}");
        $sheet->getCell("H{$ri}")->setValue($s_link_phone);

        //设置样式
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(25);

        //第一行高度
        $sheet->getRowDimension(1)->setRowHeight(40);

        //第ER行高度
        $sheet->getRowDimension(2)->setRowHeight(30);

        //第三行高度
        $sheet->getRowDimension(3)->setRowHeight(30);

        //倒数第一行高度
        $sheet->getRowDimension($ri)->setRowHeight(30);

        //其余行高度
        $re = $ri;
        for ($i = 4; $i <= ($re - 1); $i++) {
            $sheet->getRowDimension($i)->setRowHeight(20);
        }

        //单元格样式
        $styleArray = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ]
            ]
        ];
        $sheet->getStyle("A1:I{$re}")->applyFromArray($styleArray);
        return $spreadsheet;
    }

    /**
     * 上传采购单 获取数据
     * */
    public static function getUploadPurchaseInvoiceData($file, $type = 1)
    {
        $items = [];
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $sheet = $reader->load($file)->getActiveSheet();
        if (($re = $sheet->getHighestRow()) < 5) {
            return false;
        }

        //检查末尾空行 删除
        while ($re > 4) {
            $var = $sheet->getCell("A{$re}")->getValue();
            if (!$var) {
                $re--;
            } else {
                break;
            }
        }

        if ($re < 5) {
            return false;
        }

        $title = $sheet->getCell("A1")->getValue();
        $cate_name = $sheet->getCell("I1")->getValue();
        $company = $sheet->getCell("B2")->getValue();
        $linkman = $sheet->getCell("F2")->getValue();
        $link_phone = $sheet->getCell("H2")->getValue();

        $s_company = $sheet->getCell("B{$re}")->getValue();
        $s_linkman = $sheet->getCell("F{$re}")->getValue();
        $s_link_phone = $sheet->getCell("H{$re}")->getValue();

        $rs = 4;
        $re--;
        $goods_num = 0;
        for ($i = $rs; $i <= $re; $i++) {
            $item = [
                'item_no' => $sheet->getCell("A{$i}")->getValue(),
                'goods_name' => $sheet->getCell("B{$i}")->getValue(),
                'goods_spec' => $sheet->getCell("C{$i}")->getValue(),
                'goods_unit' => $sheet->getCell("D{$i}")->getValue(),
                'goods_num' => $sheet->getCell("E{$i}")->getValue(),
                'goods_price' => $sheet->getCell("F{$i}")->getValue(),
                'item_price' => $sheet->getCell("G{$i}")->getValue(),
                'goods_param' => $sheet->getCell("H{$i}")->getValue(),
                'remark' => $sheet->getCell("I{$i}")->getValue(),
            ];
            if ($type == 1) {
                if (empty($item['item_no']) || empty($item['goods_name']) || empty($item['goods_spec'])
                    || empty($item['goods_unit']) || empty($item['goods_num'])) {
                    return false;
                }
            } else {
                if (empty($item['item_no']) || empty($item['goods_name']) || empty($item['goods_spec'])
                    || empty($item['goods_unit']) || empty($item['goods_num']) || empty($item['goods_price'])
                    || empty($item['item_price']) || empty($item['goods_param']) || empty($item['remark'])) {
                    return false;
                }
            }
            $goods_num += $item['goods_num'];
            array_push($items, $item);
        }
        $ret = [
            'purchase' => [
                'title' => $title,
                'cate_name' => $cate_name,
                'company' => $company,
                'linkman' => $linkman,
                'link_phone' => $link_phone,
                'item_num' => count($items),
                'goods_num' => $goods_num,
                's_company' => $s_company,
                's_linkman' => $s_linkman,
                's_link_phone' => $s_link_phone,
            ],
            'item' => $items
        ];

        //halt($ret);
        return $ret;
    }

    /**
     * 采购单列表
     * */
    public static function getPurchaseList($page, $limit, $where = [])
    {
        $list = Db::name('purchase')
            ->where($where)
            ->page($page)
            ->limit($limit)
            ->order('id desc')
            ->select();

        foreach ($list as $key => $val) {
            $user = User::getUser('id', $val['user_id']);
            $list[$key]['user'] = [
                'id' => $user['id'],
                'avatar' => $user['avatar'],
                'nickname' => $user['nickname'],
            ];
        }
        return $list;
    }

    /**
     * 采购单详情
     * */
    public static function getPurchase($id)
    {
        $info = Db::name('purchase')
            ->where('id', $id)
            ->find();

        if ($info) {
            $user = User::getUser('id', $info['user_id']);
            $info['user'] = [
                'id' => $user['id'],
                'avatar' => $user['avatar'],
                'nickname' => $user['nickname'],
            ];

            $items = Db::name('purchase_item')
                ->where('purchase_id', $id)
                ->order('item_no ASC')
                ->select();
            $info['item'] = $items;
        }
        return $info;
    }

    /**
     * 采购单详情 添加供货
     * */
    public static function getPurchaseSupply($info)
    {
        $list = Db::name('purchase_supply')
            ->where('purchase_id', $info['id'])
            ->select();

        foreach ($list as $key => $val) {
            $user = User::getUser('id', $val['user_id']);
            $list[$key]['user'] = [
                'id' => $user['id'],
                'avatar' => $user['avatar'],
                'nickname' => $user['nickname'],
            ];
        }
        $info['supply'] = $list;
        return $info;
    }


    /**
     * 采购单详情 下载表格
     * */
    public static function getPurchaseForDownload($id)
    {
        $info = Db::name('purchase')
            ->where('id', $id)
            ->find();

        $user = User::getUser('id', $info['user_id']);
        $info['user'] = [
            'id' => $user['id'],
            'avatar' => $user['avatar'],
            'nickname' => $user['nickname'],
        ];

        $items = [];
        $list = Db::name('purchase_item')
            ->where('purchase_id', $id)
            ->order('item_no ASC')
            ->select();
        foreach ($list as $key => $val) {
            $item = [
                $val['item_no'], $val['goods_name'], $val['goods_spec'], $val['goods_unit'], $val['goods_num'],
                '', '', $val['goods_param'], $val['remark'],
            ];
            array_push($items, $item);
        }

        $info['item'] = $items;
        return $info;
    }

    /**
     * 供货单详情 下载表格
     * */
    public static function getSupplyForDownload($id)
    {
        $info = Db::name('purchase_supply')
            ->where('id', $id)
            ->find();

        $user = User::getUser('id', $info['user_id']);
        $info['user'] = [
            'id' => $user['id'],
            'avatar' => $user['avatar'],
            'nickname' => $user['nickname'],
        ];

        $items = [];
        $list = Db::name('purchase_supply_item')
            ->where('supply_id', $id)
            ->order('item_no ASC')
            ->select();
        foreach ($list as $key => $val) {
            $item = [
                $val['item_no'], $val['goods_name'], $val['goods_spec'], $val['goods_unit'], $val['goods_num'],
                $val['goods_price'], $val['item_price'], $val['goods_param'], $val['remark'],
            ];
            array_push($items, $item);
        }

        $info['item'] = $items;
        return $info;
    }


    /**
     * 供货单列表
     * */
    public static function getSupplyList($page, $limit, $where = [])
    {
        $list = Db::name('purchase_supply')
            ->where($where)
            ->page($page)
            ->limit($limit)
            ->order('id desc')
            ->select();

        foreach ($list as $key => $val) {
            $purchase = Purchase::getPurchase($val['purchase_id']);
            $puser = User::getUser('id', $purchase['user_id']);
            $list[$key]['puser'] = [
                'id' => $puser['id'],
                'avatar' => $puser['avatar'],
                'nickname' => $puser['nickname'],
            ];

            $user = User::getUser('id', $val['user_id']);
            $list[$key]['user'] = [
                'id' => $user['id'],
                'avatar' => $user['avatar'],
                'nickname' => $user['nickname'],
            ];

            $items = Db::name('purchase_supply_item')
                ->where('supply_id', $val['id'])
                ->order('item_no ASC')
                ->select();
            $list[$key]['item'] = $items;
        }
        return $list;
    }

}