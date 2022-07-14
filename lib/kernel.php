<?php
namespace Svgta\EasyApi;
use Svgta\EasyApi\utils\route;
use Svgta\EasyApi\utils\conf;
use Svgta\EasyApi\utils\utils;

class kernel{
  protected $route = null;
  protected $parent_backend = null;
  public function __construct(){
    $dbType = $_ENV['DB_TYPE'];
    $this->parent_backend = __namespace__ . '\\backend\\' . $dbType . '\\';

    require dirname(__FILE__, 1) . "/routes.php";
    header('Content-Type: application/json; charset=utf-8');
    $this->route = route::routeExist($this->prepareUri(), utils::getReqMethod());
    $this->route['request_method'] = utils::getReqMethod();

    $dirVersion = null;
    if($this->route['dir'])
      $dirVersion = $this->route['dir'] . '\\';
    $class = __namespace__ . '\\controller\\' . $dirVersion . $this->route['class'];
    if(\class_exists($class)){
      $c = new $class($this->parent_backend, utils::getRequest(), $this->route['scope']);
      $method = $this->route['method'];
      $arg = $this->route['arg'];
      $c->$method($arg);
    }
  }

  protected function prepareUri(){
    $pathInfo = pathinfo(utils::getReqUri());
    $uri = $pathInfo['dirname'];
    $baseAr = explode('?', $pathInfo['basename']);
    $uri .= '/' . $baseAr[0];

    return $uri;
  }
}
