<?php


define( 'DATABASE_ENGINE',    'pgsql'); // mysql | pgsql | couchdb | mongodb | sqlite
define( 'DATABASE_USER',      'brian');
define( 'DATABASE_PASSWORD',  '');
define( 'DATABASE_NAME',      'todos');
define( 'DATABASE_HOST',      ''); // 'localhost' | '' | IP | name
define( 'DATABASE_PORT',      5432); // 3306/mysql | 5432/pgsql | 443



require 'lib/Structal.php';
require 'lib/Moor.php';
require 'lib/Mullet.php';





class Task extends Model {
  
   static $id = array( 'type'=>'Integer', 'key'=>true );

}


class TasksApp extends Controller {
  
  static $events = array(
    'submit form' => 'create'
  );
  
  function init() {
    Task::bind( 'create', 'addOne' );
    Task::bind( 'refresh', 'addAll' );
    Task::fetch();
  }
  
  function create() {
    Task::create(array(
      'name' => self::inputval()
    ));
  }
  
  function inputval() {
    return '';
  }
  
  function addOne($task) {
    $view = new Tasks(array(
      'item' => $task
    ));
    return self::append($view->render());
  }
  
  function addAll() {
    foreach( Task::each() as $task )
      self::addOne( $task );
  }
  
}


class Tasks extends Controller {
  
  static $item;

  function init() {
    self::$item = '';
    Task::bind( 'update', 'render' );
//    Task::bind( 'update', 'addChange' );
//    Task::bind( 'delete', 'addChange' );
    Task::bind( 'create', 'addChange' );
    Task::bind( 'read', 'render', 'before' );
  }
  
  function html($data) {
    echo $data;
  }
  
  function render() {
    header('HTTP/1.1 200 OK');
    header('Content-Type: application/json');
    self::html(self::$item);
    return self;
  }
  
  function addOne( $task ) {
    
  }
  
  function addAll() {
    
  }
  
  function renderCount() {
    
  }
  
  function beforeAction() {
    trigger_before( strtolower($_SERVER['REQUEST_METHOD']) );
  }
  
  function afterAction() {
    trigger_after( strtolower($_SERVER['REQUEST_METHOD']) );
  }
  
  function get() {
    $conn = new Mullet();
    $coll = $conn->user->tasks;
    $tasks = array();
    if (isset($_GET['id']))
      $cursor = $coll->find(array(
        'id' => $_GET['id']
      ));
    else
      $cursor = $coll->find();
  	while( $cursor->hasNext() ) {
  		$task = $cursor->getNext();
      $tasks[] = array(
        'name' => $task->name,
        'done' => false,
        'id' => $task->id
      );
  	}
    echo json_encode($tasks);
  }

  function post() {
    $conn = new Mullet();
    $coll = $conn->user->tasks;
    $data = json_decode(file_get_contents('php://input'));
    $result = $coll->insert(
      $data
    );
    if (!isset($data->id)) {
      // for backbone.js create a model.id
      $cursor = $coll->find(array(
        'name' => $data->name
      ));
      $task = $cursor->getNext();
      $data->id = $task->keyname;
      $result = $coll->update(
        array(array( 'keyname' => $task->keyname )),
        array((array)$data)
      );
    }
    $_GET['id'] = $data->id;
    echo json_encode(array(
      'id'=>$data->id
    ));
  }

  function put() {
    $conn = new Mullet();
    $coll = $conn->user->tasks;
    $data = json_decode(file_get_contents('php://input'));
    $result = $coll->update(
      array(array( 'id' => $_GET['id'] )),
      array((array)$data)
    );
    echo json_encode(array(
      'ok'=>true
    ));
  }

  function delete() {
    $conn = new Mullet();
    $coll = $conn->user->tasks;
    $result = $coll->remove(
      array(array( 'id' => $_GET['id'] ))
    );
    echo json_encode(array(
      'ok'=>true
    ));
  }
  
  function changes() {
    $changes = array();
    $conn = new Mullet();
    $coll = $conn->system->changes;
    $cursor = $coll->find(array(
      'resource' => 'tasks'
    ));
  	while( $cursor->hasNext() ) {
  		$c = $cursor->getNext();
      $changes['results'][] = array(
        'seq' => $c->seq,
        'id' => $c->id,
        'changes' => array(array(
          'rev' => $c->rev
        ))
      );
    }
    if (isset($c))
      $changes['last_seq'] = $c->id;
    header('HTTP/1.1 200 OK');
    header('Content-Type: application/json');
    echo json_encode($changes);
  }
  
  function addChange() {
    $conn = new Mullet();
    $coll = $conn->system->changes;
    $result = $coll->insert(
      array(
      'resource' => 'tasks',
      'seq' => time(),
      'id' => $_GET['id'],
      'rev' => time()
      )
    );
  }
  

}



function index() {
  require 'lib/Mustache.php';
  $m = new Mustache;
  session_start();
  $params = array();
  if (isset($_SESSION['current_user']))
    $params['username'] = $_SESSION['current_user'];
  echo $m->render(file_get_contents('tpl/index.html'),$params);
}



if (!in_array(strtolower($_SERVER['REQUEST_METHOD']),array('put','delete')))
  Moor::route('/@class/@method', '@class(uc)::@method(lc)');
Moor::route('/@class/:id([0-9A-Za-z_-]+)', '@class(uc)::'.strtolower($_SERVER['REQUEST_METHOD']));
Moor::route('/@class', '@class(uc)::'.strtolower($_SERVER['REQUEST_METHOD']));
Moor::route( '/', 'index' );
Moor::run();
