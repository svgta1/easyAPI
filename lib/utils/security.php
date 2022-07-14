<?php
namespace Svgta\EasyApi\utils;
use Svgta\EasyApi\utils\JWT;
use Svgta\EasyApi\utils\JWS;
use Svgta\EasyApi\utils\JWE;
use Svgta\EasyApi\utils\JWK;
use Svgta\EasyApi\utils\conf;
use Svgta\EasyApi\utils\utils;
use Svgta\EasyApi\backend\Exception;
use Svgta\EasyApi\controller\ctrlJWK;
use GuzzleHttp\Client;
use Svgta\EasyApi\utils\httpResponse;

class security{
  private static $class = null;
  private static $scope = null;
  private $auth = null;
  private static $resSession = null;

  private static $scope_multi_session = 'multi_session';

  public function __construct($resSession = null){
    $headers = getallheaders();
    if(isset($headers['Authorization']))
      $this->auth = $headers['Authorization'];
  }

  public static function setSessionRessource($resSession = null){
    self::$resSession = $resSession;
  }

  public static function scope_authorized($scope): string {
    if(!$ret = self::is_scopeAuth($scope, self::getScope()))
      httpResponse::error403("Not authorized to access this ressource - bad scopes. Scope needed : " . str_replace(' ', ' OR ', $scope));

    return $ret;
  }

  public static function is_scopeAuth($scope, $userScope): ?string {
    $ok = false;
    if(is_string($scope))
      $scope = explode(' ', $scope);

    if(is_string($userScope))
      $userScope = explode(' ', $userScope);

    foreach($userScope as $s){
      if(in_array($s, $scope)){
        $ok = $s;
        break;
      }
    }
    return $ok;
  }

  public static function getScope(): array {
      return explode(' ', self::$scope);
  }

  public static function verifyAuthBasic($ressource){
    self::isValidAuthorization('Basic');
    $user = self::isValidUser($ressource);
    self::verifyMultiSession($user->scope, self::getAuth()["info"]["AUTH_USER"]);
    return $user;
  }

  public static function verifyAuthJWE($ressource){
    self::isValidAuthorization('Bearer');
    $user = self::isValidUser($ressource);
    self::verifyMultiSession($user->scope, self::getAuth()["info"]["AUTH_USER"]);
    return $user;
  }

  public static function verifyMultiSession(string $userScope = '', string $userId = ''):void {
    $sesConf = conf::getConfKey('CONF_GENERAL', 'multi_session');
    if(!isset($sesConf['allowed']))
      $sesConf['allowed'] = false;
    if(!$sesConf['allowed']){
      $scope = explode(' ', $userScope);
      if(!in_array(self::$scope_multi_session, $scope)){
        $nbrSession = self::$resSession->countSession($userId);
        if($nbrSession > 0){
          if(!$sesConf['delete_old']){
            httpResponse::error403('A session is already set. Multi session not allowed. User as not scope ' . self::$scope_multi_session);
          }else{
              self::$resSession->deleteMulti(['client_id' => $userId]);
          }
        }
      }
    }
  }

  public static function isValidUser($ressource){
    try{
      $user = $ressource->get(self::$class->getAuth()["info"]["AUTH_USER"]);
    }catch(Exception $e){
      httpResponse::error401($e->getMessage());
    }
    if(!password_verify(self::$class->getAuth()["info"]["AUTH_PWD"], $user->user_secret))
      httpResponse::error401("Bad user or password");
    return $user;
  }

  public static function verifyAuthOauth(){
    self::isValidAuthorization('Bearer');
    $conf = conf::getConf('OAUTH_CONF');
    $gClient = new Client();
    $gParams = $conf['GuzzleHttp_params'];
    if(!isset($gParams['headers']))
      $gParams['headers'] = [];
    if(!isset($gParams['headers']['http_errors']))
      $gParams['headers']['http_errors'] = false;

    if(!in_array($conf['type'], ['openIdConnect', 'oauth2']))
      httpResponse::error406();
    if($conf['type'] == 'openIdConnect'){
      $res = $gClient->get($conf['access']['url_config'], $gParams);
      if(!($res->getStatusCode() == 200))
        httpResponse::error($res->getStatusCode());
      $endpoints = json_decode($res->getBody());
      $oauth_userinfo = $endpoints->userinfo_endpoint;
    }
    if($conf['type'] == 'oauth2'){
      $oauth_userinfo = $conf['access']['userInfoUrl'];
    }

    $access_token = self::getAuth()['value'];
    $gParams['headers']['Authorization'] = 'Bearer ' . $access_token;
    $res = $gClient->get($oauth_userinfo, $gParams);
    if(!($res->getStatusCode() == 200))
      httpResponse::error($res->getStatusCode());

    $json = json_decode($res->getBody());
    $mappings = conf::getConfKey('OAUTH_CONF', 'mappings');
    $search = [];
    foreach($mappings as $name => $ar){
      $search[$name] = [
        'class' => $ar['class'],
        'targetId' => $ar['target_id'],
        'search' => [],
      ];
      foreach($ar['mapping'] as $map){
        $search[$name]['search'][$map['api_att']] = $json->{$map['provider_claim']};
      }
    }

    $return = [
        'status_code' => $res->getStatusCode(),
        'json' => $json,
        'search' => $search,
    ];
    return $return;
  }

  public static function verifyAuthBearer($ressource): array{
    self::isValidAuthorization('Bearer');
    return self::isValidJWT($ressource);
  }

  public static function isValidJWT($ressource): array{
    if(self::$class === null)
      self::$class = new security();
    $JWT = self::$class->getAuth()['value'];
    if(!JWT::isJWT($JWT))
      httpResponse::error403(' Bad Authorization Bearer : not a JWT');

    $keySet = $ressource->getPublicKeySet();
    if(!$payload = JWS::verifySignKeySet($JWT, $keySet))
      httpResponse::error403(' Bad Authorization Bearer : JWT can\'t be verify');

    $jwtConf = conf::getConfKey('CONF_GENERAL', 'verify_JWT');
    try{
      $session = self::$resSession->get($payload['jti']);
    }catch(Exception $e){
      httpResponse::error403($e->getMessage());
    }
    if(!($session->enc_pwd === null)){
      httpResponse::setEncKey($session->enc_pwd);
    }

    if($jwtConf['exp'])
    if($payload['exp'] < time())
      httpResponse::error403(' Bad Authorization Bearer : JWT expired');

    if($jwtConf['nbf'])
    if($payload['nbf'] > time())
      httpResponse::error403(' Bad Authorization Bearer : JWT not active');

    if($jwtConf['iss'])
    if($payload['iss'] != conf::getConfKey('CONF_GENERAL', 'appName'))
      httpResponse::error403(' Bad Authorization Bearer : JWT bad Issuer');

    if($jwtConf['client_ip'])
    if(!($session->client_ip == utils::getUserIp()))
      httpResponse::error403('Session seems to be stolen 1');

    if($jwtConf['client_ua'])
    if(!(json_encode($session->client_ua) == json_encode(utils::getUA())))
      httpResponse::error403('Session seems to be stolen 2');

    self::$scope = $payload['scope'];
    return $payload;
  }

  public static function isValidAuthorization(string $type){
    if(self::$class === null)
      self::$class = new security();
    if(!isset(self::$class->getAuth()['type']) OR !(self::$class->getAuth()['type'] == $type))
      httpResponse::error403(' Bad Authorization header type. ' . $type . ' expected');
  }

  public function getInfo(array $ar){
    switch($ar[0]){
      case 'Basic':
        $info = $this->getInfoBasic($ar[1]);
        break;
      case 'Bearer':
        $info = $this->getInfoBearer($ar[1]);
        break;
    }
    return $info;
  }

  private function getInfoBasic(string $value): array{
    $value = base64_decode($value);
    $ar = explode(':', $value);
    return [
      'AUTH_USER' => $ar[0],
      'AUTH_PWD' => $ar[1],
    ];
  }

  private function getInfoBearer(string $value): array{
    $return = [
      'isJWT' => JWT::isJWT($value),
    ];
    if(JWT::isJWT($value)){
      return $return;
    }
    $dec = null;
    $JWKSetClass = new ctrlJWK();
    $JWKSetPrivate =   $JWKSetClass->getPrivateKeys();
    foreach($JWKSetPrivate['keys'] as $privateKey){
      if( $privateKey['use'] != 'enc')
        continue;
      $key = JWK::createFormValue($privateKey);
      try{
        $dec = json_decode(JWE::decrypt($value, $key));
        break;
      }catch(\Exception){
      }
    }
    if($dec){
      $return['isJWE'] = true;
      $return['JWEPayload'] = $dec;
      $return['AUTH_USER'] = $dec->client_id;
      $return['AUTH_PWD'] = $dec->client_secret;
      $return['SHARED_KEY'] = isset($dec->shared_key) ? $dec->shared_key : null;
    }
    return $return;
  }

  public static function getAuth(): ?array{
    if(self::$class === null)
      self::$class = new security();
    if(!self::$class->auth)
      return null;
    $authA = explode(' ', self::$class->auth);
    if(!isset($authA[1]))
      return null;
    $info = self::$class->getInfo($authA);
    return [
      'type' => $authA[0],
      'value' => $authA[1],
      'info' => $info,
    ];
  }
}
