<?php
namespace PushWorker\push\Utils;

use \Push\Request\V20150827 as APush;

class AliPush extends Push
{
    protected $_xiaomi_activity;

    public function setXiaoMiActivity($activity){
        $this->_xiaomi_activity = $activity;
    }

    public function exec(array $tokens, $message, $extras = array())
    {
        $accessKeyId = $this->_config['ACCESS_KEY_ID'];
        $accessSecret = $this->_config['ACCESS_SECRET'];
        $appKey = $this->_config['APP_KEY'];
        if(!$this->_config){
            return false;
        }
        $iClientProfile = \DefaultProfile::getProfile("cn-hangzhou", $accessKeyId, $accessSecret);

        $client = new \DefaultAcsClient($iClientProfile);
        $request = new APush\PushRequest();
        $request->setAppKey($appKey);
        if(is_array($tokens)){
            $request->setTarget("device");
            $request->setTargetValue(implode(",", $tokens));
        }else{
            return false;
        }
//        else{
//            $request->setTarget("all");
//        }
        $request->setTitle($this->_config['TITLE']);
        $request->setSummary($message);
        $request->setBody($message); // 消息的内容
        $request->setStoreOffline('true');
        $request->setType(1);
        $request->setDeviceType(1);
        if($this->_xiaomi_activity){
            $request->setXiaomiActivity($this->_xiaomi_activity);
        }
        if($extras){
            $request->setAndroidExtParameters(json_encode($extras)); // 设定android类型设备通知的扩展属性
        }
        $response = $client->getAcsResponse($request);
        unset($client);
        if($response->ResponseId){
            return count($tokens);
        }
        return false;
    }
}