<?php
namespace Svgta\EasyApi\utils;
use Svgta\EasyApi\utils\utils;
use Svgta\EasyApi\utils\Exception;

class conf{
  private static $conf = [];
  private static $envFile = null;

  public static function setEnvFile(?string $envFile = null): void{
    self::$envFile = $envFile;
  }

  public static function getConf(string $env): array{
    if(!isset(self::$conf[$env]) AND isset($_ENV[$env])){
      if(!isset($_ENV[$env]) OR !is_file($_ENV[$env]))
        return [];

      $c = file_get_contents($_ENV[$env]);
      if(is_string($c) AND utils::isJson($c)){
        $config = json_decode($c, TRUE);
      }else if (utils::is_phpscript($c)){
        require $_ENV[$env];
      }else {
        throw new Exception('Conf file must be a php script or a json file');
      }
      if(!isset($config))
        httpResponse::error406('Conf file malformed.');

      self::$conf[$env] = $config;
    }

    if(isset(self::$conf[$env]))
      return self::$conf[$env];

    return [];
  }

  public static function getConfKey(string $env, string $key){
    $conf = self::getConf($env);
    if(isset($conf[$key]))
      return $conf[$key];

    return null;
  }

  public static function setConfKey(string $env, string $key, $value){
    $conf = self::getConf($env);

    $conf[$key] = $value;
    self::$conf[$env] = $conf;
  }

  public static function saveConf(string $env){
    if(!isset($_ENV[$env])){
      $bDir = $_ENV['BASE_DIR_CONF'];
      $filename = strtolower($env). '.json';
      $_ENV[$env] = $bDir . '/' . $filename;
    }

    if(isset(self::$conf[$env])){
      $pathInfo = pathinfo($_ENV[$env]);
      if(is_file($_ENV[$env]))
        rename($_ENV[$env], $_ENV[$env] . '.' . date('YmdHis'));
      $saveFile = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.json';
      file_put_contents($saveFile, json_encode(self::$conf[$env], JSON_PRETTY_PRINT));

      $content = file_get_contents(self::$envFile);
      $lines = explode(PHP_EOL, $content);
      $found = false;
      foreach($lines as $k => $line){
        $a = explode('=', $line);
        if(!($a[0] == $env))
          continue;
        else{
          $found = true;
          $a[1] = $saveFile;
          $line = implode('=', $a);
          $lines[$k] = $line;
          break;
        }
      }
      if(!$found){
        array_push($lines, $env . '=' . $saveFile);
      }
      file_put_contents(self::$envFile, implode(PHP_EOL, $lines));
      unset(self::$conf[$env]);
      $_ENV[$env] = $saveFile;
      return self::getConf($env);
    }
  }
}
