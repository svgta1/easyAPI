<?php
namespace Svgta\docAPI;
use OpenApi\Annotations as OA;
use Svgta\EasyApi\utils\conf;

$ar = [
  'api_name' => "Svgta Easy API - Authentication",
  'doc_version' => "v1",
  'api_path' => conf::getConfKey('CONF_GENERAL', 'basePath'),
  'api_ver' => 'v1',
  'dir' => 'v1r0/',
];

define("AUT_API_NAME", $ar['api_name']);
define("AUT_DOC_VERSION", $ar['doc_version']);
define("AUT_API_PATH", $ar['api_path'] . '/' . $ar['api_ver']);
define("AUT_API_VER", $ar['api_ver']);
define("AUT_API_DIR", $ar['dir']);

/**
 * @OA\Info(
 *  description="API to authenticate users.<br /> The scope 'authorization' is needed. Users without this scope are rejected. The scopes can been managed by an administrator in the Administration API.",
 *  title=AUT_API_NAME,
 *  version=AUT_DOC_VERSION
 * )
 *
 * @OA\Server(url=AUT_API_PATH)
 *
 * @OA\SecurityScheme(
 *     securityScheme="basicAuth",
 *     type="http",
 *     scheme="basic"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="JWE_bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Send client_id and client_secret in json encrypted in JWE"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="JWT_bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Use the JWT token provided after the basic auth or the access_token auth"
 * )
 *
 * OA\SecurityScheme(
 *     securityScheme="Access_token",
 *     type="openIdConnect",
 *     openIdConnectUrl="https://accounts.google.com/.well-known/openid-configuration",
 *     description="Use the access_token of the OAUTH2 provider"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="Access_token",
 *     type="oauth2",
 *     name="OAUTH2_GITHUB",
 *     @OA\Flow(
 *      flow="authorizationCode",
 *      authorizationUrl="https://github.com/login/oauth/authorize",
 *      tokenUrl="https://github.com/login/oauth/access_token",
 *      scopes={
 *        "user:read": "user info",
 *      }
 *     )
 * )
 *
 * @OA\Tag(
 *    name="Authorization",
 *    description="Authentication of users"
 * )
 *
 * @OA\Schema(
 *   schema="authDefaultKo",
 *   description="Bad request",
 *   @OA\Property(type="string", property="msg", description="error message")
 * )
 */

class apiAuth{}
