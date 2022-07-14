<?php
use Svgta\EasyApi\utils\conf;
use Jose\Component\KeyManagement\JWKFactory;

class secureKey{
  private $config = null;
  public function __construct(){
    $this->config = conf::getConfKey("SECU_CONF", "secureKey");
  }

  public function getKey(){
    $key = JWKFactory::createOctKey(
        $this->config['keySize'],
        [
          'alg' => $this->config['alg'],
          'use' => $this->config['use']
        ]
    );
    return json_decode(json_encode($key), TRUE);
  }
}
