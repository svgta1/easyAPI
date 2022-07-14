<?php
namespace Svgta\EasyApi\utils\cache;

class requestCache{
  private static array $cache = [];

  public static function getCache(string $target, string $search){
      if(!isset(self::$cache[$target]))
        return null;
      if(!isset(self::$cache[$target][$search]))
        return null;

      return self::$cache[$target][$search];
  }
  public static function setCache(string $target, string $search, $value){
    if(!isset(self::$cache[$target]))
      self::$cache[$target] = [];
    self::$cache[$target][$search] = $value;
  }
}
