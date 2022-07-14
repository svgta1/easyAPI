<?php
namespace Svgta\EasyApi\backend\mongodb;
use Svgta\EasyApi\backend\Exception;
use Svgta\EasyApi\backend\abstractAuth;
use Svgta\EasyApi\utils\cache\requestCache;

class auth extends abstractAuth{
  protected $target = "auth";

  public function insert(array $data, ?abstractAuth $authRess = null){
    $data = $this->verify_insert($data);
    $col = $this->thisColl();
    $insert = $col->insertOne($data);
    $c = $insert->getInsertedCount();
    if(!($c === 1))
      throw new Exception('Erreur auth sur le nombre d\'insertion en base : ' . $c);
  }
  public function delete(string $id, ?abstractAuth $authRess = null){
    $col = $this->thisColl();
    $del = $col->deleteOne(['user_id' => $id]);
    $c = $del->getDeletedCount();
    if(!($c === 1))
      throw new Exception('Erreur auth sur le nombre de suppression en base : ' . $c);
  }
  public function get(string $id, ?abstractAuth $authRess = null){
    $search = 'get_'. $id;
    if($res = requestCache::getCache($this->target, $search))
      return $res;

    $col = $this->thisColl();
    if(!$res = $col->findOne(['user_id' => $id]))
      throw new Exception('Erreur auth user_id not found : ' . $id);

    requestCache::setCache($this->target, $search, $res);
    return $res;
  }
  public function update(string $id, array $data, ?abstractAuth $authRess = null, bool $updateTime = true){
      $col = $this->thisColl();
      if(isset($data['user_id']))
        unset($data['user_id']);
      if(!$update = $col->updateOne(
          ['user_id' => $id],
          ['$set' => $data]
      ))
        throw new Exception('Erreur auth user_id not updated : ' . $id);

      $c = $update->getModifiedCount();
      if(!($c === 1))
        throw new Exception('Erreur auth sur le nombre de mise à jour en base : ' . $c);
  }

  public function list(?int $limit = null, ?int $start = null){
    //not used
  }

  public function find(?array $search = null, ?abstractAuth $authRess = null){
    //not used
  }

  private function thisColl(?string $col = null){
    if(!$col)
      $col = $this->target;
    return $this->conn->{$col};
  }
}
