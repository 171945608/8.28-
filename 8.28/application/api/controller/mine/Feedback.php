<?php
/**
 * @author 见龙在野
 * @date 2020-08-08
 */

namespace app\api\controller\mine;

use app\common\controller\Api;

/**
 * 反馈
 * */
class Feedback extends Api
{

    protected $noNeedLogin = [''];
    protected $noNeedRight = '*';


    //新增
    public function addItem()
    {
        $title = $this->request->param('title');
        $content = $this->request->param('content');
        $images = $this->request->param('images');

        $login = $this->auth->getUser();
        $res = \app\api\model\Feedback::addRow([
            'title' => $title,
            'content' => $content,
            'images' => $images,
            'createtime' => time(),
            'user_id' => $login->id
        ]);

        if (!$res) {
            $this->error("操作失败");
        } else {
            $this->success("操作成功");
        }
    }

}