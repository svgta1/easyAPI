<?php
namespace Svgta\EasyApi\controller\v1r0;
use Svgta\EasyApi\utils;
use Svgta\EasyApi\backend\Exception;
use Svgta\EasyApi\utils\JWS;
use Svgta\EasyApi\utils\JWK;
use Svgta\EasyApi\utils\JWE;
use Svgta\EasyApi\utils\security;
use Svgta\EasyApi\controller\apictrlAbstract;

class apictrlAuth extends apictrlAbstract{
  /**
  * @OA\Get(
  *   path="/auth/verify",
  *   operationId="AuthVerifyJWT",
  *   description="Check the validity of the JWT",
  *   security={{"JWT_bearerAuth": {}}},
  *   tags={"Authorization"},
  *   @OA\Response(
  *     response="200",
  *     description="JWT ok",
  *     @OA\JsonContent(
  *       type="object",
  *       description="JWT structure",
  *       @OA\property(
  *         property="active",
  *         type="boolean",
  *         description="JWT actif",
  *         default=true,
  *       ),
  *       @OA\property(
  *         property="scope",
  *         type="string",
  *         description="list of scopes",
  *         default="authorization otherscope onemore",
  *       ),
  *       @OA\property(
  *         property="client_id",
  *         type="string",
  *         description="The client ID",
  *       ),
  *       @OA\property(
  *         property="exp_in",
  *         type="integer",
  *         description="Time in second before expiration of the JWT",
  *       ),
  *     ),
  *   ),
  *   @OA\Response(response="403", description="Not authorized", @OA\JsonContent(ref="#/components/schemas/authDefaultKo"))
  * )
  */
  public function verify(){
    utils\httpResponse::code200([
      'active' => true,
      'scope' => $this->payload['scope'],
      'client_id' => $this->payload['aud'],
      'iss' => $this->payload['iss'],
      'exp_in' => (int)$this->payload['exp'] - time(),
    ]);
  }

  private function oauth(){
    $authRess = $this->loadBackend('auth');
    $ar = security::verifyAuthOauth();
    $found = false;
    $authBase = null;
    if(isset($this->reqBody['backend_name'])){
      $backend_name = $this->reqBody['backend_name'];
      if(!isset($ar['search'][$backend_name]))
        utils\httpResponse::error404('The backend name is unknown');
      $_ar = $ar['search'][$backend_name];
      $ar['search'] = [];
      $ar['search'][$backend_name] = $_ar;
    }
    $exception = false;
    foreach($ar['search'] as $vAr){
      $backendString = $vAr['class'];
      $backend = new $backendString();
      try{
        if(isset($this->reqBody['target_id'])){
          $searchTarget = [
            $vAr['targetId'] => $this->reqBody['target_id'],
          ];
          $vAr['search'] = array_merge($vAr['search'], $searchTarget);
        }
        $res = $backend->find($vAr['search'], $authRess);
        $found = true;
        break;
      }catch(Exception $e){
        $exception = $e;
      }
    }
    if(!$found)
      utils\httpResponse::error401($exception->getMessage());

    security::verifyMultiSession($res['auth_info']['scope'], $res['auth_info']['user_id']);
    if(!$scope = security::is_scopeAuth('authorization', $res['auth_info']['scope']))
      utils\httpResponse::error403("Not authorized to access this ressource : account blocked");
    $payload = $this->genPayload($res['auth_info']['user_id'], $res['auth_info']['scope']);
    $sign = $this->sigPayload($payload);
    $this->setSession($res['auth_info']['user_id'], null, $payload['jti']);
    $authRess->updateLastAccess($res['auth_info']['user_id'], 'lastAuthTime');
    utils\httpResponse::code200([
      'JWT' => $sign,
    ]);
  }

  /**
  * @OA\Post(
  *   path="/auth/login",
  *   operationId="AuthBasic",
  *   security={{"basicAuth": {}}, {"JWE_bearerAuth": {}}, {"Access_token": {}}},
  *   tags={"Authorization"},
  *   description="Authentification of the user or client. Different mode :
  - Basic : login and password.
  - Oauth/OIDC : with a provider.
  - JWE : admin and password in a JWE format encrypted with the RSA public key with type 'enc' of the JWKs endpoint.

  Optionnal for JWE authentication : shared_key - to send the responses in JWE format encrypted with an octet key generated with the shared key.
  ",
  *   @OA\RequestBody(
  *     description="Option : to force to connect to a specific backend",
  *     required=false,
  *     @OA\JsonContent(
  *       type="object",
  *       description="JSON Format",
  *       @OA\Property(
  *         type="string",
  *         property="backend_name",
  *         description="For Oauth only : Force to search the user only on one backend.",
  *         default="admin",
  *         nullable=true,
  *       ),
  *       @OA\Property(
  *         type="string",
  *         property="target_id",
  *         description="For Oauth only : serach for a specific user_id. Usefull if multi clients with the same email",
  *         default="",
  *         nullable=true,
  *       ),
  *     ),
  *   ),
  *   @OA\Response(
  *     response="200",
  *     @OA\JsonContent(
  *       type="object",
  *       @OA\property(
  *         property="JWT",
  *         type="string",
  *         description="The JWT token",
  *         default="eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6ImMyYzM1NzAyLWFjMGEtNGYwZS05ZGEwLWIwMjQ1OTdmNGYxMiJ9.eyJpc3MiOiJNeSBUT1RQIEFQSSIsInN1YiI6IjNiODRmYWEwLTVhYjYtNDk4OC05NmEyLWY2M2ZiMzE2ZGU2MiIsImF1ZCI6IjMzYWFmMDEwLWNhNzAtNDdmZC04NzdjLTAwZTA5YmM2MTY5NyIsInNjb3BlIjoiYWRtaW5fY2xpZW50cyBhZG1pbl9yZWFkIG11bHRpX3Nlc3Npb24gYXV0aG9yaXphdGlvbiIsImV4cCI6MTY1NzM2NjgyMSwiaWF0IjoxNjU3MzYzMjIxLCJuYmYiOjE2NTczNjMyMjF9.kYtFQtUhMTSJ7rHAV8QcrJoy5yjAFaULn0FLwq0c_j82c1eeuED0m723ldVvr1-IzIB1STKTALhOK8m5l1cMrH1X2LFP8gevLZlxGlkb4NF158a3RJliJDOZKd-xOPEFVic0kjeLh7NcWMjSCMGTEGi3TvkR-nSKSsCbDYth4FGmmUVjaQFRtieTspEOJdFvbT63YWN_-vew1rqyvo6STvVz5I7tz-iHyaJuz61EsxdnaXPbf8lfGbnm8CqFdp3EjAmmupF02lHl7Nk40SEoJM0bByMUAjyWmKQ6ZUyfGLkjJ8n6iDQ2rHsnoXVW9OUr9ibxX641AaLuyO6cB-PHqQ",
  *       ),
  *     ),
  *     description="Return the JWT token",
  *   ),
  *   @OA\Response(
  *     response="403",
  *     @OA\JsonContent(ref="#/components/schemas/authDefaultKo"),
  *     description="Not authorized"
  *   )
  * )
  */
  public function login(){
    $auth = security::getAuth();
    if(($auth['type'] == 'Bearer') AND isset($auth['info']['isJWE']) AND $auth['info']['isJWE'])
      $this->authJWE();
    if(($auth['type'] == 'Bearer'))
      $this->oauth();
    $ressource = $this->loadBackend('auth');
    $user = security::verifyAuthBasic($ressource);
    if(!$scope = security::is_scopeAuth('authorization', $user->scope))
      utils\httpResponse::error403("Not authorized to access this ressource : account blocked");
    $payload = $this->genPayload(security::getAuth()["info"]["AUTH_USER"], $user->scope);
    $sign = $this->sigPayload($payload);
    $this->setSession(security::getAuth()["info"]["AUTH_USER"], null, $payload['jti']);
    $ressource->updateLastAccess(security::getAuth()["info"]["AUTH_USER"], 'lastAuthTime');
    utils\httpResponse::code200([
      'JWT' => $sign,
    ]);
  }

  /**
  * @OA\Delete(
  *   path="/auth/logout",
  *   operationId="AuthLogout",
  *   description="Logout bearer en utilisant le JWT de l'authentification",
  *   security={{"JWT_bearerAuth": {}}},
  *   tags={"Authorization"},
  *   @OA\Response(
  *     response="200",
  *     description="logout ok",
  *     @OA\JsonContent(
  *       type="object",
  *       @OA\property(
  *         property="msg",
  *         type="string",
  *         description="Logout success",
  *         default="You have been deconnected",
  *       ),
  *     ),
  *   ),
  *   @OA\Response(response="403", description="Not authorized", @OA\JsonContent(ref="#/components/schemas/authDefaultKo"))
  * )
  */
  public function logout(){
    $resSes = $this->loadBackend('session');
    $resSes->delete($this->payload['jti']);
    utils\httpResponse::code200('You have been deconnected');
  }

  /**
  * @OA\Get(
  *   path="/auth/jwks",
  *   operationId="AuthJwks",
  *   security={},
  *   description="Public keys to verify the JWK",
  *   tags={"Authorization"},
  *   @OA\Response(
  *     response="200",
  *     description="Key set",
  *     @OA\JsonContent(
  *       type="object",
  *       @OA\Property(
  *         property="keys",
  *         type="array",
  *         @OA\Items(
  *           @OA\Property(
  *             type="string",
  *             property="alg",
  *             example="RS256",
  *           ),
  *           @OA\Property(
  *             type="string",
  *             property="use",
  *             example="sig",
  *           ),
  *           @OA\Property(
  *             type="string",
  *             property="kid",
  *             example="kidtest",
  *           ),
  *           @OA\Property(
  *             type="string",
  *             property="n",
  *           ),
  *           @OA\Property(
  *             type="string",
  *             property="e",
  *             example="AQAB",
  *           ),
  *           @OA\Property(
  *             type="string",
  *             property="kty",
  *             example="RSA",
  *           ),
  *         ),
  *       ),
  *     ),
  *   ),
  * )
  */
  public function jwks(){
    $JwkRessource = $this->loadBackend('jwk');
    utils\httpResponse::code200($JwkRessource->getPublicKeySet());
  }

  /**
  * @OA\Get(
  *   path="/auth/backends",
  *   security={},
  *   operationId="AuthOauth2Backends",
  *   description="List of the backends",
  *   tags={"Authorization"},
  *   @OA\Response(
  *     response="200",
  *     description="Return the list of the backends",
  *     @OA\JsonContent(
  *       type="object",
  *       @OA\Property(
  *         property="backends",
  *         type="array",
  *         @OA\Items(
  *           @OA\Property(
  *             type="string",
  *             property="name",
  *             example="admin",
  *           ),
  *           @OA\Property(
  *             type="string",
  *             property="desc",
  *             example="admin user backend",
  *           ),
  *         ),
  *       ),
  *     ),
  *   ),
  * )
  */
  public function backends(){
    $backends = utils\conf::getConfKey('OAUTH_CONF', 'mappings');
    $ret = ['backends' => []];
    foreach($backends as $name => $ar){
      $ret['backends'][] = [
        'name' => $name,
        'desc' => $ar['description'],
      ];
    }
    utils\httpResponse::code200($ret);
  }

  private function authJWE(){
    $ressource = $this->loadBackend('auth');
    $user = security::verifyAuthJWE($ressource);
    if(!$scope = security::is_scopeAuth('authorization', $user->scope))
      utils\httpResponse::error403("Not authorized to access this ressource : account blocked");
    $payload = $this->genPayload(security::getAuth()["info"]["AUTH_USER"], $user->scope);
    $sign = $this->sigPayload($payload);
    $this->setSession(security::getAuth()["info"]["AUTH_USER"], security::getAuth()["info"]["SHARED_KEY"], $payload['jti']);
    $ressource->updateLastAccess(security::getAuth()["info"]["AUTH_USER"], 'lastAuthTime');
    if(security::getAuth()["info"]["SHARED_KEY"])
      utils\httpResponse::setEncKey(JWK::genEncOct(security::getAuth()["info"]["SHARED_KEY"]));
    utils\httpResponse::code200([
      'JWT' => $sign,
    ]);
  }

  private function setSession(string $client_id, string $secureKey = null, string $sessionId): void{
    if($secureKey)
      $secu = JWK::genEncOct($secureKey);
    else
      $secu = null;
    $resSes = $this->loadBackend('session');
    $resSes->insert([
      'token' => $sessionId,
      'enc_pwd' => $secu,
      'exp' => time() + utils\conf::getConfKey('CONF_GENERAL', 'token_lifetime'),
      'client_id' => $client_id,
      'client_ip' => utils\utils::getUserIp(),
      'client_ua' => utils\utils::getUA(),
    ]);
  }

  private function genPayload(string $userId, string $scope): array{
    $sesUID = utils\utils::genUUID();
    $payload = [
      'iss' => utils\conf::getConfKey('CONF_GENERAL', 'appName'),
      'jti' => $sesUID,
      'aud' => $userId,
      'scope' => $scope,
      'exp' => time() + utils\conf::getConfKey('CONF_GENERAL', 'token_lifetime'),
      'iat' => time(),
    ];

    return $payload;
  }

  private function sigPayload($payload): string{
    $JwkRessource = $this->loadBackend('jwk');
    $keySet = $JwkRessource->getPrivateKeySet();
    foreach($keySet["keys"] as $key){
      if($key["use"] === "sig"){
        $privateKey = $key;
        break;
      }
    }
    return JWS::signPayload($payload, $privateKey);
  }
}
