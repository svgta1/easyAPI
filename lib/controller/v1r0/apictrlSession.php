<?php
namespace Svgta\EasyApi\controller\v1r0;
use Svgta\EasyApi\utils;
use Svgta\EasyApi\backend\Exception;
use Svgta\EasyApi\controller\apictrlAbstract;

class apictrlSession extends apictrlAbstract{
  /**
  * @OA\Get(
  *   path="/admin/sessions",
  *   description="Get sessions",
  *   security={{"bearerAuth": {}}},
  *   tags={"Admin"},
  *   @OA\Parameter(
  *     name="page",
  *     @OA\Schema(
  *       type="integer",
  *       default="1",
  *     ),
  *     in="query",
  *  ),
  *   @OA\Parameter(
  *     name="limit",
  *     @OA\Schema(
  *       type="integer",
  *       default="10",
  *     ),
  *     in="query",
  *   ),
  *   @OA\Response(
  *     response="200",
  *     description="Get logs",
  *     @OA\JsonContent(
  *       type="object",
  *       @OA\Property(
  *         type="array",
  *         property="list",
  *         @OA\Items(
  *         ),
  *       ),
  *       @OA\Property(
  *         type="integer",
  *         property="global",
  *         description="Number of sessions",
  *       ),
  *       @OA\Property(
  *         type="integer",
  *         property="page",
  *         description="Actual page",
  *       ),
  *       @OA\Property(
  *         type="integer",
  *         property="limitPerPage",
  *         description="Number of sessions per page",
  *       ),
  *       @OA\Property(
  *         type="integer",
  *         property="count",
  *         description="Number of sessions for this page",
  *       ),
  *     ),
  *   ),
  *   @OA\Response(response="403", description="Not authorized", @OA\JsonContent(ref="#/components/schemas/authDefaultKo")),
  * )
  */
  public function getList(){
    $ressource = $this->loadBackend('session');
    $page = isset($this->reqBody['page']) ? $this->reqBody['page'] : null;
    $limit = isset($this->reqBody['limit']) ? $this->reqBody['limit'] : null;
    $res = $ressource->list($limit, $page);
    utils\httpResponse::code200($res);
  }
}
