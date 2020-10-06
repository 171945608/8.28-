<?php

namespace app\api\controller;

use app\api\model\Delivery;
use app\api\model\WorkFind;
use app\api\model\WorkSupply;
use app\common\controller\Api;
use app\api\model\Mine as Model;
use function fast\e;
use think\Db;
use think\Exception;
use think\Hook;

/**
 * 我的模块
 */
class Mine extends Api
{
    protected $noNeedLogin = ['downloadPurchaseTemplate', 'downloadSupplyInvoice'];
    protected $noNeedRight = '*';


    /**
     * 商家入驻 经营类目
     */
    public function getBusinessCateList()
    {
        $page = $this->request->request("page");
        $limit = $this->request->request("limit");
        $list = Model::getBusinessCateList($page, $limit);
        $this->success('success', ['list' => $list]);
    }

    /**
     * 商家入驻 提交资料
     */
    public function beingSettled()
    {
        $business_cate_id = $this->request->request("business_cate_id");
        $link_man = $this->request->request("link_man");
        $link_mobile = $this->request->request("link_mobile");
        $link_email = $this->request->request("link_email");
        $idcard_ps = $this->request->request("idcard_ps");
        $idcard_rs = $this->request->request("idcard_rs");
        $business_license = $this->request->request("business_license");
        $qualification = $this->request->request("qualification", '');
        $company = $this->request->request("company");
        $address = $this->request->request("address");

        if (!check_name_cn($link_man)) {
            $this->error('姓名格式错误');
        }

        if (!check_mobile($link_mobile)) {
            $this->error('电话格式错误');
        }

        if (!check_email($link_email)) {
            $this->error('邮箱格式错误');
        }

        if (!$idcard_ps || !$idcard_rs || !$business_license) {
            $this->error('提交资料不全');
        }

        $shop = \app\api\model\Mine::getShop('username', $link_mobile);
        if (!empty($shop)) {
            $this->error('电话已被占用');
        }

        $user = $this->auth->getUser();
        $res = Model::createShop($business_cate_id, $link_man, $link_mobile, $link_email, $idcard_ps, $idcard_rs, $business_license, $qualification, $user->id, $company, $address);
        if ($res['code']) {
            $this->success('success');
        } else {
            $this->error($res['msg']);
        }
    }

    /**
     * 商家入驻 审核状态
     */
    public function getAuditState()
    {
        $user = $this->auth->getUser();
        $shop = Model::getShop('user_id', $user->id);
        if ($shop) {
            $audit_state = $shop['audit_state'];
        } else {
            $audit_state = 0;
        }

        $arr = [
            0 => '未申请', 10 => '提交申请', 20 => '审核驳回', 30 => '审核通过'
        ];
        $this->success('success', [
            'audit_state' => $audit_state,
            'audit_state_text' => $arr[$audit_state],
        ]);
    }

    /**
     * 首页
     * */
    public function home()
    {
        $user = $this->auth->getUser();
        $info = [
            'id' => $user->id,
            'avatar' => $user->avatar,
            'nickname' => $user->nickname,
            'star' => \app\api\model\Community::countUserStar($user->id),
            'follow' => \app\api\model\Community::countUserFollow($user->id),
            'tv' => $user->tv,
            'topic' => \app\api\model\Community::countUserTopic($user->id),
        ];
        $this->success('success', ['info' => $info]);
    }

    /**
     * 个人资料
     * */
    public function profile()
    {
        $user = $this->auth->getUser();
        $info = [
            'id' => $user->id,
            'avatar' => $user->avatar,
            'nickname' => $user->nickname,
            'id_auth' => $user->id_auth,
        ];
        $this->success('success', ['info' => $info]);
    }

    /**
     * 设置头像
     * */
    public function setAvatar()
    {
        $avatar = $this->request->param('avatar');
        $user = $this->auth->getUser();
        $res = \app\api\model\User::setUser($user->id, ['avatar' => $avatar]);
        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功');
        }
    }

    /**
     * 设置昵称
     * */
    public function setNickname()
    {
        $nickname = $this->request->param('nickname');
        $user = $this->auth->getUser();
        $res = \app\api\model\User::setUser($user->id, ['nickname' => $nickname]);
        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功');
        }
    }

    /**
     * 身份认证
     * */
    public function setIdAuth()
    {
        $realname = $this->request->param('realname');
        $idcard_no = $this->request->param('idcard_no');
        $idcard_ps = $this->request->param('idcard_ps');
        $idcard_rs = $this->request->param('idcard_rs');

        //过滤
        if (!check_name_cn($realname)) {
            $this->error('真实姓名格式错误');
        }

        if (!check_idcard($idcard_no)) {
            $this->error('身份证号格式错误');
        }

        if ($idcard_ps == '') {
            $this->error('身份证正面必须');
        }

        if ($idcard_rs == '') {
            $this->error('身份证反面必须');
        }

        $user = $this->auth->getUser();
        if ($user->id_auth != 0) {
            $this->error('请勿重复认证');
        }

        $res = \app\api\model\User::setUser($user->id, [
            'id_auth' => 1,
            'realname' => $realname,
            'idcard_no' => $idcard_no,
            'idcard_ps' => $idcard_ps,
            'idcard_rs' => $idcard_rs,
        ]);

        $obj = new \stdClass();
        $obj->mobile = \app\api\model\User::getIdAuthReceiver();
        Hook::listen('user_id_auth', $obj);

        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功');
        }
    }

    //认证资料回显
    public function getIdAuth()
    {
        $login = $this->auth->getUser();
        $user = \app\api\model\User::getUser('id', $login->id);
        $this->success('', [
            'info' => [
                'realname' => $user['realname'],
                'idcard_no' => $user['idcard_no'],
                'idcard_ps' => $user['idcard_ps'],
                'idcard_rs' => $user['idcard_rs'],
            ]
        ]);
    }

    /**
     * VIP认证
     * */
    public function setVipAuth()
    {
        $vipname = $this->request->param('vipname');
        $viplink = $this->request->param('viplink');

        //过滤
        if (!check_name_cn($vipname)) {
            $this->error('姓名格式错误');
        }

        if ($viplink == '') {
            $this->error('联系方式必须');
        }

        $user = $this->auth->getUser();
        if ($user->vip_auth != 0) {
            $this->error('请勿重复认证');
        }

        $res = \app\api\model\User::setUser($user->id, [
            'vip_auth' => 1,
            'vipname' => $vipname,
            'viplink' => $viplink,
        ]);

        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功');
        }
    }

    /**
     * 个人微信 设置
     * */
    public function setWxQrcode()
    {
        $wx_qrcode = $this->request->param('wx_qrcode');
        $user = $this->auth->getUser();
        $res = \app\api\model\User::setUser($user->id, ['wx_qrcode' => $wx_qrcode]);
        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功');
        }
    }

    /**
     * 个人微信 获取
     * */
    public function getWxQrcode()
    {
        $user = $this->auth->getUser();
        $info = [
            'id' => $user->id,
            'wx_qrcode' => $user->wx_qrcode,
        ];
        $this->success('success', ['info' => $info]);
    }

    /**
     * 名片模板 列表
     * */
    public function getBcardTemplateList()
    {
        $list = \app\api\model\User::getBcardTemplateList();
        $this->success('success', ['list' => $list]);
    }

    /**
     * 名片模板 选择
     * */
    public function setBcardTemplate()
    {
        $tid = $this->request->param('id');

        $user = $this->auth->getUser();
        $res = \app\api\model\User::setBcardTemplate($user->id, $tid);
        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功');
        }
    }


    /**
     * 名片数据 获取
     * */
    public function getBcard()
    {
        $user = $this->auth->getUser();
        $bcard = \app\api\model\User::getBcard($user->id);
        $info = [
            'company' => empty($bcard) ? '' : $bcard['company'],
            'position' => empty($bcard) ? '' : $bcard['position'],
            'name' => empty($bcard) ? '' : $bcard['name'],
            'mobile' => empty($bcard) ? '' : $bcard['mobile'],
            'phone' => empty($bcard) ? '' : $bcard['phone'],
            'address' => empty($bcard) ? '' : $bcard['address'],
            'business' => empty($bcard) ? '' : $bcard['business'],
        ];
        $this->success('success', ['info' => $info]);
    }

    /**
     * 名片模板 设置
     * */
    public function setBcard()
    {
        $company = $this->request->param('company');
        $position = $this->request->param('position');
        $name = $this->request->param('name');
        $mobile = $this->request->param('mobile');
        $phone = $this->request->param('phone');
        $address = $this->request->param('address');
        $business = $this->request->param('business');

        $user = $this->auth->getUser();
        $res = \app\api\model\User::setBcard($user->id, $company, $position, $name, $mobile, $phone, $address, $business);
        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功');
        }
    }


    /**
     * 新增发货
     * */
    public function addDelivery()
    {
        $delivery_time = $this->request->param('delivery_time');
        $from_address = $this->request->param('from_address');
        $to_address = $this->request->param('to_address');
        $delivery_man = $this->request->param('delivery_man');
        $delivery_man_sex = $this->request->param('delivery_man_sex');
        $link_phone = $this->request->param('link_phone');
        $remark = $this->request->param('remark');

        $delivery_ts = strtotime($delivery_time);
        $tomorrow_ts = strtotime('+1 day', strtotime(date('Y-m-d')));
        if ($delivery_ts < $tomorrow_ts) {
            $this->error('发货时间不得早于' . date('Y-m-d', $tomorrow_ts));
        }

        $user = $this->auth->getUser();
        $res = Delivery::addDelivery($user->id, $delivery_time, $from_address, $to_address, $delivery_man, $delivery_man_sex, $link_phone, $remark);
        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功');
        }

    }

    /**
     * 发货列表
     * */
    public function getDeliveryList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $user = $this->auth->getUser();
        $list = Delivery::getDeliveryList($page, $limit, $where = [
            'user_id' => $user->id,
        ]);
        $this->success('success', [
            'list' => Delivery::formatDeliveryList($list)
        ]);
    }

    /**
     * 发货列表 搜索
     * */
    public function searchDelivery()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $word = $this->request->param('word');

        $user = $this->auth->getUser();
        $list = Delivery::getDeliveryList($page, $limit, $where = [
            'from_address' => ['like', "%{$word}%"],
            'user_id' => $user->id,
        ]);
        $this->success('success', [
            'list' => Delivery::formatDeliveryList($list)
        ]);
    }

    /**
     * 发货详情
     * */
    public function getDelivery()
    {
        $delivery_id = $this->request->param('id');

        $info = Delivery::getDelivery($delivery_id);
        $this->success('success', [
            'info' => $info
        ]);
    }


    /**
     * 变更信息
     * */
    public function setDelivery()
    {
        $delivery_id = $this->request->param('id');
        $delivery_time = $this->request->param('delivery_time');
        $from_address = $this->request->param('from_address');
        $to_address = $this->request->param('to_address');
        $delivery_man = $this->request->param('delivery_man');
        $delivery_man_sex = $this->request->param('delivery_man_sex');
        $link_phone = $this->request->param('link_phone');
        $remark = $this->request->param('remark');

        $delivery_ts = strtotime($delivery_time);
        $tomorrow_ts = strtotime('+1 day', strtotime(date('Y-m-d')));
        if ($delivery_ts < $tomorrow_ts) {
            $this->error('发货时间不得早于' . date('Y-m-d', $tomorrow_ts));
        }

        //halt($delivery_id);
        $res = Delivery::setDelivery($delivery_id, [
            'delivery_time' => strtotime($delivery_time),
            'from_address' => $from_address,
            'to_address' => $to_address,
            'delivery_man' => $delivery_man,
            'delivery_man_sex' => $delivery_man_sex,
            'link_phone' => $link_phone,
            'remark' => $remark,
        ]);
        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功');
        }
    }

    /**
     * 删除信息
     * */
    public function deleteDelivery()
    {
        $delivery_id = $this->request->param('id');

        $res = Delivery::deleteDelivery($delivery_id);
        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功');
        }
    }


    /**
     * 我提供活
     * */
    public function addWorkSupply()
    {
        $begin_time = $this->request->param('begin_time');
        $end_time = $this->request->param('end_time');
        $content = $this->request->param('content');
        $address = $this->request->param('address');
        $linkman = $this->request->param('linkman');
        $linkman_sex = $this->request->param('linkman_sex');
        $link_phone = $this->request->param('link_phone');

        $user = $this->auth->getUser();
        $res = WorkSupply::addWorkSupply($user->id, $begin_time, $end_time, $content, $address, $linkman, $linkman_sex, $link_phone);
        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功');
        }

    }

    /**
     * 供活列表
     * */
    public function getWorkSupplyList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $user = $this->auth->getUser();
        $list = WorkSupply::getWorkSupplyList($page, $limit, $where = [
            'user_id' => $user->id,
        ]);
        $this->success('success', [
            'list' => WorkSupply::formatWorkSupplyList($list)
        ]);
    }

    /**
     * 供活列表 搜索
     * */
    public function searchWorkSupply()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $dt = $this->request->param('dt');

        $ts = strtotime($dt);
        if (!$ts) {
            $this->error("时间格式错误");
        }

        $user = $this->auth->getUser();
        $list = WorkSupply::getWorkSupplyList($page, $limit, $where = [
            'begin_time' => ['<', $ts],
            'end_time' => ['>', $ts],
            'user_id' => $user->id,
        ]);

        $this->success('success', [
            'list' => WorkSupply::formatWorkSupplyList($list)
        ]);
    }

    /**
     * 供活详情
     * */
    public function getWorkSupply()
    {
        $id = $this->request->param('id');

        $info = WorkSupply::getWorkSupply($id);
        $this->success('success', [
            'info' => $info
        ]);
    }


    /**
     * 变更信息
     * */
    public function setWorkSupply()
    {
        $id = $this->request->param('id');
        $begin_time = $this->request->param('begin_time');
        $end_time = $this->request->param('end_time');
        $content = $this->request->param('content');
        $address = $this->request->param('address');
        $linkman = $this->request->param('linkman');
        $linkman_sex = $this->request->param('linkman_sex');
        $link_phone = $this->request->param('link_phone');

        $res = WorkSupply::setWorkSupply($id, [
            'begin_time' => strtotime($begin_time),
            'end_time' => strtotime($end_time),
            'content' => $content,
            'address' => $address,
            'linkman' => $linkman,
            'linkman_sex' => $linkman_sex,
            'link_phone' => $link_phone,
        ]);
        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功');
        }
    }

    /**
     * 删除信息
     * */
    public function deleteWorkSupply()
    {
        $id = $this->request->param('id');

        $res = WorkSupply::deleteWorkSupply($id);
        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功');
        }
    }


    /**
     * 找活
     * */
    public function addWorkFind()
    {
        $begin_time = $this->request->param('begin_time');
        $content = $this->request->param('content');
        $linkman = $this->request->param('linkman');
        $linkman_sex = $this->request->param('linkman_sex');
        $link_phone = $this->request->param('link_phone');

        $user = $this->auth->getUser();
        $res = WorkFind::addWorkFind($user->id, $begin_time, $content, $linkman, $linkman_sex, $link_phone);
        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功');
        }

    }

    /**
     * 找活列表
     * */
    public function getWorkFindList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $user = $this->auth->getUser();
        $list = WorkFind::getWorkFindList($page, $limit, $where = [
            'user_id' => $user->id,
        ]);
        $this->success('success', [
            'list' => WorkFind::formatWorkFindList($list)
        ]);
    }

    /**
     * 找活列表 搜索
     * */
    public function searchWorkFind()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $dt = $this->request->param('dt');

        $ts = strtotime($dt);
        if (!$ts) {
            $this->error("时间格式错误");
        }

        $user = $this->auth->getUser();
        $sts = strtotime(date('Y-m-d', $ts));
        $ets = strtotime("+1 day", $sts);
        $list = WorkFind::getWorkFindList($page, $limit, $where = [
            'begin_time' => [['>=', $sts], ['<', $ets]],
            'user_id' => $user->id,
        ]);

        $this->success('success', [
            'list' => WorkFind::formatWorkFindList($list)
        ]);
    }


    /**
     * 供活详情
     * */
    public function getWorkFind()
    {
        $id = $this->request->param('id');

        $info = WorkFind::getWorkFind($id);
        $this->success('success', [
            'info' => $info
        ]);
    }


    /**
     * 变更信息
     * */
    public function setWorkFind()
    {
        $id = $this->request->param('id');
        $begin_time = $this->request->param('begin_time');
        $content = $this->request->param('content');
        $linkman = $this->request->param('linkman');
        $linkman_sex = $this->request->param('linkman_sex');
        $link_phone = $this->request->param('link_phone');

        $res = WorkFind::setWorkFind($id, [
            'begin_time' => strtotime($begin_time),
            'content' => $content,
            'linkman' => $linkman,
            'linkman_sex' => $linkman_sex,
            'link_phone' => $link_phone,
        ]);
        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功');
        }
    }

    /**
     * 删除信息
     * */
    public function deleteWorkFind()
    {
        $id = $this->request->param('id');

        $res = WorkFind::deleteWorkFind($id);
        if (!$res) {
            $this->error('操作失败，请重新操作。');
        } else {
            $this->success('操作成功');
        }
    }

    /**
     * 采购单 分类列表
     * */
    public function getPurchaseCateList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $list = \app\api\model\Purchase::getPurchaseCateList($page, $limit);
        $this->success('success', [
            'list' => $list,
        ]);
    }


    /**
     * 下载采购单模板
     * */
    public function downloadPurchaseTemplate()
    {
        $title = $this->request->param('title');
        $cate_id = $this->request->param('cate_id');

        $cate_name = \app\api\model\Purchase::getCateName($cate_id);
        if (empty($cate_name)) {
            $this->error("分类ID错误");
        }
        $spreadsheet = \app\api\model\Purchase::getDownloadSheet($title, $cate_name, [
            ['', '', '', '', '', '', '', '', ''], ['', '', '', '', '', '', '', '', '']
        ]);

        // MIME 协议，文件的类型，不设置，会默认html
        header('Content-Type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        // MIME 协议的扩展
        header('Content-Disposition:attachment;filename=purchase_template.xlsx');
        // 缓存控制
        header('Cache-Control:max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        // php://output 它是一个只写数据流, 允许你以 print 和 echo一样的方式写入到输出缓冲区。
        $writer->save('php://output');
        exit;
    }

    /**
     * 上传采购单 old 作废
     * */
    public function uploadPurchaseInvoice()
    {
        $file = $this->request->file('file');
        if (empty($file)) {
            $this->error("上传文件为空");
        }

        $fileInfo = $file->getInfo();
        $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix = $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';
        if ($suffix != 'xlsx') {
            $this->error("上传文件格式错误");
        }

        $fileData = \app\api\model\Purchase::getUploadPurchaseInvoiceData($fileInfo['tmp_name'], 1);
        if ($fileData === false) {
            $this->error("数据格式错误");
        }
        halt($fileData);

        Db::startTrans();
        try {
            $purchase = $fileData['purchase'];
            $cate = \app\api\model\Purchase::getCate('name', $purchase['cate_name']);
            if (empty($cate)) {
                throw new Exception("分类名称错误");
            }

            $user = $this->auth->getUser();
            $row = [
                'title' => $purchase['title'],
                'cate_name' => $purchase['cate_name'],
                'cate_id' => $cate['id'],
                'company' => $purchase['company'],
                'linkman' => $purchase['linkman'],
                'link_phone' => $purchase['link_phone'],
                'item_num' => $purchase['item_num'],
                'goods_num' => $purchase['goods_num'],
                'createtime' => time(),
                'user_id' => $user->id,
            ];

            $id = Db::name('purchase')->insertGetId($row);
            foreach ($fileData['item'] as $item) {
                $row = [
                    'purchase_id' => $id,
                    'item_no' => $item['item_no'],
                    'goods_name' => $item['goods_name'],
                    'goods_spec' => $item['goods_spec'],
                    'goods_unit' => $item['goods_unit'],
                    'goods_num' => $item['goods_num'],
                    'goods_param' => $item['goods_param'],
                    'remark' => $item['remark'],
                ];
                Db::name('purchase_item')->insert($row);
            }

            Db::commit();
            $this->success('操作成功');
        } catch (Exception $e) {
            Db::rollback();
            // dump($e->getMessage());
            $this->error('操作失败，请重新操作。');
        }
    }

    /**
     * 上传采购单文件 数据不完整返回code=99
     * */
    public function uploadPinvoice2()
    {
        $file = $this->request->file('file');
        if (empty($file)) {
            $this->error("上传文件为空");
        }

        $fileInfo = $file->getInfo();
        $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix = $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';
        if ($suffix != 'xlsx') {
            $this->error("上传文件格式错误");
        }

        $fileData = \app\api\model\Purchase::getUploadPurchaseInvoiceData($fileInfo['tmp_name'], 1);
        if ($fileData === false) {
            $this->result('数据格式错误', null, 99);
        }

        $purchase = $fileData['purchase'];
        $cate = \app\api\model\Purchase::getCate('name', $purchase['cate_name']);
        if (empty($cate)) {
            throw new Exception("分类名称错误");
        }

        // 保存文件
        $uploadDir = date('Ymd');
        $fileName = md5_file($fileInfo['tmp_name']) . ".{$suffix}";
        $splInfo = $file->move(ROOT_PATH . 'public/excel/' . $uploadDir, $fileName);
        if ($splInfo) {
            $this->success(__('Upload successful'), [
                'url' => $uploadDir . '/' . $splInfo->getSaveName()
            ]);
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }
    }

    /**
     * 上传采购单文件
     * */
    public function uploadPinvoice()
    {
        $file = $this->request->file('file');
        if (empty($file)) {
            $this->error("上传文件为空");
        }

        $fileInfo = $file->getInfo();
        $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix = $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';
        if ($suffix != 'xlsx') {
            $this->error("上传文件格式错误");
        }

        $fileData = \app\api\model\Purchase::getUploadPurchaseInvoiceData($fileInfo['tmp_name'], 1);
        if ($fileData === false) {
            $this->error("数据格式错误");
        }

        $purchase = $fileData['purchase'];
        $cate = \app\api\model\Purchase::getCate('name', $purchase['cate_name']);
        if (empty($cate)) {
            throw new Exception("分类名称错误");
        }

        // 保存文件
        $uploadDir = date('Ymd');
        $fileName = md5_file($fileInfo['tmp_name']) . ".{$suffix}";
        $splInfo = $file->move(ROOT_PATH . 'public/excel/' . $uploadDir, $fileName);
        if ($splInfo) {
            $this->success(__('Upload successful'), [
                'url' => $uploadDir . '/' . $splInfo->getSaveName()
            ]);
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }
    }

    /**
     * 发布采购单
     * */
    public function publishPinvoice()
    {
        $url = $this->request->param('url');
        $file = ROOT_PATH . 'public/excel/' . $url;
        $fileData = \app\api\model\Purchase::getUploadPurchaseInvoiceData($file, 1);
        if ($fileData === false) {
            $this->error("数据格式错误");
        }

        Db::startTrans();
        try {
            $purchase = $fileData['purchase'];
            $cate = \app\api\model\Purchase::getCate('name', $purchase['cate_name']);
            if (empty($cate)) {
                throw new Exception("分类名称错误");
            }

            $user = $this->auth->getUser();
            $row = [
                'title' => $purchase['title'],
                'cate_name' => $purchase['cate_name'],
                'cate_id' => $cate['id'],
                'company' => $purchase['company'],
                'linkman' => $purchase['linkman'],
                'link_phone' => $purchase['link_phone'],
                'item_num' => $purchase['item_num'],
                'goods_num' => $purchase['goods_num'],
                'createtime' => time(),
                'user_id' => $user->id,
            ];

            $id = Db::name('purchase')->insertGetId($row);
            foreach ($fileData['item'] as $item) {
                $row = [
                    'purchase_id' => $id,
                    'item_no' => $item['item_no'],
                    'goods_name' => $item['goods_name'],
                    'goods_spec' => $item['goods_spec'],
                    'goods_unit' => $item['goods_unit'],
                    'goods_num' => $item['goods_num'],
                    'goods_param' => $item['goods_param'],
                    'remark' => $item['remark'],
                ];
                Db::name('purchase_item')->insert($row);
            }

            //发布采购消息
            $user_ids = \app\api\model\User::getMsgToIds();
            $data = [];
            $now = time();
            foreach ($user_ids as $user_id) {
                array_push($data, [
                    'user_id' => $user_id,
                    'purchase_id' => $id,
                    'is_read' => 0,
                    'time' => $now,
                ]);
            }
            Db::name('msg_purchase')->insertAll($data);


            Db::commit();
            $this->success('操作成功');
        } catch (Exception $e) {
            Db::rollback();
            // dump($e->getMessage());
            $this->error('操作失败，请重新操作。');
        }
    }

    /**
     * 采购单列表
     * */
    public function getPurchaseList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $user = $this->auth->getUser();
        $list = \app\api\model\Purchase::getPurchaseList($page, $limit, [
            'user_id' => $user->id
        ]);
        $this->success('success', [
            'list' => $list
        ]);
    }

    /**
     * 采购单详情
     * */
    public function getPurchase()
    {
        $id = $this->request->param('id');
        $info = \app\api\model\Purchase::getPurchase($id);
        $info = \app\api\model\Purchase::getPurchaseSupply($info);
        $this->success('success', [
            'info' => $info
        ]);
    }

    /**
     * 下载供货单
     * */
    public function downloadSupplyInvoice()
    {
        $id = $this->request->param('id');

        $info = \app\api\model\Purchase::getSupplyForDownload($id);
        $spreadsheet = \app\api\model\Purchase::getDownloadSheet($info['title'], $info['cate_name'],
            $info['item'], $info['company'], $info['linkman'], $info['link_phone'], $info['s_company'],
            $info['s_linkman'], $info['s_link_phone']);

        // MIME 协议，文件的类型，不设置，会默认html
        header('Content-Type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        // MIME 协议的扩展
        header("Content-Disposition:attachment;filename=supply_{$id}.xlsx");
        // 缓存控制
        header('Cache-Control:max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        // php://output 它是一个只写数据流, 允许你以 print 和 echo一样的方式写入到输出缓冲区。
        $writer->save('php://output');
        exit;
    }

    /**
     * 我已报价列表
     * */
    public function getSupplyList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $user = $this->auth->getUser();
        $list = \app\api\model\Purchase::getSupplyList($page, $limit, [
            'user_id' => $user->id
        ]);
        $this->success('success', [
            'list' => $list
        ]);
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
     * 删除报价单
     * */
    public function deleteSupplyInvoice()
    {
        $id = $this->request->param('id'); //报价ID

        Db::startTrans();
        try {
            Db::name('purchase_supply')->where('id', $id)->delete();
            Db::name('purchase_supply_item')->where('id', $id)->delete();
            Db::commit();
            $this->success('操作成功');
        } catch (Exception $e) {
            Db::rollback();
            $this->error('操作失败，请重新操作。');
        }
    }

}
