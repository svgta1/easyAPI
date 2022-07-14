<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';
use Svgta\EasyApi\utils\conf;
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__FILE__, 2));
$dotenv->load();
require dirname(__FILE__, 2) . '/vendor/Svgta/EasyApi/docApi/SvgtaApiGenDoc.php';

$openapi = \OpenApi\Generator::scan([
  dirname(__FILE__, 1),
  dirname(__FILE__, 2) . '/lib/controller',
]);
$apiJson = $openapi->toJson();

array_push($apiDocs, [
  "url" => 'docapi.json',
  "name" => API_NAME . ' ' . conf::getConfKey('CONF_GENERAL', 'apiVersion'),
]);

array_push($jsonList, [
    "filename" => 'docapi.json',
    "json" => $apiJson,
]);

foreach($jsonList as $ar){
  file_put_contents(dirname(__FILE__, 2) . '/public/doc/' . $ar['filename'], $ar['json']);
}

$content = file_get_contents(dirname(__FILE__, 2) . "/public/doc/swagger-initializer.js.template");
$content = str_replace("{{API_DOC}}", json_encode($apiDocs, JSON_PRETTY_PRINT), $content);
file_put_contents(dirname(__FILE__, 2) . "/public/doc/swagger-initializer.js", $content);
