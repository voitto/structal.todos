<?php


define( 'DATABASE_ENGINE',    'pgsql'); // mysql | pgsql | couchdb | mongodb | sqlite
define( 'DATABASE_USER',      'brian');
define( 'DATABASE_PASSWORD',  '');
define( 'DATABASE_NAME',      'todos');
define( 'DATABASE_HOST',      ''); // 'localhost' | '' | IP | name
define( 'DATABASE_PORT',      5432); // 3306/mysql | 5432/pgsql | 443




require 'lib/Moor.php';

Moor::route( '/changes', 'changes' );
Moor::route( '/:resource/:id', 'constructor' );
Moor::route( '/:resource', 'constructor' );
Moor::route( '/', 'index' );
Moor::run();


// JSON

function constructor() {
  require 'lib/Mullet.php';
  $model = 'mdl/'.$_GET['resource'].".php";
  if (file_exists($model)) include $model;
  $action = strtolower($_SERVER['REQUEST_METHOD']);
  if (isset($_GET['action']))
    $action = $_GET['action'];
  $mapper = ucwords($_GET['resource']);
  if (class_exists($mapper))
    $obj = new $mapper;
  header('HTTP/1.1 200 OK');
  header('Content-Type: application/json');
  if (isset($obj) && method_exists($obj,$action))
    echo json_encode($obj->$action())."\n";
  else
    echo json_encode(array(
      'error'=>'internal error',
      'code'=>500
    ));
}

// HTML

function index() {
  require 'lib/Mustache.php';
  $m = new Mustache;
  session_start();
  $params = array();
  if (isset($_SESSION['current_user']))
    $params['username'] = $_SESSION['current_user'];
  echo $m->render(file_get_contents('tpl/index.html'),$params);
}
