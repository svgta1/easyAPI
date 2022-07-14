<?php
namespace Svgta\EasyApi\utils;

class JWT{
  public static function isJWT(string $jwt):bool {
    return (count(explode('.', $jwt)) == 3);
  }
}
