<?php
require dirname(__FILE__, 2) . '/vendor/autoload.php';
require dirname(__FILE__, 1) . '/class/initClass.php';
use Svgta\initAPI\secureKey;
use Svgta\EasyApi\utils\conf;

echo PHP_EOL . "\033[0m";
echo '##################################################'. PHP_EOL;
echo '#' . PHP_EOL;
echo "#       01.initRepo - \033[96mthe first step\033[0m" . PHP_EOL;
echo '#' . PHP_EOL;
echo '#' . PHP_EOL;
echo '##################################################'. PHP_EOL ;
echo '#' . PHP_EOL;

try{
  $init = new init(dirname(__FILE__, 2));
}catch(Exception $e){
  echo $e->getMessage() . PHP_EOL;
  die;
}
echo "# 1.1.  \033[93mCreate public dir\033[0m" . PHP_EOL;
$init->createPublicDir();
echo "# 1.2.  \033[93mCreate controller, backend and utils dir\033[0m" . PHP_EOL;
$init->createApiDir();
echo "# 1.3.  \033[93mCreate route file\033[0m" . PHP_EOL;
$init->genRouteFile();
echo "# 1.4.  \033[93mCreate kernel file\033[0m" . PHP_EOL;
$init->genKernelFile();
echo "# 1.5.  \033[93mCreate .env file\033[0m" . PHP_EOL;
$init->genEnvFile();
echo "# 1.6.  \033[93mCreate controller abstract file\033[0m" . PHP_EOL;
$init->genControllerAbstract();
echo "# 1.7.  \033[93mCreate backend abstract file\033[0m" . PHP_EOL;
$init->genBackendAbstract();
echo "# 1.8.  \033[93mCreate utils file\033[0m" . PHP_EOL;
$init->genUtils();
echo "# 1.9.  \033[93mCreate conf dir and files\033[0m" . PHP_EOL;
$init->createConfDir();

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__FILE__, 2));
$dotenv->load();
conf::setEnvFile(dirname(__FILE__, 2) . '/.env');

echo "# 1.10. \033[93mCreate the server secure key file\033[0m" . PHP_EOL;
$secKey = new secureKey();
if(!isset($_ENV['CONF_SECURITY_KEY']) OR !is_file($_ENV['CONF_SECURITY_KEY'])){
  conf::setConfKey('CONF_SECURITY_KEY', 'securityKey', $secKey->getKey());
  conf::saveConf('CONF_SECURITY_KEY');
}

echo "# 1.11. \033[93mCreate docAPI dir and files\033[0m" . PHP_EOL;
$init->createDocApi();

echo '#' . PHP_EOL;
echo "#       01.initRepo - \033[96mEnd of script\033[0m" . PHP_EOL;
echo '#' . PHP_EOL;
echo '##################################################'. PHP_EOL;
echo PHP_EOL;
echo "! \033[37mYou need to run \033[96m'composer update'\033[37m before continue\033[0m" . PHP_EOL;
echo "! \033[37mYou have to config \033[96mthe .env file and the config files\033[37m before going to step 2\033[0m" . PHP_EOL . PHP_EOL;
