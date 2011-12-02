<?php


// Structal

// helper library for making Sinatra-like PHP apps

// http://twitter.com/structalapp



class Model {
	
	static $filters = array();
	static $rels = array();
  
  function fetch() {
    
  }
  
  function configure() {
    
  }
  
  function bind( $events, $callback, $when='after' ) {
    $events = explode(' ',$events);
    $map = array(
      'create' => array('post'),
      'refresh' => array('post','put','delete'),
      'change' => array('post','put','delete'),
      'update' => array('put'),
      'read' => array('get'),
      'delete' => array('delete'),
      'remove' => array('delete')
    );
    foreach($events as $e)
      foreach($map[$e] as $ee)
        if ($when == 'after')
          after_filter( get_class($this).'::'.$callback, $ee );
        elseif ($when == 'before')
          before_filter( get_class($this).'::'.$callback, $ee );
  }
  
	function validates_uniqueness_of( $k ) {
		self::$filters[] = array($k,'unique');
	}

	function many( $rel ) {
		self::$rels[] = array($rel=>'many');
	}
	
}



if (class_exists('MoorAbstractController')) {


  class Controller extends MoorAbstractController {

  	 var $access_list = array();

  	 function member_of() {
  	   return false;
  	 }

     function get() {
       $conn = new Mullet();
       if (isset($_GET['class']))
         $class = $_GET['class'];
       else
         $class = $_GET['_Moor_class'];
       $coll = $conn->user->$class;
       $items = array();
       if (isset($_GET['id']))
         $cursor = $coll->find(array(
           'id' => $_GET['id']
         ));
       else
         $cursor = $coll->find();
     	while( $cursor->hasNext() ) {
     		$item = $cursor->getNext();
         $items[] = (array) $item;
     	}
       echo json_encode($items);
     }

     function post() {
       $conn = new Mullet();
       if (isset($_GET['class']))
         $class = $_GET['class'];
       else
         $class = $_GET['_Moor_class'];
       $coll = $conn->user->$class;
       $data = json_decode(file_get_contents('php://input'));
       $result = $coll->insert(
         $data
       );
       if (!isset($data->id)) {
         // for backbone.js create a model.id
         $cursor = $coll->find(array(
           'name' => $data->name
         ));
         $item = $cursor->getNext();
         $data->id = $item->keyname;
         $result = $coll->update(
           array(array( 'keyname' => $item->keyname )),
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
       if (isset($_GET['class']))
         $class = $_GET['class'];
       else
         $class = $_GET['_Moor_class'];
       $coll = $conn->user->$class;
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
       if (isset($_GET['class']))
         $class = $_GET['class'];
       else
         $class = $_GET['_Moor_class'];
       $coll = $conn->user->$class;
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
       if (isset($_GET['class']))
         $class = $_GET['class'];
       else
         $class = $_GET['_Moor_class'];
       $cursor = $coll->find(array(
         'resource' => $class
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
       if (isset($_GET['class']))
         $class = $_GET['class'];
       else
         $class = $_GET['_Moor_class'];
       $result = $coll->insert(
         array(
         'resource' => $class,
         'seq' => time(),
         'id' => $_GET['id'],
         'rev' => time()
         )
       );
     }

  	 function let_access( $fields ) {
       $this->let_read( $fields );
       $this->let_write( $fields );
       $this->let_create( $fields );
       $this->let_delete( $fields );
       $this->let_superuser( $fields );
     }

     function let_read( $fields ) {
       $args = explode( " ", $fields );
       if (!(count($args)>0)) trigger_error( "invalid data model access rule", E_USER_ERROR );
       foreach ( $args as $str) {
         $pair = split( ":", $str );
         if (!(count($pair)==2)) trigger_error( "invalid data model access rule", E_USER_ERROR );
         if ($pair[0] == 'all') {
           foreach ( $this->field_array as $field => $data_type ) {
             if (!(in_array($pair[1],$this->access_list['read'][$field])))
               $this->access_list['read'][$field][] = $pair[1];
           }
         } else {
           if (!(in_array($pair[1],$this->access_list['read'][$pair[0]])))
             $this->access_list['read'][$pair[0]][] = $pair[1];
         }
       }
     }

     function let_write( $fields ) {
       $args = explode( " ", $fields );
       if (!(count($args)>0)) trigger_error( "invalid data model access rule", E_USER_ERROR );
       foreach ( $args as $str) {
         $pair = split( ":", $str );
         if (!(count($pair)==2)) trigger_error( "invalid data model access rule", E_USER_ERROR );
         if ($pair[0] == 'all') {
           foreach ( $this->field_array as $field => $data_type ) {
             if (!(in_array($pair[1],$this->access_list['write'][$field])))
               $this->access_list['write'][$field][] = $pair[1];
           }
         } else {
           if (!(in_array($pair[1],$this->access_list['write'][$pair[0]])))
             $this->access_list['write'][$pair[0]][] = $pair[1];
         }
       }
     }

     function let_create( $fields ) {
       $args = explode( " ", $fields );
       if (!(count($args)>0)) trigger_error( "invalid data model access rule", E_USER_ERROR );
       $this->let_write( $fields );
       foreach ( $args as $str) {
         $pair = split( ":", $str );
         if (!(count($pair)==2)) trigger_error( "invalid data model access rule", E_USER_ERROR );
         if (!(isset($this->access_list['create'][$this->table][$pair[1]])))
           $this->access_list['create'][$this->table][] = $pair[1];
       }
     }

     function let_post( $fields ) {
       $this->let_create( $fields );
     }

     function let_modify( $fields ) {
       $this->let_write( $fields );
     }

     function let_put( $fields ) {
       $this->let_write( $fields );
     }

     function let_delete( $fields ) {
       $args = explode( " ", $fields );
       if (!(count($args)>0)) trigger_error( "invalid data model access rule", E_USER_ERROR );
       foreach ( $args as $str) {
         $pair = split( ":", $str );
         if (!(count($pair)==2)) trigger_error( "invalid data model access rule", E_USER_ERROR );
         if (!(isset($this->access_list['delete'][$this->table][$pair[1]])))
           $this->access_list['delete'][$this->table][] = $pair[1];
       }
     }

     function let_superuser( $fields ) {
       $args = explode( " ", $fields );
       if (!(count($args)>0)) trigger_error( "invalid data model access rule", E_USER_ERROR );
       foreach ( $args as $str) {
         $pair = split( ":", $str );
         if (!(count($pair)==2)) trigger_error( "invalid data model access rule", E_USER_ERROR );
         if (!(isset($this->access_list['superuser'][$this->table][$pair[1]])))
           $this->access_list['superuser'][$this->table][] = $pair[1];
       }
     }

     function can($action) {
       if (in_array($action,array('read','write'))) {
         $func = "can_".$action."_fields";
         if (!($this->$func($this->field_array)))
           return false;
         return true;
       }
       if (in_array($action,array('create','delete'))) {
         $func = "can_".$action;
         if (!($this->$func($this->table)))
           return false;
         return true;
       }
     }

     function can_write_fields( $fields ) {
       $return = false;
       foreach( $fields as $key=>$val ) {
         if ( $this->can_write( $key ) ) {
           $return = true;
         } else {
           return false;
         }
       }
       return $return;
     }

     function can_read_fields( $fields ) {
       $return = false;
       // array of field=>datatype
       foreach( $fields as $key=>$val ) {
         if ( $this->can_read( $key ) ) {
           $return = true;
         } else {
           return false;
         }
       }
       return $return;
     }



     function can_read( $resource ) {
       if (!(isset($this->access_list['read'][$resource]))) return false;
       foreach ( $this->access_list['read'][$resource] as $callback ) {
         if ( function_exists( $callback ) ) {
           if ($callback())
             return true;
         } else {
           if ( $this->member_of( $callback ))
             return true;
         }
       }
       return false;
     }

     function can_write( $resource ) {
       if (!(isset($this->access_list['write'][$resource]))) return false;
       foreach ( $this->access_list['write'][$resource] as $callback ) {
         if ( function_exists( $callback ) ) {
           if ($callback())
             return true;
         } else {
           if ( $this->member_of( $callback ))
             return true;
         }
       }
       return false;
     }

     function can_create( $resource ) {
       if (!(isset($this->access_list['create'][$resource]))) return false;
       foreach ( $this->access_list['create'][$resource] as $callback ) {
         if ( function_exists( $callback ) ) {
           if ($callback())
             return true;
         } else {
           if ( $this->member_of( $callback ))
             return true;
         }
       }
       return false;
     }

     function can_delete( $resource ) {
       if (!(isset($this->access_list['delete'][$resource]))) return false;
       foreach ( $this->access_list['delete'][$resource] as $callback ) {
         if ( function_exists( $callback ) ) {
           if ($callback())
             return true;
         } else {
           if ( $this->member_of( $callback ))
             return true;
         }
       }
       return false;
     }

     function can_superuser( $resource ) {
       if (!(isset($this->access_list['superuser'][$resource]))) return false;
       foreach ( $this->access_list['superuser'][$resource] as $callback ) {
         if ( function_exists( $callback ) ) {
           if ($callback())
             return true;
         } else {
           if ( $this->member_of( $callback ))
             return true;
         }
       }
       return false;
     }


  	public function __construct() {
  	  if (method_exists($this,'init'))
  	    $this->init();
  		$this->beforeAction();

  		try {
  		    parent::__construct();

  		} catch (Exception $e) {

  		    $exception = new ReflectionClass($e);

  		    while($exception) {
      		    // pass exceptions to a __catch_ExceptionClass method 
      		    $magic_exception_catcher = "catch" . $exception->getName();
  				if (is_callable(array($this, $magic_exception_catcher))) {
  					call_user_func_array(array($this, $magic_exception_catcher), array($e));
  					break;
  				}
  				$exception = $exception->getParentClass();
  			}

   			if (!$exception) {
                  throw $e;
              }
  		}

  		$this->afterAction();
  	}

  	protected function beforeAction() {}
  	protected function afterAction() {
  	  trigger_after( strtolower($_SERVER['REQUEST_METHOD']) );
  	}
  	
  	public function __destruct() {
  		$this->afterAction();
  	}
  	
  }

} else {
  
  class Controller  {

  	 var $access_list = array();

  	 function member_of() {
  	   return false;
  	 }

     function get() {
       $conn = new Mullet();
       if (isset($_GET['class']))
         $class = $_GET['class'];
       else
         $class = $_GET['_Moor_class'];
       $coll = $conn->user->$class;
       $items = array();
       if (isset($_GET['id']))
         $cursor = $coll->find(array(
           'id' => $_GET['id']
         ));
       else
         $cursor = $coll->find();
     	while( $cursor->hasNext() ) {
     		$item = $cursor->getNext();
         $items[] = (array) $item;
     	}
       echo json_encode($items);
     }

     function post() {
       $conn = new Mullet();
       if (isset($_GET['class']))
         $class = $_GET['class'];
       else
         $class = $_GET['_Moor_class'];
       $coll = $conn->user->$class;
       $data = json_decode(file_get_contents('php://input'));
       $result = $coll->insert(
         $data
       );
       if (!isset($data->id)) {
         // for backbone.js create a model.id
         $cursor = $coll->find(array(
           'name' => $data->name
         ));
         $item = $cursor->getNext();
         $data->id = $item->keyname;
         $result = $coll->update(
           array(array( 'keyname' => $item->keyname )),
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
       if (isset($_GET['class']))
         $class = $_GET['class'];
       else
         $class = $_GET['_Moor_class'];
       $coll = $conn->user->$class;
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
       if (isset($_GET['class']))
         $class = $_GET['class'];
       else
         $class = $_GET['_Moor_class'];
       $coll = $conn->user->$class;
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
       if (isset($_GET['class']))
         $class = $_GET['class'];
       else
         $class = $_GET['_Moor_class'];
       $cursor = $coll->find(array(
         'resource' => $class
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
       if (isset($_GET['class']))
         $class = $_GET['class'];
       else
         $class = $_GET['_Moor_class'];
       $result = $coll->insert(
         array(
         'resource' => $class,
         'seq' => time(),
         'id' => $_GET['id'],
         'rev' => time()
         )
       );
     }

  	 function let_access( $fields ) {
       $this->let_read( $fields );
       $this->let_write( $fields );
       $this->let_create( $fields );
       $this->let_delete( $fields );
       $this->let_superuser( $fields );
     }

     function let_read( $fields ) {
       $args = explode( " ", $fields );
       if (!(count($args)>0)) trigger_error( "invalid data model access rule", E_USER_ERROR );
       foreach ( $args as $str) {
         $pair = split( ":", $str );
         if (!(count($pair)==2)) trigger_error( "invalid data model access rule", E_USER_ERROR );
         if ($pair[0] == 'all') {
           foreach ( $this->field_array as $field => $data_type ) {
             if (!(in_array($pair[1],$this->access_list['read'][$field])))
               $this->access_list['read'][$field][] = $pair[1];
           }
         } else {
           if (!(in_array($pair[1],$this->access_list['read'][$pair[0]])))
             $this->access_list['read'][$pair[0]][] = $pair[1];
         }
       }
     }

     function let_write( $fields ) {
       $args = explode( " ", $fields );
       if (!(count($args)>0)) trigger_error( "invalid data model access rule", E_USER_ERROR );
       foreach ( $args as $str) {
         $pair = split( ":", $str );
         if (!(count($pair)==2)) trigger_error( "invalid data model access rule", E_USER_ERROR );
         if ($pair[0] == 'all') {
           foreach ( $this->field_array as $field => $data_type ) {
             if (!(in_array($pair[1],$this->access_list['write'][$field])))
               $this->access_list['write'][$field][] = $pair[1];
           }
         } else {
           if (!(in_array($pair[1],$this->access_list['write'][$pair[0]])))
             $this->access_list['write'][$pair[0]][] = $pair[1];
         }
       }
     }

     function let_create( $fields ) {
       $args = explode( " ", $fields );
       if (!(count($args)>0)) trigger_error( "invalid data model access rule", E_USER_ERROR );
       $this->let_write( $fields );
       foreach ( $args as $str) {
         $pair = split( ":", $str );
         if (!(count($pair)==2)) trigger_error( "invalid data model access rule", E_USER_ERROR );
         if (!(isset($this->access_list['create'][$this->table][$pair[1]])))
           $this->access_list['create'][$this->table][] = $pair[1];
       }
     }

     function let_post( $fields ) {
       $this->let_create( $fields );
     }

     function let_modify( $fields ) {
       $this->let_write( $fields );
     }

     function let_put( $fields ) {
       $this->let_write( $fields );
     }

     function let_delete( $fields ) {
       $args = explode( " ", $fields );
       if (!(count($args)>0)) trigger_error( "invalid data model access rule", E_USER_ERROR );
       foreach ( $args as $str) {
         $pair = split( ":", $str );
         if (!(count($pair)==2)) trigger_error( "invalid data model access rule", E_USER_ERROR );
         if (!(isset($this->access_list['delete'][$this->table][$pair[1]])))
           $this->access_list['delete'][$this->table][] = $pair[1];
       }
     }

     function let_superuser( $fields ) {
       $args = explode( " ", $fields );
       if (!(count($args)>0)) trigger_error( "invalid data model access rule", E_USER_ERROR );
       foreach ( $args as $str) {
         $pair = split( ":", $str );
         if (!(count($pair)==2)) trigger_error( "invalid data model access rule", E_USER_ERROR );
         if (!(isset($this->access_list['superuser'][$this->table][$pair[1]])))
           $this->access_list['superuser'][$this->table][] = $pair[1];
       }
     }

     function can($action) {
       if (in_array($action,array('read','write'))) {
         $func = "can_".$action."_fields";
         if (!($this->$func($this->field_array)))
           return false;
         return true;
       }
       if (in_array($action,array('create','delete'))) {
         $func = "can_".$action;
         if (!($this->$func($this->table)))
           return false;
         return true;
       }
     }

     function can_write_fields( $fields ) {
       $return = false;
       foreach( $fields as $key=>$val ) {
         if ( $this->can_write( $key ) ) {
           $return = true;
         } else {
           return false;
         }
       }
       return $return;
     }

     function can_read_fields( $fields ) {
       $return = false;
       // array of field=>datatype
       foreach( $fields as $key=>$val ) {
         if ( $this->can_read( $key ) ) {
           $return = true;
         } else {
           return false;
         }
       }
       return $return;
     }



     function can_read( $resource ) {
       if (!(isset($this->access_list['read'][$resource]))) return false;
       foreach ( $this->access_list['read'][$resource] as $callback ) {
         if ( function_exists( $callback ) ) {
           if ($callback())
             return true;
         } else {
           if ( $this->member_of( $callback ))
             return true;
         }
       }
       return false;
     }

     function can_write( $resource ) {
       if (!(isset($this->access_list['write'][$resource]))) return false;
       foreach ( $this->access_list['write'][$resource] as $callback ) {
         if ( function_exists( $callback ) ) {
           if ($callback())
             return true;
         } else {
           if ( $this->member_of( $callback ))
             return true;
         }
       }
       return false;
     }

     function can_create( $resource ) {
       if (!(isset($this->access_list['create'][$resource]))) return false;
       foreach ( $this->access_list['create'][$resource] as $callback ) {
         if ( function_exists( $callback ) ) {
           if ($callback())
             return true;
         } else {
           if ( $this->member_of( $callback ))
             return true;
         }
       }
       return false;
     }

     function can_delete( $resource ) {
       if (!(isset($this->access_list['delete'][$resource]))) return false;
       foreach ( $this->access_list['delete'][$resource] as $callback ) {
         if ( function_exists( $callback ) ) {
           if ($callback())
             return true;
         } else {
           if ( $this->member_of( $callback ))
             return true;
         }
       }
       return false;
     }

     function can_superuser( $resource ) {
       if (!(isset($this->access_list['superuser'][$resource]))) return false;
       foreach ( $this->access_list['superuser'][$resource] as $callback ) {
         if ( function_exists( $callback ) ) {
           if ($callback())
             return true;
         } else {
           if ( $this->member_of( $callback ))
             return true;
         }
       }
       return false;
     }


  	public function __construct() {
  	  if (method_exists($this,'init'))
  	    $this->init();
  		$this->beforeAction();
      if (isset($_GET['class'])) {
        $class = ucwords($_GET['class']);
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        if (isset($_GET['method']))
          $method = $_GET['method'];
        if (method_exists($class,$method)){
          //$$class = new $class();
          //$$class->$method();
          if (get_class($this) == $class)
            $this->$method();
        }
      } else {
        index();
      }

  	}

  	public function __destruct() {
  		$this->afterAction();
  	}

  	protected function beforeAction() {}
  	protected function afterAction() {
  	  trigger_after( strtolower($_SERVER['REQUEST_METHOD']) );
  	}
  }
  
  
}




function json_emit($data) {
	header('HTTP/1.1 200 OK');
	header('Content-Type: application/json');
	echo json_encode($data)."\n";
	exit;
};


function json_error($data) {
	header('HTTP/1.1 200 OK');
	header('Content-Type: application/json');
	echo json_encode(array('ok'=>false,'error'=>$data))."\n";
	die;
};




function trigger_before( $func, &$obj_a=false ) {
  if ( isset( $GLOBALS['ASPECTS']['before'][$func] ) ) {
    foreach( $GLOBALS['ASPECTS']['before'][$func] as $callback ) {
      call_user_func_array( $callback, array( $obj_a ) );
    }
  }
}

function trigger_after( $func, &$obj_a=false ) {
  if ( isset( $GLOBALS['ASPECTS']['after'][$func] ) ) {
    foreach( $GLOBALS['ASPECTS']['after'][$func] as $callback ) {
      call_user_func_array( $callback, array( $obj_a ) );
    }
  }
}

function aspect_join_functions( $func, $callback, $type = 'after' ) {
  $GLOBALS['ASPECTS'][$type][$func][] = $callback;
}

function before_filter( $name, $func, $when = 'before' ) {
  aspect_join_functions( $func, $name, $when );
}

function after_filter( $name, $func, $when = 'after' ) {
  aspect_join_functions( $func, $name, $when );
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



if (!isset($hide_errors)) {
  ini_set('display_errors','1');
  ini_set('display_startup_errors','1');
  error_reporting (E_ALL & ~E_NOTICE );
}
