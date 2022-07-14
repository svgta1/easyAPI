<?php
namespace Svgta\EasyApi\controller\v1r0;
use Svgta\EasyApi\utils;
use Svgta\EasyApi\backend\Exception;
use Svgta\EasyApi\controller\apictrlAbstract;

class apictrlLog extends apictrlAbstract{
  /**
  * @OA\Get(
  *   path="/admin/logs",
  *   description="Get logs",
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
  *         description="Number of logs",
  *       ),
  *       @OA\Property(
  *         type="integer",
  *         property="page",
  *         description="Actual page",
  *       ),
  *       @OA\Property(
  *         type="integer",
  *         property="limitPerPage",
  *         description="Number of logs per page",
  *       ),
  *       @OA\Property(
  *         type="integer",
  *         property="count",
  *         description="Number of logs for this page",
  *       ),
  *     ),
  *   ),
  *   @OA\Response(response="403", description="Not authorized", @OA\JsonContent(ref="#/components/schemas/authDefaultKo")),
  * )
  */
  public function getList(){
    $ressource = $this->loadBackend('log');
    $page = isset($this->reqBody['page']) ? $this->reqBody['page'] : null;
    $limit = isset($this->reqBody['limit']) ? $this->reqBody['limit'] : null;
    $res = $ressource->list($limit, $page);
    $uri = utils\utils::getReqUri();
    foreach($res['list'] as $k => $v){
      if(($v->request->req_uri) == $uri)
        $v->response = 'Self URI : data offusced to avoid loops';
      $res['list'][$k] = $v;
    }
    $saveToLog = false;
    utils\httpResponse::code200($res, $saveToLog);
  }
}
