<?php
/**
 * Created by PhpStorm.
 * User: 黄毛
 * Date: 2020/7/31
 * Time: 9:002222
 */

namespace app\common\wxmp;

use GuzzleHttp\Client;
use think\Config;

class Auth
{
    protected static function getConfig()
    {
        $config = Config::get('miniprogram');
        return $config;
    }

    public static function codeToSession($code)
    {
        $config = self::getConfig();
        $options = [
            'verify' => false,
            'query'  => [
                'appid'      => $config['appId'],
                'secret'     => $config['appSecret'],
                'js_code'    => $code,
                'grant_type' => 'authorization_code',
            ],

        ];

        $client = new Client();
        $response = $client->request('GET', 'https://api.weixin.qq.com/sns/jscode2session', $options);
        $ret = json_decode($response->getBody(), true); // '{"id": 1420053, "name": "guzzle", ...}'
        //halt($ret);
        return $ret;
    }

    public static function getAppId()
    {
        $config = self::getConfig();
        return $config['appId'];
    }

}