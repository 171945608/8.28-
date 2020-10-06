<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use app\common\wxmp\Auth;
use app\common\wxmp\DataCrypt;
use fast\Random;
use think\Db;
use think\Validate;

/**
 * 会员接口
 */
class User extends Api
{
    protected $noNeedLogin = ['register', 'mobilelogin', 'wxlogin', 'wxloginBind', 'mplogin', 'mploginBind', 'icodeShow'];
    protected $noNeedRight = '*';

    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 会员中心
     */
    public function index()
    {
        $this->success('', ['welcome' => $this->auth->nickname]);
    }

    /**
     * 会员登录
     *
     * @param string $mobile 手机
     * @param string $password 密码
     */
    public function login()
    {
        $mobile = $this->request->request('mobile');
        $password = $this->request->request('password');
        if (!$mobile || !$password) {
            $this->error(__('Invalid parameters'));
        }
        $ret = $this->auth->login($mobile, $password);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 手机验证码登录
     *
     * @param string $mobile 手机号
     * @param string $captcha 验证码
     */
    public function mobilelogin()
    {
        $mobile = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');
        $icode = $this->request->request('icode');

        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }

        $capt = new \think\captcha\Captcha();
        if (!$capt->check2($icode)) {
            $this->error('图片验证码错误');
        }

        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }

        if (!Sms::check($mobile, $captcha, 'mobilelogin')) {
            $this->error(__('Captcha is incorrect'));
        }

        $user = \app\common\model\User::getByMobile($mobile);
        if ($user) {
            if (!\app\api\model\User::checkForbid($user->id)) {
                $this->error(__('Account is locked'));
            }

            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        } else {
            $ret = $this->auth->register('kcb', $mobile);
        }

        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 查看图片码值 调试用
     * */
    public function icodeShow()
    {
        $icode = $this->request->request('icode');
        $capt = new \think\captcha\Captcha();
        $this->success('', [
            $capt->show($icode)
        ]);
    }

    /**
     * 微信登录
     * */
    public function wxlogin()
    {
        $unionid = $this->request->request('unionid');
        if (!$unionid) {
            $this->error(__('Invalid parameters'));
        }

        $user = \app\api\model\User::getUser('unionid', $unionid);
        if ($user) {
            if (!\app\api\model\User::checkForbid($user['id'])) {
                $this->error(__('Account is locked'));
            }

            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user['id']);
        } else {
            $this->success('首次微信登陆，请绑定手机号码', [], 99);
        }

        if ($ret) {
            $this->success(__('Logged in successful'), ['userinfo' => $this->auth->getUserinfo()]);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 微信登录 绑定手机
     * */
    public function wxloginBind()
    {
        $unionid = $this->request->request('unionid');
        $mobile = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');

        if (!$mobile || !$captcha || !$unionid) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (!Sms::check($mobile, $captcha, 'wxlogin_bind')) {
            $this->error(__('Captcha is incorrect'));
        }

        $wxuser = \app\api\model\User::getUser('unionid', $unionid);
        if ($wxuser) {
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($wxuser['id']);
        } else {
            $mobileuser = \app\api\model\User::getUser('mobile', $mobile);
            if (!empty($mobileuser)) {
                \app\api\model\User::setUser($mobileuser['id'], ['unionid' => $unionid]);
                $ret = $this->auth->direct($mobileuser['id']);
            } else {
                $ret = $this->auth->register('kcb', $mobile, ['unionid' => $unionid]);
            }
        }

        $user = $this->auth->getUser();
        if (!\app\api\model\User::checkForbid($user['id'])) {
            $this->error(__('Account is locked'));
        }

        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 注册会员
     *
     * @param string $password 密码
     * @param string $mobile 手机号
     * @param string $code 验证码
     */
    public function register()
    {
        $mobile = $this->request->request('mobile');
        $code = $this->request->request('code');
        $captcha = $this->request->request('captcha');

        if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }

        $ret = Sms::check($mobile, $code, 'register');
        if (!$ret) {
            $this->error(__('Captcha is incorrect'));
        }

        $capt = new \think\captcha\Captcha();
        if (!$capt->check($captcha)) {
            $this->error('图片验证码错误');
        }

        $ret = $this->auth->register('kcb', $mobile, []);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Sign up successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        $this->auth->logout();
        $this->success(__('Logout successful'));
    }

    /**
     * 修改会员个人信息
     *
     * @param string $avatar 头像地址
     * @param string $nickname 昵称
     */
    public function profile()
    {
        $user = $this->auth->getUser();
        $nickname = $this->request->request('nickname');
        $avatar = $this->request->request('avatar', '', 'trim,strip_tags,htmlspecialchars');

        !empty($nickname) && $user->nickname = $nickname;
        !empty($avatar) && $user->avatar = $avatar;
        $user->save();
        $this->success();
    }

    /**
     * 修改邮箱
     *
     * @param string $email 邮箱
     * @param string $captcha 验证码
     */
    public function changeemail()
    {
        $user = $this->auth->getUser();
        $email = $this->request->post('email');
        $captcha = $this->request->request('captcha');
        if (!$email || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if (\app\common\model\User::where('email', $email)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Email already exists'));
        }
        $result = Ems::check($email, $captcha, 'changeemail');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->email = 1;
        $user->verification = $verification;
        $user->email = $email;
        $user->save();

        Ems::flush($email, 'changeemail');
        $this->success();
    }

    /**
     * 修改手机号
     *
     * @param string $mobile 手机号
     * @param string $captcha 验证码
     */
    public function changemobile()
    {
        $user = $this->auth->getUser();
        $mobile_old = $this->request->request('mobile_old');
        $captcha_old = $this->request->request('captcha_old');
        $mobile_new = $this->request->request('mobile_new');
        $captcha_new = $this->request->request('captcha_new');
        if (!$mobile_old || !$captcha_old || !$mobile_new || !$captcha_new) {
            $this->error(__('Invalid parameters'));
        }

        if (!Validate::regex($mobile_old, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (!Validate::regex($mobile_new, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }

        if ($user['mobile'] != $mobile_old) {
            $this->error('旧手机号码错误');
        }
        if ($mobile_old == $mobile_new) {
            $this->error('新旧号码相同');
        }
        if (\app\common\model\User::where('mobile', $mobile_new)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Mobile already exists'));
        }

        $result = Sms::check($mobile_old, $captcha_old, 'changemobile_old');
        if (!$result) {
            $this->error(__('旧手机验证码错误'));
        }

        $result = Sms::check($mobile_new, $captcha_new, 'changemobile_new');
        if (!$result) {
            $this->error(__('新手机验证码错误'));
        }

        $verification = $user->verification;
        $verification->mobile = 1;
        $user->verification = $verification;
        $user->mobile = $mobile_new;
        $user->save();

        $this->success();
    }

    public function beforeChangeMobile()
    {
        $user = $this->auth->getUser();
        $mobile_old = $this->request->request('mobile_old');
        $captcha_old = $this->request->request('captcha_old');
        if (!$mobile_old || !$captcha_old) {
            $this->error(__('Invalid parameters'));
        }

        if (!Validate::regex($mobile_old, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }

        if ($user['mobile'] != $mobile_old) {
            $this->error('旧手机号码错误');
        }

        $result = Sms::check222($mobile_old, $captcha_old, 'changemobile_old');
        if (!$result) {
            $this->error(__('旧手机验证码错误'));
        }

        $this->success();
    }


    /**
     * 重置密码
     *
     * @param string $mobile 手机号
     * @param string $newpassword 新密码
     * @param string $captcha 验证码
     */
    public function resetpwd()
    {
        $mobile = $this->request->request("mobile");
        $newpassword = $this->request->request("newpassword");
        $captcha = $this->request->request("captcha");
        if (!$newpassword || !$captcha) {
            $this->error(__('Invalid parameters'));
        }

        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        $user = \app\common\model\User::getByMobile($mobile);
        if (!$user) {
            $this->error(__('User not found'));
        }
        $ret = Sms::check($mobile, $captcha, 'resetpwd');
        if (!$ret) {
            $this->error(__('Captcha is incorrect'));
        }
        Sms::flush($mobile, 'resetpwd');

        //模拟一次登录
        $this->auth->direct($user->id);
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret) {
            $this->success(__('Reset password successful'));
        } else {
            $this->error($this->auth->getError());
        }
    }


    /**
     * 修改密码
     *
     * @param string $oldpassword 旧密码
     * @param string $newpassword 新密码
     * @param string $repassword 确认密码
     */
    public function changepwd()
    {
        $oldpassword = $this->request->request("oldpassword");
        $newpassword = $this->request->request("newpassword");
        $repassword = $this->request->request("repassword");
        if (!$oldpassword || !$newpassword || !$repassword || $newpassword != $repassword) {
            $this->error(__('Invalid parameters'));
        }

        if ($newpassword && !Validate::regex($newpassword, "^\w{6,30}$")) {
            $this->error(__('Password must be 6 to 30 characters'));
        }

        $user = $this->auth->getUser();
        if (!$user) {
            $this->error(__('User not found'));
        }

        $ret = $this->auth->changepwd($newpassword, $oldpassword);
        if ($ret) {
            $this->success(__('Change password successful'));
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 小程序登录
     * */
    public function mplogin()
    {
        $code = $this->request->param('code');
        $encryptedData = $this->request->param('encryptedData');
        $iv = $this->request->param('iv');

        if (!$code || !$encryptedData || !$iv) {
            $this->error(__('Invalid parameters'));
        }

        //成功返回openid和session_key
        $res = Auth::codeToSession($code);
        //halt($res);
        if (isset($res['errcode']) && $res['errcode'] != 0) {
            $this->error("接口请求失败");
        }

        //解密加密数据
        $crypt = new DataCrypt(Auth::getAppId(), $res['session_key']);
        $res = $crypt->decryptData($encryptedData, $iv, $array);
        if ($res != 0) {
            $this->error("获取数据失败");
        }

        $openid = $array['openId'];
        $unionid = $array['unionId'];

        $user = \app\api\model\User::getUser('unionid', $unionid);
        if ($user) {
            if (!\app\api\model\User::checkForbid($user['id'])) {
                $this->error(__('Account is locked'));
            }

            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user['id']);
        } else {
            $this->success('首次微信登陆，请绑定手机号码', [], 99);
        }

        if ($ret) {
            $this->success(__('Logged in successful'), ['userinfo' => $this->auth->getUserinfo()]);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 小程序登录 绑定手机
     * */
    public function mploginBind()
    {
        $code = $this->request->param('code');
        $encryptedData = $this->request->param('encryptedData');
        $iv = $this->request->param('iv');
        $mobile = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');

        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }

        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }

        if (!Sms::check($mobile, $captcha, 'wxlogin_bind')) {
            $this->error(__('Captcha is incorrect'));
        }

        if (!$code || !$encryptedData || !$iv) {
            $this->error(__('Invalid parameters'));
        }

        //成功返回openid和session_key
        $res = Auth::codeToSession($code);
        if (isset($res['errcode']) && $res['errcode'] != 0) {
            $this->error("接口请求失败");
        }

        //解密加密数据
        $crypt = new DataCrypt(Auth::getAppId(), $res['session_key']);
        $res = $crypt->decryptData($encryptedData, $iv, $array);
        if ($res != 0) {
            $this->error("获取数据失败");
        }

        $openid = $array['openId'];
        $unionid = $array['unionId'];

        $wxuser = \app\api\model\User::getUser('unionid', $unionid);
        if ($wxuser) {
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($wxuser['id']);
        } else {
            $mobileuser = \app\api\model\User::getUser('mobile', $mobile);
            if (!empty($mobileuser)) {
                \app\api\model\User::setUser($mobileuser['id'], ['unionid' => $unionid]);
                $ret = $this->auth->direct($mobileuser['id']);
            } else {
                $ret = $this->auth->register('kcb', $mobile, [
                    'unionid' => $unionid
                ]);
            }
        }

        $user = $this->auth->getUser();
        if (!\app\api\model\User::checkForbid($user['id'])) {
            $this->error(__('Account is locked'));
        }

        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

}
