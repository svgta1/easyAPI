<?php
namespace Svgta\EasyApi\backend\mongodb;
use Svgta\EasyApi\backend\abstractJWK;
use Svgta\EasyApi\utils\conf;
use Svgta\EasyApi\backend\Exception;
use Svgta\EasyApi\utils\cache\requestCache;
use Svgta\EasyApi\backend\abstractAuth;

class jwk extends abstractJWK{
  protected $target = "jwk";
  public function insert(array $data, ?abstractAuth $authRess = null){
    $col = $this->thisColl();
    $insert = $col->insertOne($data);
    $c = $insert->getInsertedCount();
    if(!($c === 1))
      throw new Exception('Erreur jwk sur le nombre d\'insertion en base : ' . $c);
  }
  public function delete(string $id, ?abstractAuth $authRess = null){
    $col = $this->thisColl();
    $del = $col->deleteOne(['kid' => $id]);
    $c = $del->getDeletedCount();
    if(!($c === 1))
      throw new Exception('Erreur jwk sur le nombre de suppression en base : ' . $c);
  }
  public function get(string $id, ?abstractAuth $authRess = null){
    $search = 'get_'. $id;
    if($res = requestCache::getCache($this->target, $search))
      return $res;

    $col = $this->thisColl();
    if(!$res = $col->findOne(['kid' => $id]))
      throw new Exception('Erreur jwk KID not exist');

    requestCache::setCache($this->target, $search, $res);
    return $res;
  }
  public function update(string $id, array $data, ?abstractAuth $authRess = null, bool $updateTime = true){
    //not used
  }
  public function find(?array $search = null, ?abstractAuth $authRess = null){
    //not used
  }
  public function list(?int $limit = null, ?int $start = null){
    //limit and start not used. We get all items;
    $search = 'list_all';
    if($res = requestCache::getCache($this->target, $search))
      return $res;

    $col = $this->thisColl();
    $cursor = $col->find([]);
    $ret = [];
    foreach($cursor as $doc)
      $ret[] = $doc;
    return $ret;
  }

  private function thisColl(?string $col = null){
    if(!$col)
      $col = $this->target;
    return $this->conn->{$col};
  }
}
