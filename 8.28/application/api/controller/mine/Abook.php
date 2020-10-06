<?php
/**
 * @author 见龙在野
 * @date 2020-08-08
 */

namespace app\api\controller\mine;

use app\common\controller\Api;

/**
 * 记账本
 * */
class Abook extends Api
{

    protected $noNeedLogin = [''];
    protected $noNeedRight = '*';

    //列表
    public function getList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $login = $this->auth->getUser();
        $list = \app\api\model\Abook::getRows([
            'user_id' => $login->id
        ], $page, $limit);

        $this->success('success', [
            'list' => $list
        ]);
    }

    //查看
    public function getItem()
    {
        $id = $this->request->param('id');

        $login = $this->auth->getUser();
        $info = \app\api\model\Abook::getRow($id, $login->id);

        $this->success('success', [
            'info' => $info
        ]);
    }

    //新增
    public function addItem()
    {
        $title = $this->request->param('title');
        $content = $this->request->param('content');

        $login = $this->auth->getUser();
        $res = \app\api\model\Abook::addRow($title, $content, $login->id);

        if (!$res) {
            $this->error("操作失败");
        } else {
            $this->success("操作成功");
        }
    }

    //编辑
    public function setItem()
    {
        $title = $this->request->param('title');
        $content = $this->request->param('content');
        $id = $this->request->param('id');

        $login = $this->auth->getUser();
        $res = \app\api\model\Abook::setRow([
            'title' => $title,
            'content' => $content,
            'time' => time()
        ], $id, $login->id);

        if (!$res) {
            $this->error("操作失败");
        } else {
            $this->success("操作成功");
        }
    }

    //删除
    public function delItem()
    {
        $ids = $this->request->param('ids');

        $login = $this->auth->getUser();
        $res = \app\api\model\Abook::delRows($ids, $login->id);

        if (!$res) {
            $this->error("操作失败");
        } else {
            $this->success("操作成功");
        }
    }

}