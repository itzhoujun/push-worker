<?php
namespace PushWorker\push\Utils;

use JPush\JPushClient;

require_once 'push/conf/config.php';
require_once 'vendor/Jpush/autoload.class.php';

class JPush extends Push
{
    public function __construct($package_name)
    {
        parent::__construct($package_name);
    }

    public function exec(array $tokens, $message, $extras = array())
    {
        $platform = 'android';
        $appkey = $this->_config["APPKEY"];
        $secret = $this->_config["SECRET"];
        if(!$this->_config){
            return false;
        }
        $client = new JPushClient($appkey, $secret);
        $notification = array("alert" => $message);
        if($extras){
            $notification[$platform] = array('extras' =>$extras);
        }
        $audience = array("registration_id" => $tokens);
        try {
            $result = $client->push()
                ->setPlatform($platform)//"android", "ios", "winphone"
                ->setAudience($audience)
                ->setNotification($notification)
                ->send();
            $result = json_decode($result->json,true);
            if(isset($result['sendno']) && isset($result['msg_id'])){
                return count($tokens);
            }else{
                return false;
            }
        }catch (\JPush\Exception\APIRequestException $e){
            return false;
        }catch (\JPush\Exception\APIConnectionException $e){
            return false;
        }
    }
}