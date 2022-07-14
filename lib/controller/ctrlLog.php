<?php
namespace Svgta\EasyApi\controller;
use Svgta\EasyApi\utils\security;
use Svgta\EasyApi\utils\utils;

class ctrlLog extends ctrlAbstract{
  private $code = null;
  private $msg = null;
  private static $hidden_data = [];

  const HIDDEN_DATA = [
    'user_secret',
    'admin_secret',
    'client_secret',
    'auth_pwd',
    'shared_key',
  ];
  const HIDDEN_MSG = 'Hidden data';

  public function __construct(?int $code = null, $msg = null){
    $this->code = $code;
    $this->msg = $msg;
  }

  public static function addHiddenData(array $newData = []){
    self::$hidden_data = $newData;
  }

  public function set(){
    $log = [
      'request' => [
        'req_uri' => utils::getReqUri(),
        'req_method' => utils::getReqMethod(),
        'req_data' => utils::getRequest(),
      ],
      'response' => [
        'res_code' => $this->code,
        'res_msg' => $this->hideSecret(),
      ],
      'user' => [
        'user_agent' => utils::getUA(),
        'user_ip' => utils::getUserIp(),
      ],
      'auth' => $this->hideAuth(),
      'timestamp' => time(),
      'headers' => getallheaders(),
    ];
    $ressource = $this->loadBackend('log');

    try{
      $res = $ressource->insert($log);
    }catch(backendException $e){
      utils\httpResponse::error406($e->getMessage());
    }
  }

  private function hideSecret($msg = null){
    if($msg === null)
      $msg = $this->msg;
    $msg = json_decode(json_encode($msg), TRUE);
    $hidden = array_merge(self::$hidden_data, self::HIDDEN_DATA);
    foreach($hidden as $k => $v){
      $hidden[$k] = mb_strtolower($v);
    }
    if(is_array($msg))
    foreach($msg as $k => $v){
      if(in_array(mb_strtolower($k), $hidden) AND is_string($v))
        $msg[$k] = self::HIDDEN_MSG;
      if(is_array($v)){
        $msg[$k] = $this->hideSecret($v);
      }
    }
    return $msg;
  }

  private function hideAuth(){
    $auth = security::getAuth();
    return $this->hideSecret($auth);
  }
}
