<?php

class init{
  private $baseDir = null;
  private $namespace = null;
  private $psr4Dir = null;
  private $libDir = null;
  private $composer = null;

  const PUBLIC_DIR = 'public';
  const CONFIG_DIR = 'config';
  const DOCAPI_DIR = 'docApi';
  const LIB_NAMESPACE = 'Svgta\\EasyApi\\';
  const ENV_BASE_DIR = 'BASE_DIR';
  const EXAMPLE_EXTENSION = 'example';
  const ABSTRACT_CONTROLLER = 'apictrlAbstract.php';
  const ABSTRACT_BACKEND = 'abstractBackend.php';
  const APIINFO_FILE = 'apiInfo.php';
  const DOCAPI_NAMESPACE = 'Svgta\\docAPI\\';
  const DOCAPI_COPY_FILES = [
    'genDoc.php',
    'apiInfo.php.example',
  ];
  const API_DIR = [
    'BACKEND' => 'backend',
    'BACKEND_MONGO' => 'backend/mongodb',
    'CONTROLLER' => 'controller',
    'CONTROLLER_V1' => 'controller/v1r0',
    'UTILS' => 'utils',
  ];
  const ROUTES_FILE = 'routes.php';
  const KERNEL_FILE = 'kernel.php';

  public function __construct($baseDir){
    $this->baseDir = $baseDir;
    if(!is_writable($this->baseDir))
      throw new Exception('Put correct right on ' . $this->baseDir . ' to create dir and files');
    if(!is_file($this->baseDir . '/composer.json'))
      throw new Exception('You have to initiate your composer project');
    $composer = json_decode(file_get_contents($this->baseDir . '/composer.json'), TRUE);
    if(!isset($composer['autoload']['psr-4']))
      throw new Exception('You need to specify the autoload PSR-4 directory');

    $psr4 = $composer['autoload']['psr-4'];
    $this->namespace = array_key_first($psr4);
    $this->psr4Dir =  $this->baseDir . '/' . $psr4[$this->namespace];

    $this->libDir = $this->baseDir . mb_strtolower('/vendor/Svgta/EasyApi');
    $this->composer = $composer;
  }

  public function createDocApi(){
    $destDir = $this->baseDir . '/' . self::DOCAPI_DIR;
    $sourceDir = $this->libDir . '/' . self::DOCAPI_DIR;
    if(!is_dir($destDir))
      mkdir($destDir);
    if(!is_writable($destDir))
      throw new Exception('Put correct right on ' . $destDir . ' to create dir and files');

    foreach(self::DOCAPI_COPY_FILES as $file){
      if(!is_file($sourceDir . $destDir . '/' . $file))
        copy($sourceDir . '/' . $file, $destDir . '/' . $file);
    }
    $psr4 = $this->composer['autoload']['psr-4'];
    $docApi_exist = false;
    $search = self::DOCAPI_DIR . '/';
    foreach($psr4 as $value){
      if($value == $search){
        $docApi_exist = true;
        break;
      }
    }
    if(!$docApi_exist){
      $this->composer['autoload']['psr-4'][self::DOCAPI_NAMESPACE] = $search;
      file_put_contents($this->baseDir . '/composer.json', json_encode($this->composer, JSON_PRETTY_PRINT));
    }
  }

  public function genEnvFile(): void{
    if(!is_file($this->baseDir . '/.env'))
      copy($this->libDir . '/.env.example', $this->baseDir . '/.env');
    $content = file_get_contents($this->baseDir . '/.env');
    $contentAr = explode(PHP_EOL, $content);
    foreach($contentAr as $k => $line){
      $lineAr = explode('=', $line);
      if($lineAr[0] == self::ENV_BASE_DIR){
        $lineAr[1] = $this->baseDir;
        $contentAr[$k] = implode('=', $lineAr);
      }
    }
    $content = implode(PHP_EOL, $contentAr);
    file_put_contents($this->baseDir . '/.env', $content);
  }

  public function createConfDir(){
    $destDir = $this->baseDir . '/' . self::CONFIG_DIR;
    $sourceDir = $this->libDir . '/' . self::CONFIG_DIR;
    $this->recurseCopy($sourceDir, $destDir);
    $list = scandir($destDir);
    foreach($list as $l){
      $file = $destDir . '/' .$l;
      if(!is_file($file))
        continue;
      $path = pathinfo($file);
      if($path['extension'] == self::EXAMPLE_EXTENSION){
          $newFile = str_replace('.' . self::EXAMPLE_EXTENSION, '', $file);
          if(!file_exists($newFile))
            copy($file, $newFile);
      }
    }
  }

  public function createPublicDir(): void {
    $destDir = $this->baseDir . '/' . self::PUBLIC_DIR;
    $sourceDir = $this->libDir . '/' . self::PUBLIC_DIR;
    $this->recurseCopy($sourceDir, $destDir);
    $index = file_get_contents($destDir . '/index.php');
    $index = str_replace(self::LIB_NAMESPACE . 'kernel', $this->namespace . 'kernel', $index);
    file_put_contents($destDir . '/index.php', $index);
  }

  public function createApiDir(): void {
    $dir = $this->psr4Dir;
    if(!is_dir($dir))
    if(!mkdir($dir))
      throw new Exception('Can\'t create directory ' . $dir);
    foreach(self::API_DIR as $_dir){
        $nDir = $dir . $_dir;
        if(!is_dir($nDir))
        if(!mkdir($nDir))
          throw new Exception('Can\'t create directory ' . $nDir);
    }
  }

  public function genUtils(){
    $dir = $this->psr4Dir . '/' . self::API_DIR['UTILS'];
    $ar = explode('\\', rtrim($this->namespace, '\\'));
    $filename = end($ar) . '.php';
    $file = $dir . '/' . $filename;
    $namespace = $this->namespace . self::API_DIR['UTILS'];
    $className = str_replace('.php', '', $filename);

    $content = <<<UTILS
    <?php
    namespace $namespace;

    class $className{
    }
    UTILS;

    if(!is_file($file))
    if(!file_put_contents($file, $content))
      throw new Exception('Can\'t write file ' . $file);;
  }

  public function genBackendAbstract(){
    $dir = $this->psr4Dir . '/' . self::API_DIR['BACKEND'];
    $file = $dir . '/' . self::ABSTRACT_BACKEND;
    $namespace = $this->namespace . self::API_DIR['BACKEND'];
    $absBackend = str_replace('.php', '', self::ABSTRACT_BACKEND);

    $content = <<<BACKEND
    <?php
    namespace $namespace;
    use Svgta\\EasyApi\\backend\\abstractReq as svgtaAbstractReq;
    use Svgta\\EasyApi\\backend\\Exception;

    abstract class $absBackend extends svgtaAbstractReq{
    }
    BACKEND;

    if(!is_file($file))
    if(!file_put_contents($file, $content))
      throw new Exception('Can\'t write file ' . $file);;
  }

  public function genControllerAbstract(){
    $dir = $this->psr4Dir . '/' . self::API_DIR['CONTROLLER'];
    $file = $dir . '/' . self::ABSTRACT_CONTROLLER;
    $namespace = $this->namespace . self::API_DIR['CONTROLLER'];
    $absCtrl = str_replace('.php', '', self::ABSTRACT_CONTROLLER);

    $content = <<<CONTROLLER
    <?php
    namespace $namespace;
    use Svgta\\EasyApi\\controller\\apictrlAbstract as svgtaCtrlAbstract;
    use Svgta\\EasyApi\\backend\\Exception;

    abstract class $absCtrl extends svgtaCtrlAbstract{
      protected \$parent_backend = null;
      protected \$backend = null;

      public function __construct(string \$backend = null, array \$request = [], ?string \$scopes = null){
        \$this->parent_backend = \$parent_backend;
        \$this->backend = \$backend;
        parent::__construct(\$backend , \$request, \$scopes);
      }

      protected function loadBackend(string \$target){
        if(isset(self::\$cacheBackend[\$target]))
          return self::\$cacheBackend[\$target];
        if(class_exists(\$this->parent_backend . \$target))
          \$str = \$this->parent_backend . \$target;
        else
          \$str = \$this->backend . \$target;
        self::\$cacheBackend[\$target] = new \$str();
        return self::\$cacheBackend[\$target];
      }
    }
    CONTROLLER;

    if(!is_file($file))
    if(!file_put_contents($file, $content))
      throw new Exception('Can\'t write file ' . $file);;
  }

  public function genKernelFile(){
    $dir = $this->psr4Dir;
    $file = $dir . self::KERNEL_FILE;
    $namespace =$string = rtrim($this->namespace, '\\');

    $content = <<<KERNEL
    <?php
    namespace $namespace;
    use Svgta\\EasyApi\\kernel as svgtaKernel;
    use Svgta\\EasyApi\\utils\\route;
    use Svgta\\EasyApi\\utils\\conf;
    use Svgta\\EasyApi\\utils\\utils;

    class kernel extends svgtaKernel{
      protected \$parent_backend = null;
      public function __construct(){
        require dirname(__FILE__, 1) . "/routes.php";
        parent::__construct();
        \$dirVersion = null;
        if(\$this->route['dir'])
          \$dirVersion = \$this->route['dir'] . '\\';
        \$class = __namespace__ . '\\controller\\' . \$dirVersion . \$this->route['class'];
        \$dbType = \$_ENV['DB_TYPE'];
        \$backend = __namespace__ . '\\backend\\' . \$dbType . '\\';
        \$c = new \$class(\$this->parent_backend, \$backend, utils::getRequest(), \$this->route['scope']);
        \$method = \$this->route['method'];
        \$arg = \$this->route['arg'];
        \$c->\$method(\$arg);
      }
    }
    KERNEL;

    if(!is_file($file))
    if(!file_put_contents($file, $content))
      throw new Exception('Can\'t write file ' . $file);;
  }

  public function genRouteFile(){
    $dir = $this->psr4Dir;
    $file = $dir . self::ROUTES_FILE;
    $content = <<<ROUTE
    <?php
    use Svgta\\EasyApi\\utils\\route;
    //route::requestMethod(path, ctrlVersionDirectory, [class, class method, scope])
    //Example => route::get('v1/myAPI/example', 'v1r0', ['apiCtrlExample', 'getExample', 'authorization admin_read']);
    ROUTE;

    if(!is_file($file))
    if(!file_put_contents($file, $content))
      throw new Exception('Can\'t write file ' . $file);;
  }

  private function recurseCopy(
    string $sourceDirectory,
    string $destinationDirectory,
    string $childFolder = ''
  ): void {
    $directory = opendir($sourceDirectory);

    if (is_dir($destinationDirectory) === false) {
        mkdir($destinationDirectory);
    }

    if ($childFolder !== '') {
        if (is_dir("$destinationDirectory/$childFolder") === false) {
            mkdir("$destinationDirectory/$childFolder");
        }

        while (($file = readdir($directory)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (is_dir("$sourceDirectory/$file") === true) {
                recurseCopy("$sourceDirectory/$file", "$destinationDirectory/$childFolder/$file");
            } else {
                copy("$sourceDirectory/$file", "$destinationDirectory/$childFolder/$file");
            }
        }

        closedir($directory);

        return;
    }

    while (($file = readdir($directory)) !== false) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        if (is_dir("$sourceDirectory/$file") === true) {
            $this->recurseCopy("$sourceDirectory/$file", "$destinationDirectory/$file");
        }
        else {
            copy("$sourceDirectory/$file", "$destinationDirectory/$file");
        }
    }

    closedir($directory);
  }
}
