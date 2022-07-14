<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__FILE__, 2));
$dotenv->load();
Svgta\EasyApi\utils\conf::setEnvFile(dirname(__FILE__, 2) . '/.env');

$kernel = new Svgta\EasyApi\kernel();
