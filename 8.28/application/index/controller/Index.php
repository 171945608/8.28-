<?php

namespace app\index\controller;

use app\common\controller\Frontend;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use app\common\library\Sms;
use phpDocumentor\Reflection\Types\Object_;
use think\Config;
use think\Db;
use app\common\miniprogram\Auth;
use app\common\miniprogram\WXBizDataCrypt;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout      = '';

    public function index()
    {
        return $this->view->fetch();
    }

    public function index2()
    {
        return self::class;
    }

}
