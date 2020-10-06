<?php

namespace app\index\controller;

use app\common\controller\Frontend;

class Chat extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function index()
    {
        return $this->view->fetch();
    }

    public function index2()
    {
        return $this->view->fetch();
    }

}
