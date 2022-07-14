<?php
namespace Svgta\EasyApi\backend;
use Svgta\EasyApi\utils\conf;
use Svgta\EasyApi\backend\abstractAuth;

abstract class abstractReq{
  abstract public function insert(array $data, ?abstractAuth $authRess = null);
  abstract public function delete(string $id, ?abstractAuth $authRess = null);
  abstract public function get(string $id, ?abstractAuth $authRess = null);
  abstract public function update(string $id, array $data, ?abstractAuth $authRess = null,  bool $updateTime = true);
  abstract public function list(?int $limit = null, ?int $start = null);
  abstract public function find(?array $search = null, ?abstractAuth $authRess = null);

  protected $conn = null;
  public function __construct(){
    $dbType = $_ENV['DB_TYPE'];
    $namespace = __namespace__ . '\\' . $dbType;
    $dbConnClass = $namespace . '\\connexion';
    $dbConn = new $dbConnClass();
    $this->conn = $dbConn->getConn();
  }

  public function updateLastAccess(string $id, string $att = 'lastAccessTime'): void{
    $this->update($id, [
      $att => time(),
    ],
    null,
    false);
  }

  protected function getPagination(?int $limit = null, ?int $start = null){
    if($limit === null )
      $limit = conf::getConfKey('CONF_GENERAL', 'pagination_default')['limit'];
    if($limit > conf::getConfKey('CONF_GENERAL', 'pagination_default')['max_limit'])
      $limit = conf::getConfKey('CONF_GENERAL', 'pagination_default')['max_limit'];
    if(($start === null) OR  ($start < 1))
      $start = conf::getConfKey('CONF_GENERAL', 'pagination_default')['start'];

    return [
      'limit' => (int)$limit,
      'start' => (int)$start,
    ];
  }
}
