<?php
/**
 * @author 见龙在野
 * @date 2020-08-08
 */

namespace app\api\controller\mine;

use app\common\controller\Api;

/**
 * 帮助中心
 * */
class Faq extends Api
{

    protected $noNeedLogin = [''];
    protected $noNeedRight = '*';


    //列表
    public function getItems()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');

        $list = \app\api\model\Faq::getRows([], $page, $limit, 'weigh asc');

        $this->success('success', [
            'list' => $list
        ]);
    }

}