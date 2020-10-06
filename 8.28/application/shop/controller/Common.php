<?php

namespace app\shop\controller;

use app\admin\model\shop\Shop;
use app\api\model\Mine;
use think\Config;
use think\Hook;
use think\Session;
use think\Validate;

/**
 * 注册登录
 */
class Common extends \think\Controller
{

    public function _initialize()
    {
        parent::_initialize();
        //移除HTML标签
        $this->request->filter('trim,strip_tags,htmlspecialchars');
    }

    /**
     * 商户登录
     */
    public function login()
    {
        return $this->view->fetch();
    }

    /**
     * 登录逻辑
     * */
    public function doLogin()
    {
        $username = $this->request->param('username');
        $password = $this->request->param('password');
        $captcha = $this->request->param('captcha');

        $rule = [
            'username' => 'require',
            'password' => 'require',
            'captcha' => 'require|length:4',
        ];
        $data = [
            'username' => $username,
            'password' => $password,
            'captcha' => $captcha,
        ];
        $validate = new Validate($rule, [], ['username' => '登录账号', 'password' => '登录密码', 'captcha' => '验证码']);
        $res = $validate->check($data);
        if (!$res) {
            $this->error($validate->getError());
        }

        $capt = new \think\captcha\Captcha();
        if (!$capt->check($captcha)) {
            $this->error('验证码错误');
        }

        $shop = Mine::getShop('username', $username);
        if (empty($shop)) {
            $this->error('登录账号或登录密码错误');
        }

        if (md5(md5($password) . $shop['salt']) != $shop['password']) {
            $this->error('登录账号或登录密码错误');
        }

        if ($shop['audit_state'] != Mine::AUDIT_STATE_SUCCESS) {
            $this->error('尚未审核通过');
        }

        if (!Mine::checkForbid($shop['id'])) {
            $this->error('账号处于封禁期');
        }

        Mine::setShop($shop['id'], [
            'logintime' => time(),
            'loginip' => request()->ip(),
        ]);

        Session::set('shop', [
            'id' => $shop['id'],
            'username' => $shop['username'],
            'nickname' => $shop['nickname'],
            'avatar' => $shop['avatar'],
            'email' => $shop['email'],
        ]);
        $store = Mine::getStore('shop_id', $shop['id']);
        Session::set('store', $store);
        $this->success('success');
    }


    /**
     * 注销登录
     */
    public function logout()
    {
        Session::clear();
        $this->success('操作成功', 'login');
    }

}
