<?php
use Svgta\EasyApi\controller\apictrlAdmin;
use Svgta\EasyApi\utils\conf;
use Svgta\EasyApi\utils\utils;

class superAdmin{
  private $adminClass = null;
  private $authClass = null;
  private $config = null;
  private static $adminBackend = 'admin';
  private static $authBackend = 'auth';

  public function __construct($backend){
    $classAdmin = $backend . self::$adminBackend;
    $this->adminClass = new $classAdmin();
    $classAuth = $backend . self::$authBackend;
    $this->authClass = new $classAuth();
    $this->config = conf::getConfKey("SECU_CONF", "superAdmin_info");
  }

  public function genAdmin(){
    $secret = utils::genPassword(256);
    $scopeList = conf::getConfKey('CONF_GENERAL', 'scope_default');
    $data = [
      "email" => $this->config['email'],
      "given_name" => $this->config['given_name'],
      "family_name" => $this->config['family_name'],
      "admin_secret" => $secret,
      "scope" => $scopeList['super_admin'],
    ];

    $res = $this->adminClass->insert($data, $this->authClass);
    $ret = [
      'admin_id' => $res['data']['admin_id'],
      'admin_secret' => $secret,
      'scope' => $scopeList['super_admin'],
    ];
    return $ret;
  }
}
