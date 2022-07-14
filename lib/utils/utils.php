<?php
namespace Svgta\EasyApi\utils;
use ParagonIE\ConstantTime\Base64UrlSafe;

class utils{
  public static function toDate(?int $time = null): string{
    if($time === null)
      $time = 0;

    if($time === 0)
      return 'not set';

    return date('Y-m-d H:i:s', $time);
  }
  public static function getRequest(array $req = []): array{
    if(isset($_REQUEST)){
      foreach($_REQUEST as $k=>$v){
        if(is_string($v))
          $_REQUEST[$k] = htmlspecialchars($v, ENT_QUOTES, "UTF-8");
      }
      $req = array_merge($req, $_REQUEST);
    }

    $in = file_get_contents("php://input");
    if(utils::isJson($in)){
      $input = json_decode($in, TRUE);
      $req = array_merge($req, $input);
    }

    $req['req_timestamp'] = time();
    return $req;
  }

  public static function getReqMethod(): string {
    return $_SERVER['REQUEST_METHOD'];
  }

  public static function getReqUri(){
    return $_SERVER['REQUEST_URI'];
  }

  public static function getUserIp(): string{
    return isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
  }

  public static function getUA(): array{
    $ua =  isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'UA not set';
    return [
      'UA' => $ua,
      'browscap_info' => @\get_browser(null, true),
    ];
  }

  public static function is_phpscript(string $string): bool{
    $string = trim($string);
    $pat = '/^\<\?php/';
    if(preg_match($pat, $string))
      return true;
    return false;
  }

  public static function isJson(string $string): bool{
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
  }

  public static function password_hash($pwd): string{
    return \password_hash($pwd, PASSWORD_ARGON2I);
  }

  public static function genPassword(int $bits = 512): string{
    return Base64UrlSafe::encodeUnpadded(random_bytes($bits / 8));
  }

  public static function genUUID(): string{
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      // 32 bits for "time_low"
      mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

      // 16 bits for "time_mid"
      mt_rand( 0, 0xffff ),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 4
      mt_rand( 0, 0x0fff ) | 0x4000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      mt_rand( 0, 0x3fff ) | 0x8000,

      // 48 bits for "node"
      mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
  }
}
