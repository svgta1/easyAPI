<?php
use Svgta\EasyApi\utils\conf;

require dirname(__FILE__, 1) . '/apiAdmin.php';
$adm_openapi = \OpenApi\Generator::scan([
  dirname(__FILE__, 1) . '/apiAdmin.php',
  dirname(__FILE__, 2) . '/lib/controller/'.ADM_API_DIR.'apictrlAdmin.php',
  dirname(__FILE__, 2) . '/lib/controller/'.ADM_API_DIR.'apictrlLog.php',
  dirname(__FILE__, 2) . '/lib/controller/'.ADM_API_DIR.'apictrlSession.php',
]);
$adminJson = $adm_openapi->toJson();

require dirname(__FILE__, 1) . '/apiAuth.php';
$auth_openapi = \OpenApi\Generator::scan([
  dirname(__FILE__, 1) . '/apiAuth.php',
  dirname(__FILE__, 2) . '/lib/controller/'.AUT_API_DIR.'apictrlAuth.php',
]);
$authJson = $auth_openapi->toJson();
$authApi = json_decode($authJson);
$secu = $authApi->components->securitySchemes;
$oauthConf = conf::getConf('OAUTH_CONF');
if(isset($secu->Access_token))
  unset($secu->Access_token);

if(isset($oauthConf['type']) AND in_array($oauthConf['type'], ['oauth2', 'openIdConnect'])){
  $secu->Access_token = new stdClass;
  $secu->Access_token->type = $oauthConf['type'];
  $secu->Access_token->description = "Provider : " . $oauthConf["provider_name"];
  if($oauthConf['type'] === 'oauth2'){
    $secu->Access_token->flows = new stdClass;
    $secu->Access_token->flows->{$oauthConf['access']['flow']} = new stdClass;
    $access = $secu->Access_token->flows->{$oauthConf['access']['flow']};
    $access->authorizationUrl = isset($oauthConf['access']['authorizationUrl']) ? $oauthConf['access']['authorizationUrl'] : null;
    $access->tokenUrl = isset($oauthConf['access']['tokenUrl']) ? $oauthConf['access']['tokenUrl'] : null;
    $scopesAr = isset($oauthConf['access']['scopes']) ? explode(' ', $oauthConf['access']['scopes']) : null;
    $scopes = [];
    foreach($scopesAr as $scope)
      $scopes[$scope] = $scope;
    $access->scopes = $scopes;
  }
  if($oauthConf['type'] === 'openIdConnect'){
    $secu->Access_token->openIdConnectUrl = $oauthConf['access']['url_config'];
  }
}

$apiDocs = [
  [
    "url" => 'authapi.json',
    "name" => AUT_API_NAME . ' ' . AUT_API_VER,
  ],
  [
    "url" => 'adminapi.json',
    "name" => ADM_API_NAME . ' ' . ADM_API_VER,
  ],
];

$jsonList = [
  [
    "filename" => 'authapi.json',
    "json" => json_encode($authApi),
  ],
  [
    "filename" => 'adminapi.json',
    "json" => $adminJson,
  ],
];
