<?php

class Tasks extends MulletMapper {

  function get( $request, $response ) {
    $conn = new Mullet();
    $coll = $conn->user->tasks;
    $tasks = array();
    $cursor = $coll->find();
  	while( $cursor->hasNext() ) {
  		$task = $cursor->getNext();
      $tasks[] = array(
        'name' => $task->name,
        'id' => $task->id
      );
  	}
    return $tasks;
  }

  function post( $request, $response ) {
    $conn = new Mullet();
    $coll = $conn->user->tasks;
    $data = json_decode(file_get_contents('php://input'));
    $result = $coll->insert(
      $data
    );
    return array(
      'ok'=>true
    );
  }

  function put( $request, $response ) {
    $conn = new Mullet();
    $coll = $conn->user->tasks;
    $data = json_decode(file_get_contents('php://input'));
    $result = $coll->update(
      array(array( 'id' => $request->id )),
      array((array)$data)
    );
    return array(
      'ok'=>true
    );
  }

  function delete( $request, $response ) {
    $conn = new Mullet();
    $coll = $conn->user->tasks;
    $result = $coll->remove(
      array(array( 'id' => $request->id ))
    );
    return array(
      'ok'=>true
    );
  }

}

