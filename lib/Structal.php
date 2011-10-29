<?php


// Structal

// helper library for making Sinatra-like PHP apps

// http://twitter.com/structalapp



class Model {
	
	static $filters = array();
	static $rels = array();
  
  function fetch() {
    
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
