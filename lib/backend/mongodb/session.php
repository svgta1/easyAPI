<?php
namespace Svgta\EasyApi\backend\mongodb;
use Svgta\EasyApi\backend\Exception;
use Svgta\EasyApi\backend\abstractSession;
use Svgta\EasyApi\backend\abstractAuth;
use Svgta\EasyApi\utils\utils;

class session extends abstractSession{
  protected $target = "session";

  public function insert(array $data, ?abstractAuth $authRess = null){
    $col = $this->thisColl();
    $insert = $col->insertOne($data);
    $c = $insert->getInsertedCount();
    if(!($c === 1))
      throw new Exception('Erreur session sur le nombre d\'insertion en base : ' . $c);
  }
  public function delete(string $id, ?abstractAuth $authRess = null){
    $col = $this->thisColl();
    $del = $col->deleteOne(['token' => $id]);
    $c = $del->getDeletedCount();
    if(!($c === 1))
      throw new Exception('Erreur session sur le nombre de suppression en base : ' . $c);
  }
  public function deleteOld(){
    return $this->deleteMulti(['exp' => ['$lt' => time()]]);
  }
  public function deleteMulti(array $crit){
    $col = $this->thisColl();
    $del = $col->deleteMany($crit);
    return $del->getDeletedCount();
  }
  public function get(string $id, ?abstractAuth $authRess = null){
    $col = $this->thisColl();
    if(!$get = $col->findOne(['token' => $id]))
      throw new Exception('Session not set');

    return $get;
  }

  public function countSession(string $user_id){
    $col = $this->thisColl();
    return $col->count(['client_id' => $user_id]);
  }

  public function update(string $id, array $data, ?abstractAuth $authRess = null, bool $updateTime = true){
    //not used
  }
  public function list(?int $limit = null, ?int $start = null){
    $pagination = $this->getPagination($limit, $start);
    $search = 'list_'. $pagination['limit'] . '_' . $pagination['start'];
    $skip = ($pagination['start'] - 1) * $pagination['limit'];
    $col = $this->thisColl();
    $cursor = $col->find([], [
      'limit' => $pagination['limit'],
      'skip' => $skip,
    ]);

    $ret = [
      'list'=>[],
      'global'=>$col->count([]),
      'page' => $pagination['start'],
      'limitPerPage' => $pagination['limit'],
      'count' => 0,
    ];
    foreach($cursor as $doc){
      $ar = $doc;
      unset($ar->_id);
      $ar['exp_date'] = utils::toDate($ar['exp']);
      $ret['list'][] = $ar;
    }
    $ret['count'] = count($ret['list']);
    return $ret;
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
