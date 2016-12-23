<?php
namespace PushWorker\push\Utils;
/**
 * Created by PhpStorm.
 * User: zhoujun
 * Date: 16/12/21
 * Time: 21:00
 */

class IosPush extends Push
{

    private $_fp;
    protected $_production_mode = true;

    public function connect(){
        $push_config = $this->_getIosPushConfig();
        return $this->_fp = $this->_connectAPNS($push_config);
    }

    public function exec(array $tokens, $message, $extras = array())
    {
        $send_count = 0;
        foreach ($tokens as $key => $token){

            $body = array(
                'aps' => array(
                    'alert' => array("body" => $message, "action-loc-key" => "阅读"),
                    'badge' => 1,
                    'sound' => 'default',
                ),
            );
            if($extras && is_array($extras)){
                foreach ($extras as $key => $v){
                    $body[$key] = $v;
                }
            }
            $payload = json_encode($body);
            $json_size = strlen($payload);
            $msg = chr(0) . pack('n', 32) . pack('H*', trim($token)) . pack('n', $json_size) . $payload;
            $msg_size = strlen($msg);
            $result = fwrite($this->_fp, $msg, $msg_size);
            if(!$result){
                $this->close();
                $this->connect();
                if(!$this->_fp){
                    continue;
                }
            }else{
                $send_count++;
            }
        }
        return $send_count;
    }

    public function close(){
        if($this->_fp){
            fclose($this->_fp);
        }
    }

    private function _connectAPNS($push_config){
        $passphrase = $push_config['IOS_PASSPHRASE'];
        $apns_host 	= $push_config['IOS_APNS_HOST'];
        $apns_cert 	= $push_config['IOS_APNS_CERT'];
        $apns_port = $push_config['IOS_APNS_PORT'];
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $apns_cert);
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
        $result = stream_socket_client('ssl://'.$apns_host.':'.$apns_port, $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

        return $result;
    }

    private function _getIosPushConfig(){
        $push_config = include 'push/conf/config.php';
        $config = $push_config["IOS_PUSH_CONFIG"];
        $config["IOS_PRODUCTION_MODE"] = $this->_production_mode;
        $config["IOS_APNS_HOST"] = $this->_production_mode? "gateway.push.apple.com" : "gateway.sandbox.push.apple.com";
        $package_name = $this->_package_name . "/";
        $cert_name = $this->_production_mode? "push_production.pem" : "push_development.pem";
        $config["IOS_APNS_CERT"] = 'vendor/Identify/' . $package_name . $cert_name;
        return $config;
    }
}