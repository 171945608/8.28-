<?php

namespace app\common\behavior;

use think\Config;
use think\Lang;
use think\Loader;
use think\Db;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class Sms
{
    public function smsSend(&$obj)
    {
        $conf = Config::get('sms');
        AlibabaCloud::accessKeyClient($conf['accessKeyId'], $conf['accessKeySecret'])
            ->regionId('cn-hangzhou')
            ->asDefaultClient();

        try {
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId'      => "cn-hangzhou",
                        'PhoneNumbers'  => $obj->mobile,
                        'SignName'      => $conf['SignName'],
                        'TemplateCode'  => $conf['TemplateCode'],
                        'TemplateParam' => json_encode(['code' => $obj->code], JSON_FORCE_OBJECT),
                    ],
                ])
                ->request();

            if ($result->Code == 'OK') {
                return true;
            }
        } catch (ClientException $e) {
            //echo $e->getErrorMessage() . PHP_EOL;
        } catch (ServerException $e) {
            //echo $e->getErrorMessage() . PHP_EOL;
        }
        return false;
    }

    public function smsCheck(&$sms) {

        Db::name('sms')->where('id', $sms->id)->delete();
        return true;
    }

}
