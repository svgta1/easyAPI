<?php
namespace Svgta\EasyApi\backend;
use Svgta\EasyApi\utils\utils;
use Svgta\EasyApi\backend\Exception;
use Svgta\EasyApi\utils\JWK;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Core\JWKSet;

abstract class abstractJWK extends abstractReq{
  protected function genKeySig(){
    $rsa = JWK::genSigRsa();
    $rsa['createTime'] = time();
    $this->insert($rsa);
  }

  protected function genKeyEnc(){
    $rsa = JWK::genEncRsa();
    $rsa['createTime'] = time();
    $this->insert($rsa);
  }

  public function getPublicKeySet(){
    $keys = $this->getKeys();
    $jwks = [];
    foreach($keys as $key){
      if($key->exp_verify < time())
        continue;
      $ar = json_decode(json_encode($key->publicKey), TRUE);
      $ar['kid'] = $key->kid;
      $k = JWKFactory::createFromValues($ar);
      $jwks[] = $k;
    }

    $jwkSet = new JWKSet($jwks);
    return json_decode(json_encode($jwkSet), TRUE);
  }

  public function getPrivateKeySet(){
    $keys = $this->getKeys();
    $jwks = [];
    foreach($keys as $key){
      if($key->exp < time())
        continue;

      $ar = JWK::getKey($key->privateKey_encrypted);
      $ar['kid'] = $key->kid;
      $k = JWKFactory::createFromValues($ar);
      $jwks[] = $k;
    }
    $jwkSet = new JWKSet($jwks);
    return json_decode(json_encode($jwkSet), TRUE);
  }

  protected function getKeys(){
    $res = $this->list();
    if(count($res) === 0){
      $this->genKeySig();
      $this->genKeyEnc();
      $res = $this->list();
    }

    $privateKeyCount = ['enc' => 0, 'sig' => 0];
    foreach($res as $k => $key){
      if(($key->exp < time()) AND ($key->exp_verify < time())){
        $this->delete($key->kid);
        unset($res[$k]);
      }
      if($key->exp > time())
        $privateKeyCount[$key->publicKey->use]++;
    }

    $actuList = false;
    if($privateKeyCount['enc'] === 0){
      $actuList = true;
      $this->genKeyEnc();
    }
    if($privateKeyCount['sig'] === 0){
      $actuList = true;
      $this->genKeySig();
    }

    if($actuList)
      $res = $this->list();
    return $res;
  }
}
