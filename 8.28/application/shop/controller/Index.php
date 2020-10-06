<?php

namespace app\shop\controller;

use think\Config;
use think\Hook;
use think\Validate;

/**
 * 外框页面
 */
class Index extends Base
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
