<?php

return array(
    'auth' => [
        'AccessKeyId' => 'LTAI4GAHEoiMUzZhGr7zF1XL',
        'AccessKeySecret' => 'Ww6GyAFGd44b2W37AU5m3EsnKHHzAw',
        'bucket' => 'kuaicaibao',
        'endpoint' => 'http://oss-cn-beijing.aliyuncs.com',
    ],

    'upload' => [
        /**
         * 上传地址,默认是本地上传
         */
        'uploadurl' => 'ajax/upload',
        /**
         * CDN地址
         */
        'cdnurl' => '',
        /**
         * 文件保存格式
         */
        'savekey' => '{filemd5}{.suffix}',
        /**
         * 最大可上传大小
         */
        'maxsize' => '10mb',
        /**
         * 可上传的文件类型
         */
        'mimetype' => 'jpg,png,bmp,jpeg,gif,avi,rmvb,rm,asf,divx,mpg,mpeg,mpe,wmv,mp4,mkv,vob',
        /**
         * 是否支持批量上传
         */
        'multiple' => false,
    ],

);