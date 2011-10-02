<?php

// Data-mullet
// HTTP/REST API inspired by Mongo, Couch & Sleepy Mongoose
// https://datamullet.com

// Brian Hendrickson <brian@megapump.com>
// Commit by Ben Martin <ben@martinben.com>
// Version: 0.2 
// June 16, 2011

// IRC: irc.freenode.net #datamullet
// Facebook: http://rp.ly/4i
// Twitter: http://rp.ly/4h


class Mullet {
	
	public $conn;
	private $dbclass,$user,$pass;
	
	function __construct( $dsn=false, $pass=false ) {
		if ($pass) {
		  $this->user = $dsn;
		  $this->pass = $pass;
		  $this->dbclass = 'MulletRemote';
		  return;
		}
		if (!defined('DATABASE_ENGINE')) 
		  return;
		$this->connect($dsn);
	}
	
	function __get( $arg ){
		if (!defined('DATABASE_ENGINE') || ($this->dbclass == 'MulletRemote'))
		  return new $this->dbclass($this->user,$this->pass,$arg);
    return new $this->dbclass($this->conn,$arg);
  }

  function authenticate( $user, $pass ) {
	  $this->user = $user;
	  $this->pass = $pass;
	  $this->dbclass = 'MulletRemote';
  }

  function connect( $dsn=false ) {
	  if (!$dsn && DATABASE_HOST == '')
		  $dsn = DATABASE_ENGINE.':dbname='.DATABASE_NAME;
		elseif (!$dsn)
		  $dsn = DATABASE_ENGINE.':host='.DATABASE_HOST.';dbname='.DATABASE_NAME;
	  switch ( DATABASE_ENGINE ) {
	    case 'mysql':
	      $this->dbclass = 'MulletMySQL';
				if (class_exists('PDO')) {
			    $this->conn = new PDO( $dsn, DATABASE_USER, DATABASE_PASSWORD );
				} else {
					$this->conn = mysql_connect( DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD );
					mysql_select_db( DATABASE_NAME );
				}
	      break;
	    case 'pgsql':
        $this->dbclass = 'MulletPostgreSQL';
				if (class_exists('PDO')) {
			    $this->conn = new PDO( $dsn, DATABASE_USER, DATABASE_PASSWORD );
			    $this->conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
				} else {
					trigger_error('Mullet pgsql requires pdo sorry!',E_USER_ERROR);
				}
	      break;
		  case 'sqlite':
        $this->dbclass = 'MulletSQLite';
				if (class_exists('PDO')) {
			    $this->conn = new PDO( $dsn );
				} else {
					trigger_error('Mullet sqlite requires pdo sorry!',E_USER_ERROR);
				}
	      break;
		  case 'mongodb':
	      $this->dbclass = 'MulletMongoDB';
			  $this->conn = new Mongo(
			    "mongodb://".DATABASE_USER.":".DATABASE_PASSWORD."@".DATABASE_HOST.":".DATABASE_PORT."/".DATABASE_NAME
			  );
	      break;
  		case 'couchdb':
        require_once 'lib/couch.php';
        require_once 'lib/couchClient.php';
        require_once 'lib/couchDocument.php';
  		  $this->dbclass = 'MulletCouchDB';
		  	$this->conn = false;
		  	//$this->conn = new couchClient(DATABASE_HOST.":".DATABASE_PORT,DATABASE_NAME);
		    break;
		}
  }

  function createdb($db) {
	  $result = false;
		switch ( DATABASE_ENGINE ) {
			case 'mysql':
				if (class_exists('PDO')) {
				  $query = "create database ".$db;
					$result = $this->conn->query( $query );
				} else {
				}
				break;
			case 'pgsql':
				if (class_exists('PDO')) {
				  $query = "create database ".$db;
					$result = $this->conn->query( $query );
				} else {
				}
				break;
			case 'sqlite':
				if (class_exists('PDO')) {
				} else {
				}
				break;
		}
		if ($result)
		  return true;
		return false;
	}


  function create_database() {

	  $table = substr($args[0],1);
	  $result = false;
		switch ( DATABASE_ENGINE ) {
			case 'mysql':
				if (class_exists('PDO')) {
			    $query = "CREATE TABLE ".mysql_escape_string($table)." (";
			    $query .= " 
			         keyname VARCHAR(255) PRIMARY KEY NOT NULL UNIQUE,
			         jsonval TEXT
			    )";
					$result = $this->conn->query( $query );
				} else {
				}
				break;
			case 'pgsql':
				if (class_exists('PDO')) {
			    $query = "CREATE TABLE ".pg_escape_string($table)." (";
			    $query .= " 
			         keyname VARCHAR(255) PRIMARY KEY NOT NULL UNIQUE,
			         jsonval TEXT
			    )";
					$result = $this->conn->query( $query );
				} else {
				}
				break;
			case 'sqlite':
				if (class_exists('PDO')) {
				} else {
				}
				break;
		}
		if ($result)
		  return true;
		return false;
	}
	
	function collections($db){
	  $list = array();
		switch ( DATABASE_ENGINE ) {
			case 'mysql':
				if (class_exists('PDO')) {
					foreach ($this->conn->query($query) as $d){
						$arr = false;
						$arr = explode('_',$d['Tables_in_'.DATABASE_NAME]);
						if (isset($arr[1]) && !in_array($arr[0],$list) && !empty($arr[0])){
							if (!is_array($list[$arr[0]]))
							  $list[$arr[0]] = array();
						  $list[$arr[0]] = array_merge(array($arr[1]),$list[$arr[0]]);
						}
					}
				} else {
					// XXX
				}
				break;
			case 'pgsql':
				if (class_exists('PDO')) {
			    $query =  "SELECT tablename AS relname FROM pg_catalog.pg_tables";
			    $query .= " WHERE schemaname NOT IN ('pg_catalog', 'information_schema',";
			    $query .= " 'pg_toast')";
					if (class_exists('PDO')) {
						foreach ( $this->conn->query($query) as $d ) {
							$arr = false;
							$arr = explode('_',$d['relname']);
							if (isset($arr[1]) && !in_array($arr[0],$list) && !empty($arr[0])){
								if (!is_array($list[$arr[0]]))
								  $list[$arr[0]] = array();
							  $list[$arr[0]] = array_merge(array($arr[1]),$list[$arr[0]]);
							}
						}
					} else {
            // XXX
					}
				} else {
				}
				break;
			case 'sqlite':
				if (class_exists('PDO')) {
				} else {
				}
				break;
		}
		return $list;
	}
	
  function all_dbs() {
	  $list = array();
		switch ( DATABASE_ENGINE ) {
			case 'mysql':
				if (class_exists('PDO')) {
			    $query =  "SHOW tables";
					if (class_exists('PDO')) {
						foreach ($this->conn->query($query) as $d){
							$arr = false;
							$arr = explode('_',$d['Tables_in_'.DATABASE_NAME]);
							if (isset($arr[1]) && !in_array($arr[0],$list) && !empty($arr[0]))
							  $list[] = $arr[0];
							elseif (!isset($arr[1]))
							  $list[] = $d['Tables_in_'.DATABASE_NAME];
						}
					} else {
	          // XXX
					}
				} else {
				}
				break;
			case 'pgsql':
				if (class_exists('PDO')) {
			    $query =  "SELECT tablename AS relname FROM pg_catalog.pg_tables";
			    $query .= " WHERE schemaname NOT IN ('pg_catalog', 'information_schema',";
			    $query .= " 'pg_toast')";
					if (class_exists('PDO')) {
						foreach ($this->conn->query($query) as $d){
							$arr = false;
							$arr = explode('_',$d['relname']);
							if (isset($arr[1]) && !in_array($arr[0],$list))
							  $list[] = $arr[0];
							elseif (!isset($arr[1]))
							  $list[] = $d['relname'];
						}
					} else {
            // XXX
					}
				} else {
				}
				break;
			case 'sqlite':
				if (class_exists('PDO')) {
				} else {
				}
				break;
		}
		return $list;
  }

}


class MulletCollection {
	
	private $db,$name,$exists;
	
	function __construct( $db, $name, $exists=false ) {
		$this->name = $name;
		$this->db = $db;
		$this->exists = $exists;
	}
	
	function insert( $doc ) {
		if (!$this->exists)
		  $this->db->create_if_not_exists( $doc, $this->name );
		return $this->db->insert_doc( $doc, $this->name );
	}

	function remove( $criteria ) {
		return $this->db->remove_doc( $criteria, $this->name );
	}
	
	function update( $criteria, $newobj ) {
		if (!$this->exists)
		  $this->db->create_if_not_exists( $newobj, $this->name );
		return $this->db->update_doc( $criteria, $newobj, $this->name );
	}
	
	function findOne( $criteria = false ) {
    return $this->db->find_one( $this->name, $criteria );
	}

	function count() {
    return $this->db->count( $this->name );
	}
	
	function find( $criteria=false ) {
    return $this->db->find( $this->name, $criteria );
	}
	
}


class MulletDocument {
  
	function __construct( $id ) {
	}
	
}


class MulletDatabase {
	
	public $name,$conn;
	
	function __construct( $conn, $name ) {
		$this->name = $name;
		$this->conn = $conn;
	}
	
	function __get( $arg ){
    return new MulletCollection( $this, $arg );
  }
	
}

class MulletRemote extends MulletDatabase {
	
	private $user,$pass;
	
	function __construct($user,$pass,$name) {
		$this->user = $user;
		$this->pass = $pass;
		$this->name = $name;
	}

	function create_if_not_exists( $doc, $name ) {
	}

	function insert_doc( $doc, $collname ) {
		return $this->post(
			'https://datamullet.com/'.$this->name.'/'.$collname.'/_insert',
      array('docs'=>json_encode(array($doc))),
			array(
		    CURLOPT_SSL_VERIFYPEER => false,
		    CURLOPT_USERPWD => $this->user.':'.$this->pass,
		    CURLOPT_HTTPAUTH => CURLAUTH_BASIC
		  )
		);
	}

	function remove_doc( $criteria, $collname ) {
	  $url = 'https://datamullet.com/'.$this->name.'/'.$collname.'/_remove';
		return $this->post(
			$url,
      array(
	      'criteria'=>json_encode(array($criteria))
      ),
			array(
		    CURLOPT_SSL_VERIFYPEER => false,
		    CURLOPT_USERPWD => $this->user.':'.$this->pass,
		    CURLOPT_HTTPAUTH => CURLAUTH_BASIC
		  )
		);
	}

	function update_doc( $criteria, $newobj, $collname ) {
	  $url = 'https://datamullet.com/'.$this->name.'/'.$collname.'/_update';
		$data = $this->post(
			$url,
      array(
	      'newobj'=>json_encode(array($newobj)),
	      'criteria'=>json_encode(array($criteria))
      ),
			array(
		    CURLOPT_SSL_VERIFYPEER => false,
		    CURLOPT_USERPWD => $this->user.':'.$this->pass,
		    CURLOPT_HTTPAUTH => CURLAUTH_BASIC
		  )
		);
		$result = json_decode($data);
	  return new MulletIterator($result->results);
	}

  function count( $collname ) {
	  $return = 0;
    return $return;
  }

  function find( $collname, $criteria = false ) {
	  if (!$criteria)
	    $url = 'https://datamullet.com/'.$this->name.'/'.$collname.'/_find';
	  else
	    $url = 'https://datamullet.com/'.$this->name.'/'.$collname.'/_find?criteria='.urlencode(json_encode(array($criteria)));
		$data = $this->get(
			$url,
      array(),
			array(
		    CURLOPT_SSL_VERIFYPEER => false,
		    CURLOPT_USERPWD => $this->user.':'.$this->pass,
		    CURLOPT_HTTPAUTH => CURLAUTH_BASIC
		  )
		);
		$result = json_decode($data);
	  return new MulletIterator($result->results);
  }

	function find_one( $collname ) {
	  $return = array();
	  $results = array();
    if (!(count($results)>0)) return new MulletDocument(0);
    $return['_id'] = new MulletDocument( $name );
    $obj = unserialize( $value );
    foreach($obj as $key=>$val)
      if (is_object($val))
        $return[$key] = (array)$val;
      else
        $return[$key] = $val;
    return $return;
	}
	
	function post($url, array $post = NULL, array $options = array()) { 
    $defaults = array( 
        CURLOPT_POST => 1, 
        CURLOPT_HEADER => 0, 
        CURLOPT_URL => $url, 
        CURLOPT_FRESH_CONNECT => 1, 
        CURLOPT_RETURNTRANSFER => 1, 
        CURLOPT_FORBID_REUSE => 1, 
        CURLOPT_TIMEOUT => 4, 
        CURLOPT_POSTFIELDS => http_build_query($post) 
    ); 
    $ch = curl_init(); 
    curl_setopt_array($ch, ($options + $defaults)); 
    if( ! $result = curl_exec($ch)) { 
        trigger_error(curl_error($ch)); 
    } 
    curl_close($ch); 
    return $result; 
	} 

	function get($url, array $get = NULL, array $options = array()) {    
    $defaults = array( 
        CURLOPT_URL => $url. (strpos($url, '?') === FALSE ? '?' : ''). http_build_query($get), 
        CURLOPT_HEADER => 0, 
        CURLOPT_RETURNTRANSFER => TRUE, 
        CURLOPT_TIMEOUT => 4 
    ); 
    $ch = curl_init(); 
    curl_setopt_array($ch, ($options + $defaults)); 
    if( ! $result = curl_exec($ch)) { 
        trigger_error(curl_error($ch)); 
    } 
    curl_close($ch); 
    return $result; 
	}
	
}

class MulletMySQL extends MulletDatabase {

	function create_fields_if_not_exists( $doc, $name ) {
	  
		$table = $this->name."_".$name;
    $sql = "SHOW columns FROM ".$table;

		if (class_exists('PDO')) {

  		try {
  		    $statement = $this->conn->prepare( $sql );
  		    $statement->execute();
  		    $results = $statement->fetchAll(PDO::FETCH_CLASS,get_class((object)array()));
  				$columns = array();
  				foreach($results as $k=>$v)
  				  $columns[] = $v->Field;
  		    foreach ($doc as $k=>$v)
  		      if (in_array($k,$columns))
  		        continue;
  			    elseif (is_string($k) && is_array($v))
  				    $this->conn->query( 'ALTER TABLE '.$table.' ADD COLUMN '.$k.' longblob' );
  				  elseif (is_string($k) && is_string($v))
  				    $this->conn->query( 'ALTER TABLE '.$table.' ADD COLUMN '.$k.' text' );
  				  elseif (is_string($k) && is_object($v))
  				    $this->conn->query( 'ALTER TABLE '.$table.' ADD COLUMN '.$k.' longblob' );
  				  elseif (is_string($k) && is_integer($v))
  				    $this->conn->query( 'ALTER TABLE '.$table.' ADD COLUMN '.$k.' integer' );
    			  elseif (is_string($k) && is_integer((integer)$v))
  				    $this->conn->query( 'ALTER TABLE '.$table.' ADD COLUMN '.$k.' integer' );
  		} catch (PDOException $err) {
  			echo $err->getMessage();
  		}

		  
		} else {
			

	    $result = mysql_query( $sql );
			$columns = array();
	    while ($v = mysql_fetch_assoc($result))
    	  $columns[] = $v['Field'];
	    foreach ($doc as $k=>$v)
	      if (in_array($k,$columns))
	        continue;
		    elseif (is_string($k) && is_array($v))
    	    $result = mysql_query( 'ALTER TABLE '.$table.' ADD COLUMN '.$k.' longblob'  );
			  elseif (is_string($k) && is_string($v))
    	    $result = mysql_query('ALTER TABLE '.$table.' ADD COLUMN '.$k.' text' );
			  elseif (is_string($k) && is_object($v))
    	    $result = mysql_query('ALTER TABLE '.$table.' ADD COLUMN '.$k.' longblob' );
			  elseif (is_string($k) && is_integer($v))
    	    $result = mysql_query( 'ALTER TABLE '.$table.' ADD COLUMN '.$k.' integer'  );
			  elseif (is_string($k) && is_integer((integer)$v))
    	    $result = mysql_query( 'ALTER TABLE '.$table.' ADD COLUMN '.$k.' integer' );


		  
		}


	}


	function create_if_not_exists( $doc, $name ) {
    $query = "CREATE TABLE IF NOT EXISTS ".$this->name."_".$name." (";
    foreach ($doc as $k=>$v)
      if (in_array($k,array('keyname','jsonval')))
        continue;
	    elseif (is_string($k) && is_array($v))
		    $query .= "$k BLOB(512),";
		  elseif (is_string($k) && is_string($v))
		    $query .= "$k text,";
		  elseif (is_string($k) && is_object($v))
		    $query .= "$k BLOB(512),";
		  elseif (is_string($k) && is_integer($v))
		    $query .= "$k int(11),";
		  elseif (is_string($k) && is_integer((integer)$v))
		    $query .= "$k int(11),";
    $query .= " 
      keyname VARCHAR(255) PRIMARY KEY NOT NULL UNIQUE,
      jsonval TEXT )";
		if (class_exists('PDO'))
			$this->conn->query($query);
		else
			mysql_query( $query );
		$this->create_fields_if_not_exists( $doc, $name );
	}

	function insert_doc( $doc, $collname ) {
		if (class_exists('PDO')) {
			$vals = array();
	    foreach ($doc as $k=>$v)
	      if (in_array($k,array('keyname','jsonval')))
	        continue;
	      elseif (is_string($k) && is_array($v))
			    $vals[':'.$k] = base64_encode(serialize($v));
			  elseif (is_string($k) && is_string($v))
			    $vals[':'.$k] = mysql_escape_string($v);
			  elseif (is_string($k) && is_object($v))
			    $vals[':'.$k] = base64_encode(serialize($v));
			  elseif (is_string($k) && is_integer($v))
			    $vals[':'.$k] = $v;
			  elseif (is_string($k) && is_integer((integer)$v))
			    $vals[':'.$k] = $v;
		  $query = "REPLACE INTO ".$this->name."_".$collname." (";
		  foreach(array_keys($vals) as $k)
		    $query .= substr($k,1).",";
		  $query .= "keyname,jsonval) VALUES (";
		  foreach(array_keys($vals) as $k)
		    $query .= "$k,";
	    $query .= ":keyname,:jsonval);";
      $vals[':keyname'] = md5(uniqid(rand(), true));
	    $vals[':jsonval'] = serialize( $doc );
			try {
				  $statement = $this->conn->prepare( $query );
			    $statement->execute( $vals );
			} catch (PDOException $err) {
				echo $err->getMessage();
			}
			return true;
		} else {
		  $query = "REPLACE INTO ".$this->name."_".$collname." (keyname,jsonval) VALUES ('".md5(uniqid(rand(), true))."','".serialize( $doc )."');";
			$result = mysql_query( $query );
			if ($result)
			  return true;
		}
		return false;
	}

	function remove_doc( $criteria, $collname ) {
		if (class_exists('PDO')) {
		  $query = "DELETE FROM ".$this->name."_".$collname." WHERE ";
		  $and = '';
	    foreach ($criteria as $c)
		    foreach ($c as $k=>$v) {
		      if (is_string($k) && is_string($v))
				    $query .= $and . "$k = '$v' ";
				  elseif (is_string($k) && is_integer($v))
				    $query .= $and . "$k = $v ";
				  $and = 'and ';
			  }
			$result = $this->conn->exec( $query );
			if ($result) 
			  return true;
		} else {
			// XXX
		}
		return false;
	}

	function update_doc( $criteria, $newobj, $collname ) {
		if (class_exists('PDO')) {
		  $query = "UPDATE ".$this->name."_".$collname." SET ";
		  $vals = array( ':jsonval' => serialize( $newobj ) );
	    foreach ($newobj as $n)
		    foreach ($n as $k=>$v)
		      if (is_string($k) && is_array($v))
				    $vals[':'.$k] = pg_escape_bytea(serialize($v));
				  elseif (is_string($k) && is_string($v))
				    $vals[':'.$k] = mysql_escape_string($v);
				  elseif (is_string($k) && is_object($v))
				    $vals[':'.$k] = pg_escape_bytea(serialize($v));
				  elseif (is_string($k) && is_integer($v))
				    $vals[':'.$k] = $v;
				  elseif (is_string($k) && is_integer((integer)$v))
				    $vals[':'.$k] = $v;
				  $comma = '';
				  foreach(array_keys($vals) as $k){
				    $query .= $comma.substr($k,1)."=".$k;
					  $comma = ",";
				  }
		  $query .= " WHERE ";
		  $and = '';
	    foreach ($criteria as $c)
		    foreach ($c as $k=>$v) {
		      if (is_string($k) && is_string($v))
				    $query .= $and . "$k = '$v' ";
				  elseif (is_string($k) && is_integer($v))
				    $query .= $and . "$k = $v ";
				  $and = 'and ';
				}
					try {
				    $statement = $this->conn->prepare( $query );
				    $statement->execute( $vals );
					} catch (PDOException $err) {
						echo $err->getMessage();
					}
					return true;
		} else {
			// XXX
		}
		return false;
	}

  function count( $collname ) {
	  $return = 0;
    $query = "SELECT count(*) as count FROM ".$this->name."_".$collname;
  	if (class_exists('PDO')) {
	    $statement = $this->conn->prepare($query);
	    $statement->execute();
	    $results = $statement->fetchAll(PDO::FETCH_CLASS,get_class((object)array()));
	    $return = $results[0]->count;
		} else {
			$result = mysql_query( $query );
			$results = mysql_fetch_assoc( $result );
			$return = $results['count'];
		}
    return $return;
  }

  function find( $collname, $criteria = false ) {
	  $limit = 15;
	  if (isset($criteria['limit'])) {
	    $limit = $criteria['limit'];
	    unset($criteria['limit']);
    }
	  $results = false;
    $query = "SELECT * FROM ".$this->name."_".$collname;
	  if ($criteria)
	    $query .= " WHERE ";
	  $and = '';
	  if ($criteria)
	    foreach ($criteria as $k=>$v) {
	      if (is_string($k) && is_string($v))
			    $query .= $and . "$k = '$v' ";
			  elseif (is_string($k) && is_integer($v))
			    $query .= $and . "$k = $v ";
	      $and = 'and ';
	    }
    $query .= " LIMIT ".$limit;
  	if (class_exists('PDO')) {
			try {
		    $statement = $this->conn->prepare( $query );
		    $statement->execute();
			} catch (PDOException $err) {
				echo $err->getMessage();
			}
	    $results = $statement->fetchAll(PDO::FETCH_CLASS,get_class((object)array()));
		} else {
			$result = mysql_query( $query );
			$results = mysql_fetch_assoc( $result );
		}
	  return new MulletIterator($results);
  }

	function find_one( $collname ) {
	  $return = array();
	  $results = array();
  	if (class_exists('PDO')) {
	    $query = "SELECT keyname, jsonval FROM ".$this->name."_".$collname." LIMIT 1";
	    $statement = $this->conn->prepare($query);
	    $statement->execute();
	    $results = $statement->fetchAll(PDO::FETCH_CLASS,get_class((object)array()));
	    $name = $results[0]->keyname;
	    $value = $results[0]->jsonval;
		} else {
	    $query = "SELECT keyname, jsonval FROM ".$this->name."_".$collname." LIMIT 1";
			$result = mysql_query( $query );
			$results = mysql_fetch_assoc( $result );
			$name = $results['keyname'];
			$value = $results['jsonval'];
		}
    if (!(count($results)>0)) return new MulletDocument(0);
    $return['_id'] = new MulletDocument( $name );
    $obj = unserialize( $value );
    foreach($obj as $key=>$val)
      if (is_object($val))
        $return[$key] = (array)$val;
      else
        $return[$key] = $val;
    return $return;
	}
	
}


class MulletPostgreSQL extends MulletDatabase {

	function create_fields_if_not_exists( $doc, $name ) {
		$table = $this->name."_".$name;
		$sql = "SELECT a.attname, pg_catalog.format_type(a.atttypid, a.atttypmod)";
    $sql .= " as type FROM pg_catalog.pg_attribute a LEFT JOIN";
    $sql .= " pg_catalog.pg_attrdef adef ON a.attrelid=adef.adrelid AND";
    $sql .= " a.attnum=adef.adnum LEFT JOIN pg_catalog.pg_type t ON";
    $sql .= " a.atttypid=t.oid WHERE a.attrelid = (SELECT oid FROM";
    $sql .= " pg_catalog.pg_class WHERE relname='$table')";
    $sql .= " and a.attname != 'tableoid' and a.attname != 'oid'";
    $sql .= " and a.attname != 'xmax' and a.attname != 'xmin'";
    $sql .= " and a.attname != 'cmax' and a.attname != 'cmin'";
    $sql .= " and a.attname != 'ctid' and a.attname != 'otre'";
    $sql .= " and a.attname not ilike '%..%' order by a.attnum ASC";
		try {
		    $statement = $this->conn->prepare( $sql );
		    $statement->execute();
		    $results = $statement->fetchAll(PDO::FETCH_CLASS,get_class((object)array()));
				$columns = array();
				foreach($results as $k=>$v)
				  $columns[] = $v->attname;
		    foreach ($doc as $k=>$v)
		      if (in_array($k,$columns))
		        continue;
			    elseif (is_string($k) && is_array($v))
				    $this->conn->query( 'ALTER TABLE '.$table.' ADD COLUMN '.$k.' bytea' );
				  elseif (is_string($k) && is_string($v))
				    $this->conn->query( 'ALTER TABLE '.$table.' ADD COLUMN '.$k.' text' );
				  elseif (is_string($k) && is_object($v))
				    $this->conn->query( 'ALTER TABLE '.$table.' ADD COLUMN '.$k.' bytea' );
				  elseif (is_string($k) && is_integer($v))
				    $this->conn->query( 'ALTER TABLE '.$table.' ADD COLUMN '.$k.' bigint' );
		} catch (PDOException $err) {
			echo $err->getMessage();
		}
	}

	function create_if_not_exists( $doc, $name ) {
    $query =  "SELECT tablename AS relname FROM pg_catalog.pg_tables";
    $query .= " WHERE schemaname NOT IN ('pg_catalog', 'information_schema',";
    $query .= " 'pg_toast') AND tablename LIKE '".$this->name."_".$name."' ORDER BY tablename";
		if (class_exists('PDO')) {
			foreach ($this->conn->query($query) as $d){
				$this->create_fields_if_not_exists( $doc, $name );
				return true;
			}
		} else {

		}
    $query = "CREATE TABLE ".$this->name."_".$name." (";
    foreach ($doc as $k=>$v)
      if (in_array($k,array('keyname','jsonval')))
        continue;
	    elseif (is_string($k) && is_array($v))
		    $query .= "$k bytea,";
		  elseif (is_string($k) && is_string($v))
		    $query .= "$k text,";
		  elseif (is_string($k) && is_object($v))
		    $query .= "$k bytea,";
		  elseif (is_string($k) && is_integer($v))
		    $query .= "$k bigint,";
    $query .= " 
         keyname VARCHAR(255) PRIMARY KEY NOT NULL UNIQUE,
         jsonval TEXT
    )";
		if (class_exists('PDO')) {
			$this->conn->query( $query );
		} else {
      // XXX
		}
	}
	
	function remove_doc( $criteria, $collname ) {
		if (class_exists('PDO')) {
		  $query = "DELETE FROM ".$this->name."_".$collname." WHERE ";
		  $and = '';
	    foreach ($criteria as $c)
		    foreach ($c as $k=>$v) {
		      if (is_string($k) && is_string($v))
				    $query .= $and . "$k = '$v' ";
				  elseif (is_string($k) && is_integer($v))
				    $query .= $and . "$k = $v ";
				  $and = 'and ';
				}
				$result = $this->conn->exec( $query );
				if ($result) 
				  return true;
		} else {
			// XXX
		}
		return false;
	}

	function update_doc( $criteria, $newobj, $collname ) {
		if (class_exists('PDO')) {

	    foreach ($newobj as $n)
			  $this->create_fields_if_not_exists( $n, $collname );
		  
		  $query = "UPDATE ".$this->name."_".$collname." SET ";
		  $vals = array( ':jsonval' => serialize( $newobj ) );
		  if (!is_array($newobj[0]))
		    $newobj = array($newobj);
	    foreach ($newobj as $n)
		    foreach ($n as $k=>$v)
		      if (is_string($k) && is_array($v))
				    $vals[':'.$k] = pg_escape_bytea(serialize($v));
				  elseif (is_string($k) && is_string($v))
				    $vals[':'.$k] = pg_escape_string($v);
				  elseif (is_string($k) && is_object($v))
				    $vals[':'.$k] = pg_escape_bytea(serialize($v));
				  elseif (is_string($k) && is_integer($v))
				    $vals[':'.$k] = $v;
				  elseif (is_string($k) && is_integer((integer)$v))
				    $vals[':'.$k] = $v;
				  $comma = '';
				  foreach(array_keys($vals) as $k){
				    $query .= $comma.substr($k,1)."=".$k;
					  $comma = ",";
				  }
		  $query .= " WHERE ";
		  $and = '';
		  if (!is_array($criteria[0]))
		    $criteria = array($criteria);
	    foreach ($criteria as $c)
		    foreach ($c as $k=>$v) {
		      if (is_string($k) && is_string($v))
				    $query .= $and . "$k = '$v' ";
				  elseif (is_string($k) && is_integer($v))
				    $query .= $and . "$k = $v ";
				  $and = 'and ';
				}
				try {
			    $statement = $this->conn->prepare( $query );
			    $statement->execute( $vals );
			    return true;
				} catch (PDOException $err) {
					echo $err->getMessage();
				}
		} else {
			// XXX
		}
		return false;
	}

	function insert_doc( $doc, $collname ) {
		$mapper = strtoupper($this->name).strtoupper($collname);
		if (class_exists($mapper)) {
			$obj = new $mapper;
		}
		
		if (class_exists('PDO')) {
			$vals = array();
	    foreach ($doc as $k=>$v)
	      if (in_array($k,array('keyname','jsonval')))
	        continue;
	      elseif (is_string($k) && is_array($v))
			    $vals[':'.$k] = pg_escape_bytea(serialize($v));
			  elseif (is_string($k) && is_string($v))
			    $vals[':'.$k] = pg_escape_string($v);
			  elseif (is_string($k) && is_object($v))
			    $vals[':'.$k] = pg_escape_bytea(serialize($v));
			  elseif (is_string($k) && is_integer($v))
			    $vals[':'.$k] = $v;
			  elseif (is_string($k) && is_integer((integer)$v))
			    $vals[':'.$k] = $v;
		  $query = "INSERT INTO ".$this->name."_".$collname." (";
		  foreach(array_keys($vals) as $k)
		    $query .= substr($k,1).",";
		  $query .= "keyname,jsonval) VALUES (";
		  foreach(array_keys($vals) as $k)
		    $query .= "$k,";
	    $query .= ":keyname,:jsonval);";
      $vals[':keyname'] = md5(uniqid(rand(), true));
	    $vals[':jsonval'] = serialize( $doc );
	    if (isset($obj))
				foreach($obj->filters as $arr)
					if ($arr[1] == 'unique')
					  if (!$this->validate_uniqueness_of($collname, $arr[0], $vals[':'.$arr[0]]))
					    return false;
			try {
			    $statement = $this->conn->prepare( $query );
			    $statement->execute( $vals );
			} catch (PDOException $err) {
				echo $err->getMessage();
			}
			return true;
		} else {
			// XXX
		}
		return false;
	}

  function count( $collname ) {
	  $return = 0;
    $query = "SELECT count(*) as count FROM ".$this->name."_".$collname;
  	if (class_exists('PDO')) {
	    $statement = $this->conn->prepare($query);
	    $statement->execute();
	    $results = $statement->fetchAll(PDO::FETCH_CLASS,get_class((object)array()));
	    $return = $results[0]->count;
		} else {
			// XXX
		}
    return $return;
  }

  function validate_uniqueness_of( $collname, $key, $newval ) {
	  $query = "select * from ".$this->name."_".$collname." where ".$key." = '".pg_escape_string($newval)."'";
  	if (class_exists('PDO')) {
			try {
		    $statement = $this->conn->prepare( $query );
		    $statement->execute();
			} catch (PDOException $err) {
				echo $err->getMessage();
			}
	    $results = $statement->fetchAll(PDO::FETCH_CLASS,get_class((object)array()));
		} else {
			// XXX
		}
    if ($results && count($results) > 0)
      return false;
    return true;
  }

  function find( $collname, $criteria = false ) {
	  $results = false;
	  $limit = 15;
	  if (is_array($criteria) && isset($criteria['limit'])) {
	    $limit = $criteria['limit'];
	    unset($criteria['limit']);
    }
    $query = "SELECT * FROM ".$this->name."_".$collname;
	  if ($criteria)
	    $query .= " WHERE ";
	  $and = '';
	  if ($criteria)
	    foreach ($criteria as $k=>$v){
	      if (is_string($k) && is_string($v))
			    $query .= $and . "$k = '$v' ";
			  elseif (is_string($k) && is_integer($v))
			    $query .= $and . "$k = $v ";
	      $and = 'and ';
	    }
      $query .= " LIMIT ".$limit;
  	if (class_exists('PDO')) {
			try {
		    $statement = $this->conn->prepare( $query );
		    $statement->execute();
			} catch (PDOException $err) {
				echo $err->getMessage();
			}
	    $results = $statement->fetchAll(PDO::FETCH_CLASS,get_class((object)array()));
		} else {
      // XXX
		}
	  return new MulletIterator($results);
  }

	function find_one( $collname ) {
	  $return = array();
	  $results = array();
  	if (class_exists('PDO')) {
	    $query = "SELECT keyname, jsonval FROM ".$this->name."_".$collname." LIMIT 1";
	    $statement = $this->conn->prepare($query);
	    $statement->execute();
	    $results = $statement->fetchAll(PDO::FETCH_CLASS,get_class((object)array()));
	    $name = $results[0]->keyname;
	    $value = $results[0]->jsonval;
		} else {
			// XXX
		}
    if (!(count($results)>0)) return new MulletDocument(0);
    $return['_id'] = new MulletDocument( $name );
    $obj = unserialize( $value );
    foreach($obj as $key=>$val)
      if (is_object($val))
        $return[$key] = (array)$val;
      else
        $return[$key] = $val;
    return $return;
	}
	
}


class MulletSQLite extends MulletDatabase {

	function create_if_not_exists( $doc, $name ) {
    $query = "CREATE TABLE IF NOT EXISTS ".$this->name."_".$name." (
          \"keyname\" VARCHAR PRIMARY KEY NOT NULL UNIQUE,
          \"jsonval\" TEXT 
    )";
		if (class_exists('PDO')) {
			$result = $this->conn->query( $query );
		} else {
			// XXX 
		}
	}

	function insert_doc( $doc, $collname ) {
		if (class_exists('PDO')) {
		  $query = "INSERT OR REPLACE INTO ".$this->name."_".$collname." (keyname,jsonval) VALUES (:name,:value);";
	    $statement = $this->conn->prepare( $query );
	    $statement->execute(array(':name'=>md5(uniqid(rand(), true)),':value'=>serialize( $doc )));
		} else {
		  // XXX
		}
	}

	function find_one( $collname ) {
	  $return = array();
	  $results = array();  
	}
	
}


class MulletIterator implements Iterator {

  private $position = 0;
  private $array = array();

  public function __construct( $results ) {
      $this->position = 0;
      $this->array = $results;
  }

  function rewind() {
      $this->position = 0;
  }

  function current() {
      return $this->array[$this->position];
  }

  function key() {
      return $this->position;
  }

  function next() {
      ++$this->position;
  }

  function valid() {
      return isset($this->array[$this->position]);
  }

  function hasNext() {
    return ($this->position < count($this->array));
  }

  function getNext(){
	  $this->next();
	  return $this->array[$this->position - 1];
  }

}


class MulletMapper {
	
	var $keys = array();
	var $filters = array();
	var $rels = array();
	
	function key( $k, $type ) {
		$this->keys[] = array($k=>$type);
	}
	
	function validates_uniqueness_of( $k ) {
		$this->filters[] = array($k,'unique');
	}

	function many( $rel ) {
		$this->rels[] = array($rel=>'many');
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




class MulletMongoDB extends MulletDatabase {

   function create_fields_if_not_exists( $doc, $name ) {
   }

   function create_if_not_exists( $doc, $name ) {
	   
   }
   
   function remove_doc( $criteria, $collname ) {

     $dbname = DATABASE_NAME;
     $db = $this->conn->$dbname;
     $collname = $this->name."_".$collname;
     $coll = $db->$collname;
     $crit = array();
     foreach ($criteria as $c) {
      if (isset($c['id'])) {
        $mongoID = new MongoID($c['id']);
        $c['_id'] = $mongoID;
        unset($c['id']);
      }
      foreach($c as $k=>$v)
        $crit[$k] = $v;
     }
     $coll->remove($crit,true);

   }

   function update_doc( $criteria, $newobj, $collname ) {

     $dbname = DATABASE_NAME;
     $db = $this->conn->$dbname;
     $collname = $this->name."_".$collname;
     $coll = $db->$collname;
     $crit = array();
     foreach ($criteria as $c) {
      if (isset($c['id'])) {
        $mongoID = new MongoID($c['id']);
        $c['_id'] = $mongoID;
        unset($c['id']);
      }
      foreach($c as $k=>$v)
        $crit[$k] = $v;
     }
     if (isset($newobj[0]['id']))
       unset($newobj[0]['id']);
     if (isset($newobj[0]['ok']))
       unset($newobj[0]['ok']);
     $coll->update($crit,array('$set'=>$newobj[0]),array("multiple"=>true));

   }

   function insert_doc( $doc, $collname ) {

      $dbname = DATABASE_NAME;
      $db = $this->conn->$dbname;
      $collname = $this->name."_".$collname;
      $coll = $db->$collname;
      $coll->insert($doc);

   }

function count( $collname ) {
}

function validate_uniqueness_of( $collname, $key, $newval ) {
}

function find( $collname, $criteria = false ) {

	$dbname = DATABASE_NAME;
	$db = $this->conn->$dbname;
	$collname = $this->name."_".$collname;
	$coll = $db->$collname;
	$data = array();
	if (!$criteria)
	  $cursor = $coll->find();
	else
	  $cursor = $coll->find($criteria);
  foreach ($cursor as $doc) {
    $result = (array)$doc;
	  $item = new stdClass;
    $_id = $result['_id'];
    $key = '$id';
		foreach($result as $k=>$v)
		  if (!in_array($k,array('_id')))
		    $item->$k = $v;
	  if (!isset($item->id))
	    $item->id = $_id->$key;
    $item->keyname = $_id->$key;
	  if (!$criteria)
		  $data[] = $item;
		else {
		  $match = false;
		    foreach ($criteria as $k=>$v) {
          if ($item->$k == $v)
            $match = true;
				}
		  if ($match)
		    $data[] = $item;
		}
	}
  return new MulletIterator($data);

}

   function find_one( $collname ) {
	   
	$dbname = $this->name;
	$db = $this->conn->$dbname;
	$coll = $db->$collname;
	if (!$criteria) $result = $coll->find();
	else
	$result = $coll->findOne($criteria);
	return $result;
	
   }
   
}




class MulletCouchDB extends MulletDatabase {
  
	function create_fields_if_not_exists( $doc, $name ) {
	}
	
	function create_if_not_exists( $doc, $name ) {
	}
	
	function remove_doc( $criteria, $collname ) {

		$coll = new couchClient('https://'.DATABASE_USER.":".DATABASE_PASSWORD."@".DATABASE_HOST.":".DATABASE_PORT,$this->name."_".$collname);
		if ( !$coll->databaseExists() )
		  $coll->createDatabase();
		$result = $coll->getAllDocs();
		$data = array();
		foreach($result->rows as $r) {
  		$item = $coll->getDoc($r->id);
  	  if (!isset($item->id))
  	    $item->id = $item->_id;
	    $item->keyname = $item->_id;
		  $match = false;
	    foreach ($criteria as $c) 
		    foreach ($c as $k=>$v)
          if ($item->$k == $v)
            $match = true;
		  if ($match)
		    $data[] = $item;
		}
		foreach($data as $doc)
		  $coll->deleteDoc($doc);

	}
	
	function update_doc( $criteria, $newobj, $collname ) {

		$coll = new couchClient('https://'.DATABASE_USER.":".DATABASE_PASSWORD."@".DATABASE_HOST.":".DATABASE_PORT,$this->name."_".$collname);
		if ( !$coll->databaseExists() )
		  $coll->createDatabase();
		$result = $coll->getAllDocs();
		$data = array();
		foreach($result->rows as $r) {
  		$item = $coll->getDoc($r->id);
	    $item->keyname = $item->_id;
		  $match = false;
	    foreach ($criteria as $c) 
		    foreach ($c as $k=>$v)
          if ($item->$k == $v)
            $match = true;
		  if ($match)
		    $data[] = $item;
		}
		foreach($data as $doc)
		  $coll->storeDoc($doc);
		  
	}
	
	function insert_doc( $doc, $collname ) {
	  
    if (!isset($doc->_id))
      $doc->_id = md5(uniqid(rand(),true));
  	$coll = new couchClient('https://'.DATABASE_USER.":".DATABASE_PASSWORD."@".DATABASE_HOST.":".DATABASE_PORT,$this->name."_".$collname);
		if ( !$coll->databaseExists() )
		  $coll->createDatabase();
		$document = new couchDocument($coll);
		$document->set($doc);
		
	}
	
	function count( $collname ) {
	}
	
	function validate_uniqueness_of( $collname, $key, $newval ) {
	}
	
	function find( $collname, $criteria = false ) {

		$coll = new couchClient('https://'.DATABASE_USER.":".DATABASE_PASSWORD."@".DATABASE_HOST.":".DATABASE_PORT,$this->name."_".$collname);
		if ( !$coll->databaseExists() )
		  $coll->createDatabase();
		$result = $coll->getAllDocs();
		$data = array();
		foreach($result->rows as $r) {
  		$result = $coll->getDoc($r->id);
		  $item = new stdClass;
  		foreach($result as $k=>$v)
  		  if (!in_array($k,array('_rev','_id')))
  		    $item->$k = $v;
  	  if (!isset($item->id))
  	    $item->id = $result->_id;
	    $item->keyname = $result->_id;
  	  if (!$criteria)
  		  $data[] = $item;
  		else {
  		  $match = false;
  		    foreach ($criteria as $k=>$v) {
            if ($item->$k == $v)
              $match = true;
  				}
  		  if ($match)
  		    $data[] = $item;
  		}
		}
	  return new MulletIterator($data);
		
	}
	
	function find_one( $collname, $criteria = false ) {
		
	}
   
}