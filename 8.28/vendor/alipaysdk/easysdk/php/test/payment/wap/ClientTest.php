<?php

use Alipay\EasySDK\Kernel\Factory;
use Alipay\EasySDK\Test\TestAccount;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $account = new TestAccount();
        Factory::setOptions($account->getTestAccount());
    }
    public function testPay(){
        $create =Factory::payment()->common()->create("Iphone6 16G",
            microtime(), "88.88", "2088002656718920");
        $result = Factory::payment()->wap()->pay("Iphone6 16G",$create->outTradeNo,"0.10","http://www.taobao.com/product/113714.html");
        $this->assertEquals(true, strpos($result->body,'alipay_sdk=alipay-easysdk-php')>0);
        $this->assertEquals(true, strpos($result->body,'sign')>0);
    }
}