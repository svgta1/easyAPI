<?php
namespace Svgta\EasyApi\backend\mongodb;
use Svgta\EasyApi\backend\abstractReq;

abstract class mongoAbstract extends abstractReq{
  protected function thisColl(?string $col = null){
    if(!$col)
      $col = $this->target;
    return $this->conn->{$col};
  }
}
