<?php
namespace Svgta\EasyApi\backend\mongodb;
use Svgta\EasyApi\backend\abstractAdmin;
use Svgta\EasyApi\utils\conf;
use Svgta\EasyApi\backend\Exception;
use Svgta\EasyApi\utils\cache\requestCache;
use Svgta\EasyApi\backend\abstractAuth;

class admin extends abstractAdmin{
  protected $target = "admin";

  public function find(?array $search = null, ?abstractAuth $authRess = null){
    if($search === null)
      throw new Exception('Bad search param');
    $col = $this->thisColl();
    if($col->count($search) > 1)
      throw new Exception('To many admin with same search params');

    if(!$doc = $col->findOne($search))
      throw new Exception('Admin not found');

    if($authRess === null)
      $authRess = new auth();
    return [
      'user_info' => $doc,
      'auth_info' => $authRess->get($doc->admin_id),
    ];
  }

  public function insert(array $data, ?abstractAuth $authRess = null){
    $data = $this->verify_insert($data);
    $col = $this->thisColl();
    $admin_secret = $data['admin_secret'];
    unset($data['admin_secret']);
    $scope = $data['scope'];
    unset($data['scope']);

    $insert = $col->insertOne($data);
    $c = $insert->getInsertedCount();
    if(!($c === 1))
      throw new Exception('Erreur clients sur le nombre d\'insertion en base : ' . $c);

    $authData = [
      'user_id' => $data['admin_id'],
      'user_secret' => $admin_secret,
      'scope' => $scope,
    ];
    if($authRess === null)
      $authRess = new auth();

    $authRess->insert($authData);

    return [
        'data' => $data,
        'admin_secret' => $admin_secret,
        'nbr_insert' => $insert->getInsertedCount(),
        'oid' => $insert->getInsertedId(),
    ];
  }
  public function delete(string $id, ?abstractAuth $authRess = null){
    if($authRess === null)
      $authRess = new auth();
    try{
      $authRess->delete($id);
    }catch(Exception $e){
    }

    $col = $this->thisColl();
    $del = $col->deleteOne(['admin_id' => $id]);
    $c = $del->getDeletedCount();
    if(!($c === 1))
      throw new Exception('Erreur admin sur le nombre de suppression en base : ' . $c);
  }

  public function get(string $id, ?abstractAuth $authRess = null){
    $col = $this->thisColl();
    if(!$doc = $col->findOne(['admin_id' => $id]))
      throw new Exception('Admin not found');

    if($authRess === null)
      $authRess = new auth();
    return [
      'admin_info' => $doc,
      'auth_info' => $authRess->get($id),
    ];
  }
  public function update(string $id, array $data, ?abstractAuth $authRess = null, bool $updateTime = true){
    $data = $this->verify_update($data);
    if(!$updateTime)
      unset($data['updateTime']);
    $col = $this->thisColl();
    if(isset($data['admin_id']))
      unset($data['admin_id']);

    $admin_secret = null;
    if(isset($data['admin_secret'])){
      $admin_secret = $data['admin_secret'];
      unset($data['admin_secret']);
    }
    $scope = null;
    if(isset($data['scope'])){
      $scope = $data['scope'];
      unset($data['scope']);
    }

    if(!$update = $col->updateOne(
        ['admin_id' => $id],
        ['$set' => $data]
    ))
      throw new Exception('Erreur admin admin_id not updated : ' . $id);

    $c = $update->getModifiedCount();
    if(!($c === 1))
      throw new Exception('Erreur admin sur le nombre de mise à jour en base : ' . $c);

    if($admin_secret OR $scope){
      $authData = [];
      if($admin_secret)
        $authData['user_secret'] = $admin_secret;
      if($scope)
        $authData['scope'] = $scope;
      try{
        if($authRess === null)
          $authRess = new auth();
        $authRess->update($id, $authData);
      }catch(Exception $e){
      }
    }

    return [
      'admin_info' => $data,
      'auth_info' => [
        'user_secret' => $admin_secret ?? null,
        'scope' => $scope ?? null,
      ],
    ];
  }
  public function list(?int $limit = null, ?int $start = null){
    $pagination = $this->getPagination($limit, $start);
    $search = 'list_'. $pagination['limit'] . '_' . $pagination['start'];
    if($res = requestCache::getCache($this->target, $search))
      return $res;

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
      $ar = [
        'admin_id' => $doc->admin_id,
        'given_name' => $doc->given_name,
        'family_name' => $doc->family_name,
        'email' => $doc->email,
      ];
      $ret['list'][] = $ar;
    }
    $ret['count'] = count($ret['list']);

    requestCache::setCache($this->target, $search, $ret);
    return $ret;
  }

  private function thisColl(?string $col = null){
    if(!$col)
      $col = $this->target;
    return $this->conn->{$col};
  }
}
