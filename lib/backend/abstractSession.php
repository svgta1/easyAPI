<?php
namespace Svgta\EasyApi\backend;
use Svgta\EasyApi\utils\utils;
use Svgta\EasyApi\backend\Exception;

abstract class abstractSession extends abstractReq{
  abstract public function deleteMulti(array $crit);
  abstract public function deleteOld();
  abstract public function countSession(string $user_id);
}
