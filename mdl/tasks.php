<?php

class Tasks extends MulletMapper {

  function get() {
    $conn = new Mullet();
    $coll = $conn->user->tasks;
    $tasks = array();
    $cursor = $coll->find();
  	while( $cursor->hasNext() ) {
  		$task = $cursor->getNext();
      $tasks[] = array(
        'name' => $task->name,
        'done' => false,
        'id' => $task->id
      );
  	}
    return $tasks;
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
    return array(
      'id'=>$data->id
    );
  }

  function put() {
    $conn = new Mullet();
    $coll = $conn->user->tasks;
    $data = json_decode(file_get_contents('php://input'));
    $result = $coll->update(
      array(array( 'id' => $_GET['id'] )),
      array((array)$data)
    );
    return array(
      'ok'=>true
    );
  }

  function delete() {
    $conn = new Mullet();
    $coll = $conn->user->tasks;
    $result = $coll->remove(
      array(array( 'id' => $_GET['id'] ))
    );
    return array(
      'ok'=>true
    );
  }

}

