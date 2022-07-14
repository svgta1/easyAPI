<?php
namespace Svgta\EasyApi\controller;
use Svgta\EasyApi\utils\utils;
use Svgta\EasyApi\utils\security;
use Svgta\EasyApi\backend\abstractReq;
use Svgta\EasyApi\controller\ctrlAbstract;

class apictrlAbstract extends ctrlAbstract{
  protected $security = null;
  protected $backend = null;
  protected $reqBody = [];
  protected $payload = null;
  protected $scope = null;

  protected static $cacheBackend = [];

  public function __construct(string $backend = null, array $request = [], ?string $scopes = null){
    $this->backend = $backend;
    $this->reqBody = $request;
    $resSession = $this->loadBackend('session');
    $resSession->deleteOld();
    security::setSessionRessource($resSession);
    if($scopes){
      $resJwk = $this->loadBackend('jwk');
      $this->payload = security::verifyAuthBearer($resJwk);
      $this->scope = $this->getScope($scopes);
    }
  }

  protected function loadBackend(string $target){
    if(isset(self::$cacheBackend[$target]))
      return self::$cacheBackend[$target];
    $str = $this->backend . $target;
    self::$cacheBackend[$target] = new $str();
    return self::$cacheBackend[$target];
  }

  protected function verifyScope($scope) : string{
    $scope = security::scope_authorized($scope);
    return $scope;
  }

  protected function getScope($scopes) : string{
    return $this->verifyScope($scopes);
  }

}
