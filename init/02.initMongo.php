<?php
namespace Svgta\EasyApi;

require dirname(__FILE__, 2) . '/vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__FILE__, 2));
$dotenv->load();

echo PHP_EOL . "\033[0m";
echo '##################################################'. PHP_EOL;
echo '#' . PHP_EOL;
echo "#       02.initMongo - \033[96mthe second step\033[0m" . PHP_EOL;
echo '#' . PHP_EOL;
echo '#' . PHP_EOL;
echo '##################################################'. PHP_EOL ;
echo '#' . PHP_EOL;

$dbType = $_ENV['DB_TYPE'];
$backend = __namespace__ . '\\backend\\' . $dbType . '\\';
$dbClass = $backend . 'connexion';
$dbC = new $dbClass();
$db = $dbC->getConn();

echo "# 2.1.  \033[93mCreate auth indexes\033[0m" . PHP_EOL;
$auth = $db->auth;
$auth->createIndex(
  ['user_id' => 1],
  [
    'unique' => true,
    'name' => "user_id",
  ]
);

echo "# 2.2.  \033[93mCreate admin indexes\033[0m" . PHP_EOL;
$admin = $db->admin;
$admin->createIndex(
  ['admin_id' => 1],
  [
    'unique' => true,
    'name' => "admin_id",
  ]
);
$admin->createIndex(
  ['email' => 1],
  [
    'unique' => false,
    'name' => "email",
  ]
);

echo "# 2.3.  \033[93mCreate jwk indexes\033[0m" . PHP_EOL;
$jwk = $db->jwk;
$jwk->createIndex(
  ['kid' => 1],
  [
    'unique' => true,
    'name' => "kid",
  ]
);

echo "# 2.4.  \033[93mCreate logs indexes\033[0m" . PHP_EOL;
$logs = $db->logs;
$logs->createIndex(
  ['timestamp' => 1],
  [
    'unique' => false,
    'name' => "timestamp",
  ]
);

echo "# 2.5.  \033[93mCreate session indexes\033[0m" . PHP_EOL;
$session = $db->session;
$session->createIndex(
  ['exp' => 1],
  [
    'unique' => false,
    'name' => "exp",
  ]
);
$session->createIndex(
  ['token' => 1],
  [
    'unique' => true,
    'name' => "token",
  ]
);
$session->createIndex(
  ['client_id' => 1],
  [
    'unique' => false,
    'name' => "client_id",
  ]
);
echo "# 2.6.  \033[93mCreate first Super Admin user\033[0m" . PHP_EOL;
require dirname(__FILE__, 1) . '/class/superAdmin.php';
$sAdmin = new \superAdmin($backend);
$res = $sAdmin->genAdmin();
echo "# \033[96mSave theses datas in a secure way. They can't be sent a second time. \033[0m";
echo PHP_EOL;
echo "# \033[91m " .PHP_EOL;
echo json_encode($res, JSON_PRETTY_PRINT);
echo "\033[0m";
echo PHP_EOL;
echo '# ... Super Admin created'. PHP_EOL;
echo '#' . PHP_EOL;
echo "#       02.initMongo - \033[96mEnd of script\033[0m" . PHP_EOL;
echo '#' . PHP_EOL;
echo '##################################################'. PHP_EOL;
echo PHP_EOL;
