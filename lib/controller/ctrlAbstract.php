<?php
namespace Svgta\EasyApi\controller;

class ctrlAbstract{
  protected function loadBackend(string $target){
    $dbType = $_ENV['DB_TYPE'];
    $backend = str_replace('\\controller', '', __namespace__) . '\\backend\\' . $dbType . '\\';
    $str = $backend . $target;
    return new $str();
  }
}
