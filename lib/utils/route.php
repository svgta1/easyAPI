<?php
namespace Svgta\EasyApi\utils;
use Svgta\EasyApi\utils\httpResponse;
use Svgta\EasyApi\utils\conf;

class route{
  private static $get = [];
  private static $post = [];
  private static $put = [];
  private static $delete = [];

  public static function routeExist(string $uri, string $method){
    $route = null;
    switch($method){
      case 'GET' :
        $route = self::$get;
        break;
      case 'POST' :
        $route = self::$post;
        break;
      case 'PUT' :
        $route = self::$put;
        break;
      case 'DELETE' :
        $route = self::$delete;
        break;
    }
    if(!$route)
      httpResponse::error404('Route not found');

    $basePath = conf::getConfKey('CONF_GENERAL', 'basePath');
    if($basePath AND (substr($basePath, -1) == '/'))
      $basePath = substr($basePath, 0, -1);
    if(substr($uri, -1) == '/')
      $uri = substr($uri, 0, -1);
    if($basePath)
      $uri = str_replace($basePath . '/', '', $uri);

    $routeExist = false;
    $return = null;
    foreach($route as $k => $v){
      if($k === $uri){
        $routeExist = true;
        $return = [
          'class' => $v['ar'][0],
          'method' => $v['ar'][1],
          'scope' => isset($v['ar'][2]) ? $v['ar'][2] : '',
        ];
        $return['route'] = $k;
        $return['arg'] = null;
        $return['dir'] = $v['dir'];
        break;
      }
    }

    if(!$routeExist){
      $pat = '/^\{.*\}$/';
      foreach($route as $k => $v){
        $r = explode('/', $k);
        $u = explode('/', $uri);
        if(count($u) != count($r))
          continue;

        if(!preg_match($pat, end($r)))
          continue;

        for($i=0; $i < count($r) - 1; $i++)
          if(!$r[$i] == $u[$i])
            continue;
        $return = [
          'class' => $v['ar'][0],
          'method' => $v['ar'][1],
          'scope' => isset($v['ar'][2]) ? $v['ar'][2] : '',
        ];
        $return['route'] = $k;
        $return['arg'] = end($u);
        $return['dir'] = $v['dir'];
      }
    }

    if(!$return)
      httpResponse::error404('Controller not found');

    return $return;
  }

  public static function getRoutes(){
    return [
      'get' => self::$get,
      'post' => self::$post,
      'put' => self::$put,
      'delete' => self::$delete,
    ];
  }

  public static function get(?string $uri, ?string $dir = null, ?array $ar = []){
      if($uri)
        self::$get[$uri] = [
            'ar' => $ar,
            'dir' => $dir,
        ];
  }

  public static function post(?string $uri, ?string $dir = null, ?array $ar = []){
      if($uri)
        self::$post[$uri] = [
            'ar' => $ar,
            'dir' => $dir,
        ];
  }

  public static function put(?string $uri, ?string $dir = null, ?array $ar = []){
      if($uri)
        self::$put[$uri] = [
            'ar' => $ar,
            'dir' => $dir,
        ];
  }

  public static function delete(?string $uri, ?string $dir = null, ?array $ar = []){
      if($uri)
        self::$delete[$uri] = [
            'ar' => $ar,
            'dir' => $dir,
        ];
  }
}
