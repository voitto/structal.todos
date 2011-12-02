<?php


$config = array(
  '',       // host name ('localhost' | '' | IP | name)
  'brian',  // db user name
  '',       // db user password
  'todos',   // db name
  5432,     // port number (3306/mysql | 5432/pgsql | 443/ssl)
  'pgsql'   // db type (mysql | pgsql | couchdb | mongodb | sqlite | remote)
);



require 'lib/Structal.php';
require 'lib/Mullet.php';




class Task extends Model {}






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
  
}

return new Tasks;