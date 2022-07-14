<?php
namespace Svgta\EasyApi\backend;
use Svgta\EasyApi\utils\conf;
use Svgta\EasyApi\utils\utils;
use Svgta\EasyApi\backend\Exception;

abstract class abstractAuth extends abstractReq{
  protected function verify_insert(array $data): array{
    $data['createTime'] = time();
    $data['updateTime'] = time();
    $data['lastAuthTime'] = 0;
    return $data;
  }
}
