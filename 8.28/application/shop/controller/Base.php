<?php

namespace app\shop\controller;

use think\Config;
use think\Hook;
use think\Session;
use think\Validate;
use think\Response;

/**
 * 商户中心基类
 */
class Base extends \think\Controller
{
    public function _initialize()
    {
        parent::_initialize();
        //移除HTML标签
        $this->request->filter('trim,strip_tags,htmlspecialchars');

        //检查是否登录
        if (!Session::has('shop') || !Session::has('store')) {
            $this->error('请登录后继续操作', 'common/login');
        }

    }

    public function getResponse($list, $count)
    {
        return Response::create([
            'code' => 0, 'msg' => '', 'data' => $list, 'count' => $count
        ], 'json');
    }

    /**
     * 普通上传
     * */
    public function aliyunUpload()
    {
        $file = $this->request->file('file');
        //halt($file);

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
        if (empty($splInfo)) {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }

        if (in_array($suffix, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'webp'])) {
            $file_type = 'image';
        }
        if (in_array($suffix, ['avi', 'rmvb', 'rm', 'asf', 'divx', 'mpg', 'mpeg', 'mpe', 'wmv', 'mp4', 'mkv', 'vob'])) {
            $file_type = 'video';
        }
        if (!isset($file_type)) {
            $this->error('文件格式错误');
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

        $shop = \app\api\model\Mine::getShop('id', Session::get('shop.id'));
        if ($type == 'video' && !empty($shop) && $shop['access_key_id'] && $shop['access_key_secret'] && $shop['bucket'] && $shop['endpoint']) {
            $accessKeyId = $shop['access_key_id'];
            $accessKeySecret = $shop['access_key_secret'];
            $bucket = $shop['bucket'];
            $endpoint = $shop['endpoint'];
        }

        try {
            $ossClient = new \OSS\OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $res = $ossClient->uploadFile($bucket, $file_name, $file_path);
            //halt($res);
            $this->success('success', '', [
                'url' => $res['oss-request-url']
            ]);
        } catch (\OSS\OssException $e) {
            $this->error($e->getMessage());
        }
    }


    /**
     * 富文本上传
     * */
    public function editorUpload()
    {
        $file = $this->request->file('file');

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
        if (empty($splInfo)) {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }

        if (in_array($suffix, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'webp'])) {
            $file_type = 'image';
        }
        if (in_array($suffix, ['avi', 'rmvb', 'rm', 'asf', 'divx', 'mpg', 'mpeg', 'mpe', 'wmv', 'mp4', 'mkv', 'vob'])) {
            $file_type = 'video';
        }
        if (!isset($file_type)) {
            $this->error('文件格式错误');
        }
        $this->doEditorUpload($file_type, $fileName, $fileInfo['tmp_name']);
    }

    public function doEditorUpload($type, $file_name, $file_path)
    {
        $conf = (config('aliyun'))['auth'];
        $accessKeyId = $conf['AccessKeyId'];
        $accessKeySecret = $conf['AccessKeySecret'];
        $bucket = $conf['bucket'];
        $endpoint = $conf['endpoint'];

        $shop = \app\api\model\Mine::getShop('id', Session::get('shop.id'));
        if ($type == 'video' && !empty($shop) && $shop['access_key_id'] && $shop['access_key_secret'] && $shop['bucket'] && $shop['endpoint']) {
            $accessKeyId = $shop['access_key_id'];
            $accessKeySecret = $shop['access_key_secret'];
            $bucket = $shop['bucket'];
            $endpoint = $shop['endpoint'];
        }

        try {
            $ossClient = new \OSS\OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $res = $ossClient->uploadFile($bucket, $file_name, $file_path);
            //halt($res);

            $ret = [
                "errno" => 0,
                "data" => [
                    $res['oss-request-url']
                ],
            ];
            echo json_encode($ret);exit;
        } catch (\OSS\OssException $e) {
            $this->error($e->getMessage());
        }
    }


}
