<?php
namespace Svgta\EasyApi\controller\v1r0;
use Svgta\EasyApi\utils;
use Svgta\EasyApi\backend\Exception as backendException;
use Svgta\EasyApi\utils\security;
use Svgta\EasyApi\controller\apictrlAbstract;

class apictrlAdmin extends apictrlAbstract{

  public function __construct($backend = null, $request = [], ?string $scope = null){
    parent::__construct($backend, $request, $scope);
    $admRessource = $this->loadBackend('admin');
    try{
      $admRessource->updateLastAccess($this->payload['aud']);
    }catch(backendException $e){
      utils\httpResponse::error403('Not authorized to access this ressource - not an admin user.');
    }
  }

  /**
  * @OA\Get(
  *   path="/admin/list",
  *   description="List of admins",
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
  *     description="List of admins",
  *     @OA\JsonContent(
  *       type="object",
  *       @OA\Property(
  *         type="array",
  *         property="list",
  *         @OA\Items(
  *           @OA\Property(
  *             type="string",
  *             property="admin_id",
  *           ),
  *           @OA\Property(
  *             type="string",
  *             property="given_name",
  *           ),
  *           @OA\Property(
  *             type="string",
  *             property="family_name",
  *           ),
  *           @OA\Property(
  *             type="string",
  *             property="email",
  *           ),
  *         ),
  *       ),
  *       @OA\Property(
  *         type="integer",
  *         property="global",
  *         description="Number of users",
  *       ),
  *       @OA\Property(
  *         type="integer",
  *         property="page",
  *         description="Actual page",
  *       ),
  *       @OA\Property(
  *         type="integer",
  *         property="limitPerPage",
  *         description="Number of users per page",
  *       ),
  *       @OA\Property(
  *         type="integer",
  *         property="count",
  *         description="Number of users for this page",
  *       ),
  *     ),
  *   ),
  *   @OA\Response(response="403", description="Not authorized", @OA\JsonContent(ref="#/components/schemas/authDefaultKo")),
  * )
  */
  public function getList(){
    $ressource = $this->loadBackend('admin');
    $page = isset($this->reqBody['page']) ? $this->reqBody['page'] : null;
    $limit = isset($this->reqBody['limit']) ? $this->reqBody['limit'] : null;
    $res = $ressource->list($limit, $page);
    utils\httpResponse::code200($res);
  }

  /**
  * @OA\Get(
  *   path="/admin",
  *   description="Connected admin informations",
  *   security={{"bearerAuth": {}}},
  *   tags={"Admin"},
  *   @OA\Response(
  *     response="200",
  *     description="Admin informations",
  *     @OA\JsonContent(
  *       type="object",
  *       @OA\Property(
  *         type="string",
  *         property="admin_id",
  *         description="Id admin",
  *       ),
  *       @OA\Property(
  *         type="string",
  *         property="email",
  *         description="Email of the admin",
  *       ),
  *       @OA\Property(
  *         type="string",
  *         property="given_name",
  *         description="given_name",
  *       ),
  *       @OA\Property(
  *         type="string",
  *         property="family_name",
  *         description="family_name",
  *       ),
  *       @OA\Property(
  *         type="object",
  *         property="time",
  *           @OA\Property(
  *             type="integer",
  *             property="last_access",
  *             description="last access of the user",
  *           ),
  *           @OA\Property(
  *             type="integer",
  *             property="last_auth",
  *             description="last authentication time",
  *           ),
  *           @OA\Property(
  *             type="integer",
  *             property="last_update",
  *             description="last update time of the admin",
  *           ),
  *           @OA\Property(
  *             type="integer",
  *             property="create",
  *             description="Create time of the user",
  *           ),
  *       ),
  *       @OA\Property(
  *         type="object",
  *         property="date",
  *           @OA\Property(
  *             type="string",
  *             property="last_access",
  *             description="last access of the user",
  *           ),
  *           @OA\Property(
  *             type="string",
  *             property="last_auth",
  *             description="last authentication date",
  *           ),
  *           @OA\Property(
  *             type="string",
  *             property="last_update",
  *             description="last update date of the admin",
  *           ),
  *           @OA\Property(
  *             type="string",
  *             property="create",
  *             description="Create date of the user",
  *           ),
  *       ),
  *       @OA\Property(
  *         type="string",
  *         property="scope",
  *         description="List of the admin scopes",
  *       ),
  *     ),
  *   ),
  *   @OA\Response(response="403", description="Not authorized", @OA\JsonContent(ref="#/components/schemas/authDefaultKo")),
  * )
  */
  public function get(){
    $ressource = $this->loadBackend('admin');
    $res = $ressource->get($this->payload['aud']);
    $ret = [
      'admin_id' => $res['admin_info']['admin_id'],
      'email' => $res['admin_info']['email'],
      'given_name' => $res['admin_info']['given_name'],
      'family_name' => $res['admin_info']['family_name'],
      'time' => [
        'last_access' => $res['admin_info']['lastAccessTime'],
        'last_auth' => $res['auth_info']['lastAuthTime'],
        'last_update' => $res['admin_info']['updateTime'],
        'create' => $res['admin_info']['createTime'],
      ],
      'date' => [
        'last_access' => utils\utils::toDate($res['admin_info']['lastAccessTime']),
        'last_auth' => utils\utils::toDate($res['auth_info']['lastAuthTime']),
        'last_update' => utils\utils::toDate($res['admin_info']['updateTime']),
        'create' => utils\utils::toDate($res['admin_info']['createTime']),
      ],
      'scope' => $res['auth_info']['scope'],
    ];
    utils\httpResponse::code200($ret);
  }

  /**
  * @OA\Put(
  *   path="/admin",
  *   description="Update connected admin informations",
  *   security={{"bearerAuth": {}}},
  *   tags={"Admin"},
  *   @OA\Response(
  *     response="200",
  *     description="Update done",
  *     @OA\JsonContent(
  *       type="object",
  *       description="JSON Format",
  *       @OA\Property(
  *         type="string",
  *         property="admin_id",
  *         description="The admin id",
  *         default="admin_id",
  *       ),
  *       @OA\Property(
  *         type="object",
  *         property="change",
  *         description="What has been changed",
  *         default={},
  *       ),
  *       @OA\Property(
  *         type="string",
  *         property="new_admin_secret",
  *         description="The new secret. Only if new secret is required",
  *         default="admin_secret",
  *       ),
  *       @OA\Property(
  *         type="string",
  *         property="info",
  *         description="Message. Only if new secret is required",
  *         default="You are now logged out. You have to authenticate with your new admin_secret",
  *       ),
  *     ),
  *   ),
  *   @OA\Response(response="403", description="Not authorized", @OA\JsonContent(ref="#/components/schemas/authDefaultKo")),
  *   @OA\Response(response="406", description="Not Acceptable", @OA\JsonContent(ref="#/components/schemas/authDefaultKo")),
  *   @OA\RequestBody(
  *     description="Authentication of the connected admin",
  *     required=true,
  *     @OA\JsonContent(
  *       type="object",
  *       description="JSON Format",
  *       required={"admin_secret"},
  *       @OA\Property(
  *         type="string",
  *         property="admin_secret",
  *         description="The admin secret. Required !",
  *         default="admin_secret",
  *       ),
  *       @OA\Property(
  *         type="boolean",
  *         property="genNewSecret",
  *         description="Generate a new admin_secret",
  *         default=false,
  *         nullable=true,
  *       ),
  *       @OA\Property(
  *         type="string",
  *         property="new_name",
  *         description="Change the given_name",
  *         default="given_name",
  *         nullable=true,
  *       ),
  *       @OA\Property(
  *         type="string",
  *         property="new_family_name",
  *         description="Change the family_name",
  *         default="family_name",
  *         nullable=true,
  *       ),
  *       @OA\Property(
  *         type="string",
  *         property="new_email",
  *         description="Change the email",
  *         default="email",
  *         nullable=true,
  *       ),
  *     ),
  *   ),
  * )
  */
  public function put(){
    $_req = $this->reqBody;
    $req = [];
    foreach($_req as $k => $v){
      switch($k){
        case 'admin_secret':
        case 'genNewSecret':
        case 'new_name':
        case 'new_family_name':
        case 'new_email':
          $req[$k] = $v;
          break;
      }
    }
    $user_id = $this->payload['aud'];
    if(!isset($req['admin_secret']))
      utils\httpResponse::error403("admin_secret required");
    $authRess = $this->loadBackend('auth');
    $auth = $authRess->get($user_id);
    if(!(\password_verify($req['admin_secret'], $auth->user_secret)))
      utils\httpResponse::error403("Bad authentification");

    $updateAdmin = false;
    $newValue = [];
    if(isset($req['new_name']) AND $req['new_name']){
      $updateAdmin = true;
      $newValue['given_name'] = $req['new_name'];
    }

    if(isset($req['new_family_name']) AND $req['new_family_name']){
      $updateAdmin = true;
      $newValue['family_name'] = $req['new_family_name'];
    }

    if(isset($req['new_email']) AND $req['new_email']){
      $updateAdmin = true;
      $newValue['email'] = $req['new_email'];
    }

    $updateSecret = false;
    if(isset($req['genNewSecret']) AND $req['genNewSecret']){
      $admin_secret = utils\utils::genPassword(256);
      $updateAdmin = true;
      $newValue['admin_secret'] = $admin_secret;
      $updateSecret = true;
    }

    $ressource = $this->loadBackend('admin');
    try{
      $old = $ressource->get($user_id, $authRess);
      $hasChange = false;
      foreach($newValue as $k=>$v){
        if(isset($old['admin_info']->{$k}) AND ($old['admin_info']->{$k} != $v)){
          $hasChange = true;
          break;
        }
        if($k == "admin_secret"){
          $hasChange = true;
          break;
        }
      }
      if(!$hasChange)
        utils\httpResponse::code200([
          'admin_id' => $user_id,
          'change' => [],
        ]);

      $res = $ressource->update($user_id, $newValue, $authRess);
      $new = $ressource->get($user_id, $authRess);
    }catch(backendException $e){
      utils\httpResponse::error406($e->getMessage());
    }
    $change = [];
    foreach($res['admin_info'] as $k => $v){
      if($old['admin_info']->{$k} != $new['admin_info']->{$k})
        $change[$k] = [
          'old_value' => $old['admin_info']->{$k},
          'new_value' => $new['admin_info']->{$k},
        ];
    }

    $ret = [
      'admin_id' => $user_id,
      'change' => $change,
    ];
    if($updateSecret){
      $ret['new_admin_secret'] = $admin_secret;
      $ret['info'] = 'You are now logged out. You have to authenticate with your new admin_secret';
      $sessRess = $this->loadBackend('session');
      $session = $sessRess->delete($this->payload['jti']);
    }
    utils\httpResponse::code200($ret);
  }

  /**
  * @OA\Post(
  *   path="/admin",
  *   description="Create new admin",
  *   security={{"bearerAuth": {}}},
  *   tags={"Admin"},
  *   @OA\Response(
  *     response="200",
  *     description="The admin is created",
  *     @OA\JsonContent(ref="#/components/schemas/adminAuth"),
  *   ),
  *   @OA\Response(response="403", description="Not authorized", @OA\JsonContent(ref="#/components/schemas/authDefaultKo")),
  *   @OA\Parameter(
  *     in="query",
  *     name="email",
  *     required=true,
  *     description="New admin email",
  *     @OA\Schema(
  *       type="string",
  *       format="email",
  *     ),
  *   ),
  *   @OA\Parameter(
  *     in="query",
  *     name="given_name",
  *     required=true,
  *     description="Given Name",
  *     @OA\Schema(
  *       type="string",
  *     ),
  *   ),
  *   @OA\Parameter(
  *     in="query",
  *     name="family_name",
  *     required=true,
  *     description="Family name",
  *     @OA\Schema(
  *       type="string",
  *     ),
  *   ),
  *   @OA\Response(
  *     response="404",
  *     @OA\JsonContent(ref="#/components/schemas/authDefaultKo"),
  *     description="Admin not found"
  *   ),
  * )
  */
  public function post(){
    $ressource = $this->loadBackend('admin');
    $req = $this->reqBody;
    $admin_secret = utils\utils::genPassword(256);
    $req['admin_secret'] = $admin_secret;
    $default_scopes = utils\conf::getConfKey('CONF_GENERAL', 'scope_default');
    $req['scope'] = $default_scopes['admin'];
    try{
      $res = $ressource->insert($req, $this->loadBackend('auth'));
    }catch(backendException $e){
      utils\httpResponse::error406($e->getMessage());
    }

    if(!password_verify($admin_secret, $res['admin_secret'])){
      $ressource->delete($res['data']['admin_secret']);
      utils\httpsResponse::error406('Admin not created. Error system on password hash');
    }

    $ret = [
      'admin_id' => $res['data']['admin_id'],
      'admin_secret' => $admin_secret,
      'scope' => $default_scopes['admin'],
    ];
    utils\httpResponse::code200($ret);
  }

  /**
  * @OA\Put(
  *   path="/admin/{admin_id}",
  *   description="Update the admin request",
  *   security={{"bearerAuth": {}}},
  *   tags={"Admin"},
  *   @OA\Response(
  *     response="200",
  *     description="Update ok",
  *     @OA\JsonContent(
  *       description="JSON Format",
  *       type="object",
  *       @OA\Property(
  *         type="string",
  *         property="admin_id_operator",
  *         description="The admin id",
  *         default="admin_id",
  *         format="uuid",
  *       ),
  *       @OA\Property(
  *         type="string",
  *         property="admin_id",
  *         description="The admin id",
  *         default="admin_id",
  *         format="uuid",
  *       ),
  *       @OA\Property(
  *         type="object",
  *         property="change",
  *         description="What has been changed",
  *         default={},
  *       ),
  *       @OA\Property(
  *         type="string",
  *         property="new_admin_secret",
  *         description="The new secret. Only if new secret is required",
  *         default="admin_secret",
  *       ),
  *       @OA\Property(
  *         type="string",
  *         property="new_scope",
  *         description="The new scope list",
  *         default="scope1 scope2",
  *       ),
  *       @OA\Property(
  *         type="string",
  *         property="info",
  *         description="Message. Only if new secret is required or scope updated",
  *         default="The administator is logged out. He has to authentificate to take the new changes",
  *       ),
  *     ),
  *   ),
  *   @OA\Response(response="403", description="Not authorized", @OA\JsonContent(ref="#/components/schemas/authDefaultKo")),
  *   @OA\Response(response="406", description="Not Acceptable", @OA\JsonContent(ref="#/components/schemas/authDefaultKo")),
  *   @OA\RequestBody(
  *     description="Authentication of the connected admin",
  *     required=true,
  *     @OA\JsonContent(
  *       type="object",
  *       description="JSON Format",
  *       required={"admin_secret"},
  *       @OA\Property(
  *         type="string",
  *         property="admin_secret",
  *         description="The actual admin secret. Required !",
  *         default="actual_admin_secret",
  *       ),
  *       @OA\Property(
  *         type="boolean",
  *         property="genNewSecret",
  *         description="Generate a new admin_secret",
  *         default=false,
  *         nullable=true,
  *       ),
  *       @OA\Property(
  *         type="string",
  *         property="new_name",
  *         description="Change the given_name",
  *         default="given_name",
  *         nullable=true,
  *       ),
  *       @OA\Property(
  *         type="string",
  *         property="new_family_name",
  *         description="Change the family_name",
  *         default="family_name",
  *         nullable=true,
  *       ),
  *       @OA\Property(
  *         type="string",
  *         property="new_email",
  *         description="Change the email",
  *         default="email",
  *         nullable=true,
  *       ),
  *       @OA\Property(
  *         type="string",
  *         property="add_scope",
  *         description="Add new scope",
  *         default="new_scope1 new_scope2",
  *         nullable=true,
  *       ),
  *       @OA\Property(
  *         type="string",
  *         property="rm_scope",
  *         description="Remove scope",
  *         default="old_scope1 old_scope2",
  *         nullable=true,
  *       ),
  *     ),
  *   ),
  *   @OA\Parameter(
  *     in="path",
  *     name="admin_id",
  *     required=true,
  *     description="Id de l'admin",
  *     @OA\Schema(
  *       type="string",
  *       format="uuid",
  *     ),
  *   ),
  *   @OA\Response(
  *     response="404",
  *     @OA\JsonContent(ref="#/components/schemas/authDefaultKo"),
  *     description="Client_id non trouvé"
  *   ),
  * )
  */
  public function putAdmin($admin_id){
    $_req = $this->reqBody;
    $req = [];
    foreach($_req as $k => $v){
      switch($k){
        case 'admin_secret':
        case 'genNewSecret':
        case 'new_name':
        case 'new_family_name':
        case 'new_email':
        case 'add_scope':
        case 'rm_scope':
          $req[$k] = $v;
          break;
      }
    }
    $user_id = $this->payload['aud'];
    if(!isset($req['admin_secret']))
      utils\httpResponse::error403("admin_secret required");
    $authRess = $this->loadBackend('auth');
    try{
      $auth = $authRess->get($user_id);
    }catch(backendException $e){
      utils\httpResponse::error404($e->getMessage());
    }
    if(!(\password_verify($req['admin_secret'], $auth->user_secret)))
      utils\httpResponse::error403("Bad authentification");

    $updateAdmin = false;
    $newValue = [];
    if(isset($req['new_name']) AND $req['new_name']){
      $updateAdmin = true;
      $newValue['given_name'] = $req['new_name'];
    }

    if(isset($req['new_family_name']) AND $req['new_family_name']){
      $updateAdmin = true;
      $newValue['family_name'] = $req['new_family_name'];
    }

    if(isset($req['new_email']) AND $req['new_email']){
      $updateAdmin = true;
      $newValue['email'] = $req['new_email'];
    }

    $updateSecret = false;
    if(isset($req['genNewSecret']) AND $req['genNewSecret']){
      $admin_secret = utils\utils::genPassword(256);
      $updateAdmin = true;
      $newValue['admin_secret'] = $admin_secret;
      $updateSecret = true;
    }

    $updateScope = false;
    try{
      $auth = $authRess->get($admin_id);
    }catch(backendException $e){
      utils\httpResponse::error404($e->getMessage());
    }
    $scope = $auth->scope;
    $scopeAr = explode(' ', $scope);
    if(isset($req['add_scope']) AND $req['add_scope']){
      $addScopeAr = explode(" ", $req['add_scope']);
      foreach($addScopeAr as $v){
        if(strlen($v) < 3)
          continue;
        if(!in_array($v, $scopeAr)){
          array_push($scopeAr, $v);
          $updateScope = true;
        }
      }
    }
    if(isset($req['rm_scope']) AND $req['rm_scope']){
      $rmScopeAr = explode(" ", $req['rm_scope']);
      foreach($scopeAr as $k => $v){
        if(in_array($v, $rmScopeAr)){
          $updateScope = true;
          unset($scopeAr[$k]);
        }
      }
    }

    if($updateScope){
      if($scope == implode(' ', $scopeAr))
        $updateScope = false;
      else
        $newValue['scope'] = implode(' ', $scopeAr);
    }

    $ressource = $this->loadBackend('admin');
    try{
      $old = $ressource->get($admin_id, $authRess);
      $hasChange = false;
      if($updateSecret OR $updateScope)
        $hasChange = true;
      if(!$hasChange)
      foreach($newValue as $k=>$v){
        if(isset($old['admin_info']->{$k}) AND ($old['admin_info']->{$k} != $v)){
          $hasChange = true;
          break;
        }
      }
      if(!$hasChange)
        utils\httpResponse::code200([
          'admin_id' => $user_id,
          'change' => [],
        ]);

      $res = $ressource->update($admin_id, $newValue, $authRess);
      $new = $ressource->get($admin_id, $authRess);
    }catch(backendException $e){
      utils\httpResponse::error406($e->getMessage());
    }
    $change = [];
    foreach($res['admin_info'] as $k => $v){
      if($old['admin_info']->{$k} != $new['admin_info']->{$k})
        $change[$k] = [
          'old_value' => $old['admin_info']->{$k},
          'new_value' => $new['admin_info']->{$k},
        ];
    }

    $ret = [
      'admin_id_operator' => $user_id,
      'admin_id' => $admin_id,
      'change' => $change,
    ];
    if($updateScope OR $updateSecret){
      if($updateSecret){
        $ret['new_admin_secret'] = $admin_secret;
      }
      if($updateScope){
        $ret['new_scope'] = $newValue['scope'];
      }
      $ret['info'] = 'The administator is logged out. He has to authentificate to take the new changes';
      $sessRess = $this->loadBackend('session');
      $session = $sessRess->deleteMulti(['client_id' => $this->payload['jti']]);
    }
    utils\httpResponse::code200($ret);
  }

  /**
  * @OA\Delete(
  *   path="/admin/{admin_id}",
  *   description="Delete an admin",
  *   security={{"bearerAuth": {}}},
  *   tags={"Admin"},
  *   @OA\Response(
  *     response="204",
  *     description="Admin has been deleted",
  *   ),
  *   @OA\Response(response="403", description="Not authorized", @OA\JsonContent(ref="#/components/schemas/authDefaultKo")),
  *   @OA\Parameter(
  *     in="path",
  *     name="admin_id",
  *     required=true,
  *     description="Admin ID",
  *     @OA\Schema(
  *       type="string",
  *       format="uuid",
  *     ),
  *   ),
  *   @OA\Response(
  *     response="404",
  *     @OA\JsonContent(ref="#/components/schemas/authDefaultKo"),
  *     description="Client_id non trouvé"
  *   ),
  * )
  */
  public function deleteAdmin($admin_id){
    try{
      $authRess = $this->loadBackend('auth');
      $admRess = $this->loadBackend('admin');
      $admRess->delete($admin_id, $authRess);
    }catch(backendException $e){
      utils\httpResponse::error404($admin_id . ' not found' . $e->getMessage());
    }
    utils\httpResponse::code204();
  }


}
