<?php
namespace Svgta\docAPI;
use OpenApi\Annotations as OA;
use Svgta\EasyApi\utils\conf;

$ar = [
  'api_name' => 'Svgta Easy API - Administration',
  'doc_version' => 'v1',
  'api_path' => conf::getConfKey('CONF_GENERAL', 'basePath'),
  'api_ver' => 'v1',
  'dir' => 'v1r0/',
];

define("ADM_API_NAME", $ar['api_name']);
define("ADM_DOC_VERSION", $ar['doc_version']);
define("ADM_API_PATH", $ar['api_path'] . '/' . $ar['api_ver']);
define("ADM_API_VER", $ar['api_ver']);
define("ADM_API_DIR", $ar['dir']);

/**
 * @OA\Info(
 *  description="API to manage the administrators. <br /> To authenticate, use the JWT produce after using the Authenticate API",
 *  title=ADM_API_NAME,
 *  version=ADM_DOC_VERSION
 * )
 *
 * @OA\Server(url=ADM_API_PATH)
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Use the JWT token provided after the basic auth or the access_token auth"
 * )
 *
 * @OA\Tag(
 *    name="Admin",
 *    description="Administration"
 * )
 *
 * @OA\Schema(
 *   schema="adminAuth",
 *   title="Admin authentication",
 *   @OA\Property(
 *      type="string",
 *      property="admin_id",
 *      description="admin ID",
 *      format="uuid",
 *   ),
 *   @OA\Property(
 *      type="string",
 *      property="admin_secret",
 *      description="admin Secret",
 *      format="password",
 *   ),
 *   @OA\Property(
 *      type="string",
 *      property="scope",
 *      description="admin scopes",
 *      format="scope",
 *   ),
 * )
 *
 * @OA\Schema(
 *   schema="authDefaultKo",
 *   description="Bad request",
 *   @OA\Property(type="string", property="msg", description="error message")
 * )
 *
 */

class apiAdmin{}
