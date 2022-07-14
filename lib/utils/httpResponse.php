<?php
namespace Svgta\EasyApi\utils;
use Svgta\EasyApi\controller\ctrlLog as log;
use Svgta\EasyApi\utils\JWE;
use Svgta\EasyApi\utils\JWK;

class httpResponse{
  private static $encKey = null;
  private static $saveLog = true;

  public static function setEncKey($encKey): void{
      self::$encKey = $encKey;
  }

  public static function error401($msg = null): void{
    self::error(401, $msg);
  }

  public static function error403($msg = null): void{
    self::error(403, $msg);
  }

  public static function error404($msg = null): void{
    self::error(404, $msg);
  }

  public static function error406($msg = null): void{
    self::error(406, $msg);
  }

  public static function code201($msg = null): void{
    self::code(201, $msg);
  }

  public static function code204(): void{
    self::code(204, $msg);
  }

  public static function code200($msg = null, bool $saveToLog = true): void{
    self::$saveLog = $saveToLog;
    self::code(200, $msg);
  }

  private static function code(int $code, $msg): void{
    http_response_code($code);
    $return = self::setReturn($msg);
    if(self::$saveLog){
      $log = new log($code, $msg);
      $log->set();
    }
    echo json_encode($return, JSON_PRETTY_PRINT);
    die();
  }

  public static function error(int $code, $msg = null): void{
    http_response_code($code);
    if(self::$saveLog){
      $log = new log($code, $msg);
      $log->set();
    }
    echo json_encode(self::setReturn($msg), JSON_PRETTY_PRINT);
    die();
  }

  private static function setReturn($msg): array|string {
    $return = [];
    if($msg){
      if(is_array($msg))
        $return = $msg;
      else {
        $return['msg'] = $msg;
      }
    }
    if(self::$encKey){
      $oct = JWK::getKey(self::$encKey);
      $return = ['JWE' => JWE::encrypt($msg, JWK::createOctFromArray($oct))];
    }
    return $return;
  }
}
