<?php
use Svgta\EasyApi\utils\route;
//route::requestMethod(path, ctrlVersionDirectory, [class, class method, scope])

route::get("v1/auth/verify", "v1r0", ['apictrlAuth', 'verify', 'authorization']);
route::post("v1/auth/login", "v1r0", ['apictrlAuth', 'login']);
route::get("v1/auth/backends", "v1r0", ['apictrlAuth', 'backends']);
route::delete("v1/auth/logout", "v1r0", ['apictrlAuth', 'logout', 'authorization']);
route::get("v1/auth/jwks", "v1r0", ['apictrlAuth', 'jwks']);

route::get("v1/admin", "v1r0", ['apictrlAdmin', 'get', 'admin_read admin_super']);
route::get("v1/admin/list", "v1r0", ['apictrlAdmin', 'getList', 'admin_read admin_super']);
route::put("v1/admin", "v1r0", ['apictrlAdmin', 'put', 'admin_read admin_super']);
route::post("v1/admin", "v1r0", ['apictrlAdmin', 'post', 'admin_write admin_super']);
route::put("v1/admin/{admin_id}", "v1r0", ['apictrlAdmin', 'putAdmin', 'admin_super']);
route::delete("v1/admin/{admin_id}", "v1r0", ['apictrlAdmin', 'deleteAdmin', 'admin_super']);

route::get("v1/admin/logs", "v1r0", ['apictrlLog', 'getList', 'admin_super']);
route::get("v1/admin/sessions", "v1r0", ['apictrlSession', 'getList'], 'admin_super');
