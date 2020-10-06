<?php

namespace app\shop\controller;

use think\Config;
use think\Hook;
use think\Validate;

/**
 * 仪表盘
 */
class Dashboard extends Base
{

    public function _initialize()
    {
        parent::_initialize();
        //移除HTML标签
        $this->request->filter('trim,strip_tags,htmlspecialchars');
    }

    public function index()
    {
        return $this->view->fetch();
    }
}
