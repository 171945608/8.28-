<?php

//配置文件
return [
    'url_common_param' => true,
    'url_html_suffix' => '',
    'controller_auto_search' => true,

    'session' => [
        'id' => '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
        // SESSION 前缀
        'prefix' => 'shop_',
        // 驱动方式 支持redis memcache memcached
        'type' => '',
        // 是否自动开启 SESSION
        'auto_start' => true,
    ],
    // +----------------------------------------------------------------------
    // | Cookie设置
    // +----------------------------------------------------------------------
    'cookie' => [
        // cookie 名称前缀
        'prefix' => 'shop_',
        // cookie 保存时间
        'expire' => 0,
        // cookie 保存路径
        'path' => '/',
        // cookie 有效域名
        'domain' => '',
        //  cookie 启用安全传输
        'secure' => false,
        // httponly设置
        'httponly' => '',
        // 是否使用 setcookie
        'setcookie' => true,
    ],

    // 视图输出字符串内容替换,留空则会自动进行计算
    'view_replace_str' => [
        '__SHOP__' => '/shop',
    ],

    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl'  => APP_PATH . 'common' . DS . 'view' . DS . 'tpl' . DS . 'dispatch_jump_cn.tpl',
    'dispatch_error_tmpl'    => APP_PATH . 'common' . DS . 'view' . DS . 'tpl' . DS . 'dispatch_jump_cn.tpl',
];
