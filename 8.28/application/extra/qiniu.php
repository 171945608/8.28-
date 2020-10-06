<?php

return array(
    'auth' => [
        'access_key' => '',
        'secret_key' => '',
        'bucket' => '',
        'domain' => '',
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
        'mimetype' => 'jpg,png,bmp,jpeg,gif,zip,rar,xls,xlsx,avi,rmvb,rm,asf,divx,mpg,mpeg,mpe,wmv,mp4,mkv,vob',
        /**
         * 是否支持批量上传
         */
        'multiple' => false,
    ],

);