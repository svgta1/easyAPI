<?php
namespace Svgta\EasyApi\backend\mongodb;
use Svgta\EasyApi\backend\Exception;
use Svgta\EasyApi\backend\abstractLog;
use Svgta\EasyApi\utils\conf;
use Svgta\EasyApi\utils\utils;
use Svgta\EasyApi\backend\abstractAuth;

class log extends abstractLog{
  protected $target = "logs";

  public function insert(array $data, ?abstractAuth $authRess = null){
    $col = $this->thisColl();
    $insert = $col->insertOne($data);
    $c = $insert->getInsertedCount();
    if(!($c === 1))
      throw new Exception('Erreur log sur le nombre d\'insertion en base : ' . $c);
  }
  public function deleteOld(){
    $logRet = conf::getConfKey('CONF_GENERAL', 'log_retention');
    $time = time() - $logRet;
    $col = $this->thisColl();
    $del = $col->deleteMany(['timestamp' => ['$lt' => $time]]);
  }
  public function delete(string $id, ?abstractAuth $authRess = null){
    //not used
  }
  public function get(string $id, ?abstractAuth $authRess = null){
    //not used
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
      'sort' => ['timestamp' => -1],
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
      $ar['date'] = utils::toDate($ar['timestamp']);
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
