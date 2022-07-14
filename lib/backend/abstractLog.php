<?php
namespace Svgta\EasyApi\backend;

abstract class abstractLog extends abstractReq{
  abstract public function deleteOld();

  public function __construct(){
    parent::__construct();
    $this->deleteOld();
  }
}
