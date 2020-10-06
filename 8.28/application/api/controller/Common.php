<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Area;
use app\common\model\Version;
use fast\Random;
use think\Config;
use think\Db;

/**
 * 公共接口
 */
class Common extends Api
{
    protected $noNeedLogin = [
        'aliyunUpload', 'getBcard', 'getVipSwitch', 'getUserProtocol', 'getPrivacyProtocol', 'getAppLaunchImage'
    ];
    protected $noNeedRight = '*';

    /**
     * 加载初始化
     *
     * @param string $version 版本号
     * @param string $lng 经度
     * @param string $lat 纬度
     */
    public function init()
    {
        if ($version = $this->request->request('version')) {
            $lng = $this->request->request('lng');
            $lat = $this->request->request('lat');
            $content = [
                'citydata'    => Area::getCityFromLngLat($lng, $lat),
                'versiondata' => Version::check($version),
                'uploaddata'  => Config::get('upload'),
                'coverdata'   => Config::get("cover"),
            ];
            $this->success('', $content);
        } else {
            $this->error(__('Invalid parameters'));
        }
    }

    /**
     * 上传文件
     * @ApiMethod (POST)
     * @param File $file 文件流
     */
    public function upload()
    {
        $file = $this->request->file('file');
        if (empty($file)) {
            $this->error(__('No file upload or server upload limit exceeded'));
        }

        //判断是否已经存在附件
        $sha1 = $file->hash();

        $upload = Config::get('upload');

        preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
        $type = strtolower($matches[2]);
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        $size = (int)$upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
        $fileInfo = $file->getInfo();
        $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix = $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';

        $mimetypeArr = explode(',', strtolower($upload['mimetype']));
        $typeArr = explode('/', $fileInfo['type']);

        //禁止上传PHP和HTML文件
        if (in_array($fileInfo['type'], ['text/x-php', 'text/html']) || in_array($suffix, ['php', 'html', 'htm'])) {
            $this->error(__('Uploaded file format is limited'));
        }
        //验证文件后缀
        if ($upload['mimetype'] !== '*' &&
            (
                !in_array($suffix, $mimetypeArr)
                || (stripos($typeArr[0] . '/', $upload['mimetype']) !== false && (!in_array($fileInfo['type'], $mimetypeArr) && !in_array($typeArr[0] . '/*', $mimetypeArr)))
            )
        ) {
            $this->error(__('Uploaded file format is limited'));
        }
        //验证是否为图片文件
        $imagewidth = $imageheight = 0;
        if (in_array($fileInfo['type'], ['image/gif', 'image/jpg', 'image/jpeg', 'image/bmp', 'image/png', 'image/webp']) || in_array($suffix, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'webp'])) {
            $imgInfo = getimagesize($fileInfo['tmp_name']);
            if (!$imgInfo || !isset($imgInfo[0]) || !isset($imgInfo[1])) {
                $this->error(__('Uploaded file is not a valid image'));
            }
            $imagewidth = isset($imgInfo[0]) ? $imgInfo[0] : $imagewidth;
            $imageheight = isset($imgInfo[1]) ? $imgInfo[1] : $imageheight;
        }
        $replaceArr = [
            '{year}'     => date("Y"),
            '{mon}'      => date("m"),
            '{day}'      => date("d"),
            '{hour}'     => date("H"),
            '{min}'      => date("i"),
            '{sec}'      => date("s"),
            '{random}'   => Random::alnum(16),
            '{random32}' => Random::alnum(32),
            '{filename}' => $suffix ? substr($fileInfo['name'], 0, strripos($fileInfo['name'], '.')) : $fileInfo['name'],
            '{suffix}'   => $suffix,
            '{.suffix}'  => $suffix ? '.' . $suffix : '',
            '{filemd5}'  => md5_file($fileInfo['tmp_name']),
        ];
        $savekey = $upload['savekey'];
        $savekey = str_replace(array_keys($replaceArr), array_values($replaceArr), $savekey);

        $uploadDir = substr($savekey, 0, strripos($savekey, '/') + 1);
        $fileName = substr($savekey, strripos($savekey, '/') + 1);
        //
        $splInfo = $file->validate(['size' => $size])->move(ROOT_PATH . '/public' . $uploadDir, $fileName);
        if ($splInfo) {
            $params = array(
                'admin_id'    => 0,
                'user_id'     => (int)$this->auth->id,
                'filesize'    => $fileInfo['size'],
                'imagewidth'  => $imagewidth,
                'imageheight' => $imageheight,
                'imagetype'   => $suffix,
                'imageframes' => 0,
                'mimetype'    => $fileInfo['type'],
                'url'         => $uploadDir . $splInfo->getSaveName(),
                'uploadtime'  => time(),
                'storage'     => 'local',
                'sha1'        => $sha1,
            );
            $attachment = model("attachment");
            $attachment->data(array_filter($params));
            $attachment->save();
            \think\Hook::listen("upload_after", $attachment);
            $this->success(__('Upload successful'), [
                'url' => $uploadDir . $splInfo->getSaveName()
            ]);
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }
    }

    /**
     * 七牛上传
     * */
    public function qiniuUpload()
    {
        $file = $this->request->file('file');
        if (empty($file)) {
            $this->error(__('No file upload or server upload limit exceeded'));
        }
        $upload = (Config::get('qiniu'))['upload'];

        preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
        $type = strtolower($matches[2]);
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        $size = (int)$upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);

        $fileInfo = $file->getInfo();
        $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix = $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';

        $mimetypeArr = explode(',', strtolower($upload['mimetype']));
        $typeArr = explode('/', $fileInfo['type']);

        //禁止上传PHP和HTML文件
        if (in_array($fileInfo['type'], ['text/x-php', 'text/html']) || in_array($suffix, ['php', 'html', 'htm'])) {
            $this->error(__('Uploaded file format is limited'));
        }
        //验证文件后缀
        if ($upload['mimetype'] !== '*' &&
            (
                !in_array($suffix, $mimetypeArr)
                || (stripos($typeArr[0] . '/', $upload['mimetype']) !== false && (!in_array($fileInfo['type'], $mimetypeArr) && !in_array($typeArr[0] . '/*', $mimetypeArr)))
            )
        ) {
            $this->error(__('Uploaded file format is limited'));
        }
        //验证是否为图片文件
        if (in_array($fileInfo['type'], ['image/gif', 'image/jpg', 'image/jpeg', 'image/bmp', 'image/png', 'image/webp']) || in_array($suffix, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'webp'])) {
            $imgInfo = getimagesize($fileInfo['tmp_name']);
            if (!$imgInfo || !isset($imgInfo[0]) || !isset($imgInfo[1])) {
                $this->error(__('Uploaded file is not a valid image'));
            }
        }
        $replaceArr = [
            '{.suffix}' => $suffix ? '.' . $suffix : '',
            '{filemd5}' => md5_file($fileInfo['tmp_name']),
        ];
        $savekey = $upload['savekey'];
        $savekey = str_replace(array_keys($replaceArr), array_values($replaceArr), $savekey);

        $fileName = substr($savekey, strripos($savekey, '/') + 1);

        $splInfo = $file->validate(['size' => $size]);
        if (empty($splInfo)) {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }

        $ret = qiniu_upload($fileName, $fileInfo['tmp_name']);
        if ($ret['code']) {
            $this->success("success", $ret['data']);
        } else {
            $this->error($ret['msg']);
        }
    }

    /**
     * 阿里云上传
     * */
    public function aliyunUpload()
    {
        $file = $this->request->file('file');
        $file_type = $this->request->param('type');

        if (empty($file)) {
            $this->error(__('No file upload or server upload limit exceeded'));
        }
        $upload = (Config::get('aliyun'))['upload'];

        preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
        $type = strtolower($matches[2]);
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        $size = (int)$upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);

        $fileInfo = $file->getInfo();
        $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix = $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';

        $mimetypeArr = explode(',', strtolower($upload['mimetype']));
        $typeArr = explode('/', $fileInfo['type']);

        //禁止上传PHP和HTML文件
        if (in_array($fileInfo['type'], ['text/x-php', 'text/html']) || in_array($suffix, ['php', 'html', 'htm'])) {
            $this->error(__('Uploaded file format is limited'));
        }
        //验证文件后缀
        if ($upload['mimetype'] !== '*' &&
            (
                !in_array($suffix, $mimetypeArr)
                || (stripos($typeArr[0] . '/', $upload['mimetype']) !== false && (!in_array($fileInfo['type'], $mimetypeArr) && !in_array($typeArr[0] . '/*', $mimetypeArr)))
            )
        ) {
            $this->error(__('Uploaded file format is limited'));
        }
        //验证是否为图片文件
        if (in_array($fileInfo['type'], ['image/gif', 'image/jpg', 'image/jpeg', 'image/bmp', 'image/png', 'image/webp']) || in_array($suffix, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'webp'])) {
            $imgInfo = getimagesize($fileInfo['tmp_name']);
            if (!$imgInfo || !isset($imgInfo[0]) || !isset($imgInfo[1])) {
                $this->error(__('Uploaded file is not a valid image'));
            }
        }
        $replaceArr = [
            '{.suffix}' => $suffix ? '.' . $suffix : '',
            '{filemd5}' => md5_file($fileInfo['tmp_name']),
        ];
        $savekey = $upload['savekey'];
        $savekey = str_replace(array_keys($replaceArr), array_values($replaceArr), $savekey);

        $fileName = substr($savekey, strripos($savekey, '/') + 1);

        $splInfo = $file->validate(['size' => $size]);
        //halt($size);

        if (empty($splInfo)) {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }

        $this->doUpload($file_type, $fileName, $fileInfo['tmp_name']);
    }

    public function doUpload($type, $file_name, $file_path)
    {
        $conf = (config('aliyun'))['auth'];
        $accessKeyId = $conf['AccessKeyId'];
        $accessKeySecret = $conf['AccessKeySecret'];
        $bucket = $conf['bucket'];
        $endpoint = $conf['endpoint'];

        $user = $this->auth->getUser();
        $shop = \app\api\model\Mine::getShop('user_id', $user->id);
        if ($type == 'video' && !empty($shop) && $shop['access_key_id'] && $shop['access_key_secret'] && $shop['bucket'] && $shop['endpoint']) {
            $accessKeyId = $shop['access_key_id'];
            $accessKeySecret = $shop['access_key_secret'];
            $bucket = $shop['bucket'];
            $endpoint = $shop['endpoint'];
        }

        try {
            $ossClient = new \OSS\OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $res = $ossClient->uploadFile($bucket, $file_name, $file_path);
            $this->success('success', [
                'url' => $res['oss-request-url']
            ]);
        } catch (\OSS\OssException $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * 获取名片数据
     * */
    public function getBcard()
    {
        $user_id = $this->request->param('id');

        $user = \app\api\model\User::getUser('id', $user_id);
        if (empty($user)) {
            $this->error('用户不存在');
        }

        $bcard = \app\api\model\User::getBcard($user_id);
        $template = \app\api\model\User::getBcardTemplate($user_id);
        $info = [
            'company'        => empty($bcard) ? '' : $bcard['company'],
            'position'       => empty($bcard) ? '' : $bcard['position'],
            'name'           => empty($bcard) ? '' : $bcard['name'],
            'mobile'         => empty($bcard) ? '' : $bcard['mobile'],
            'phone'          => empty($bcard) ? '' : $bcard['phone'],
            'address'        => empty($bcard) ? '' : $bcard['address'],
            'business'       => empty($bcard) ? '' : $bcard['business'],
            'store_address'  => \app\api\model\User::getStoreAddress($user_id),
            'template_image' => $template['image'],
            'template_id'    => $template['id'],
        ];

        $store_id = Db::name('store')->alias('st')
            ->join('shop sh', 'sh.id = st.shop_id')
            ->where('sh.user_id', $user_id)
            ->value('st.id');

        if (!empty($store_id)) {
//            $qrcode_text = urlencode($this->request->domain() . '/store/' . $store_id);
            $qrcode_text = urlencode($this->request->domain() . '/store/6');
            $qrcode = $this->request->domain() . '/qrcode/build?text=' . $qrcode_text;
        } else if (!empty($user['wx_qrcode'])) {
            $qrcode = $user['wx_qrcode'];
        } else {
            $qrcode = $this->request->domain() . '/qrcode/build?text=' . '暂无默认二维码';
        }
        $info['qrcode'] = $qrcode;

        $this->success('success', ['info' => $info]);
    }

    /**
     * VIP开关
     * */
    public function getVipSwitch()
    {
        $value = Db::name('vip_button')
            ->where('name', 'switch')
            ->value('value');

        $info = [
            'switch' => $value
        ];

        $this->success('success', ['info' => $info]);
    }


    /**
     * 用户协议
     * */
    public function getUserProtocol()
    {
        $item = Db::name('protocol')
            ->where('name', 'ua')
            ->find();

        $info = [
            'content' => $item['content']
        ];

        $this->success('success', ['info' => $info]);
    }

    /**
     * 隐私协议
     * */
    public function getPrivacyProtocol()
    {
        $item = Db::name('protocol')
            ->where('name', 'pa')
            ->find();

        $info = [
            'content' => $item['content']
        ];


        $this->success('success', ['info' => $info]);
    }

    /**
     * app启动图
     * */
    public function getAppLaunchImage()
    {
        $info = Db::name('app_launch')
            ->where('field', 'launch')
            ->find();
        $this->success('', [
            'info' => [
                'type' => $info['type'],
                'file' => $info['file'],
            ]
        ]);
    }
}
