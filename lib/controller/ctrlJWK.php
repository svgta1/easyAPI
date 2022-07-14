<?php
namespace Svgta\EasyApi\controller;

class ctrlJWK extends ctrlAbstract{
  private $backend;
  public function __construct(){
      $this->backend = $this->loadBackend('jwk');
  }

  public function getPrivateKeys(){
    return $this->backend->getPrivateKeySet();
  }

  public function getPublicKeys(){
    return $this->backend->getPublicKeySet();
  }

}
