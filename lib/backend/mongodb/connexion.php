<?php
namespace Svgta\EasyApi\backend\mongodb;
use Svgta\EasyApi\backend\abstractConnexion;

class connexion extends abstractConnexion{

  public function connect(){
    $conn = new \MongoDB\Client($this->config["uri"], $this->config["options"], $this->config["driver"]);
    $db = $this->config['db'];
    return $conn->{$db};
  }
}
