<?php
namespace Svgta\EasyApi\backend;
use Svgta\EasyApi\utils\conf;

abstract class abstractConnexion{
  protected $config = null;
  protected $conn = null;
  protected static $staticConn = null;

  abstract public function connect();

  public function __construct(){
    if(!self::$staticConn){
      $this->config = conf::getConf('DB_CONF');
      self::$staticConn = $this->connect();
    }
    $this->conn = self::$staticConn;
  }

  public function getConn(){
    return $this->conn;
  }
}
