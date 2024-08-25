<?php

namespace Backend\Models;

use Backend\DB\Database;
use SleekDB\Exceptions\IdNotAllowedException;
use SleekDB\Exceptions\InvalidArgumentException;
use SleekDB\Exceptions\JsonException;

enum TodoStatus: int {
  case ONGOING = 1;
  case DONE = 2;
}

class Task {

  public ?int $_id = null;

  public function __construct(
    public string $title,
    public int $owner_id,
    public string $created,
    public string $expected,
    public Todos $todos = new Todos(),
  ) {}

  public function save() {
    $db = new Database();
    $authenticated = User::getLoggedInUser();

    if ($authenticated->_id != $this->owner_id) {
      http_response_code(HTTP_UNAUTHORIZED);
      echo @json_encode([ 'err' => "unauthorized to create a task for {$this->owner_id}" ]);
      exit();
    }

    try {
      $task = [
        'title' => $this->title,
        'owner_id' => $this->owner_id,
        'created' => $this->created,
        'expected' => $this->expected,
        'todos' => $this->todos->toArray(),
      ];
      
      if ($this->_id) {
        $db->TodoStore->updateById($this->_id, $task);
      } else {
        $result = $db->TodoStore->insert($task);
        $task['_id'] = $result['_id'];
      }

      return $task;
    } catch (IdNotAllowedException $err) {
      http_response_code(HTTP_BAD_REQUEST);
      echo @json_encode([ 'err' => 'you are not allowed to set id' ]);
      exit();
    } catch (InvalidArgumentException $err) {
      http_response_code(HTTP_BAD_REQUEST);
      echo @json_encode([ 'err' => 'invalid argument' ]);
      exit();
    } catch (JsonException $err) {
      http_response_code(HTTP_BAD_REQUEST);
      echo @json_encode([ 'err' => 'invalid argument' ]);
      exit();
    }
  }

  public function delete() {
    $db = new Database();
    $authenticated = User::getLoggedInUser();

    Task::_checkifOwner($authenticated, $this);

    try {
      $db->TodoStore->deleteById($this->_id);

      http_response_code(HTTP_OK);
      echo @json_encode([ 'message' => "task \"{$this->title}\" was deleted." ]);
      exit();
    } catch (InvalidArgumentException $err) {
      http_response_code(HTTP_BAD_REQUEST);
      echo @json_encode([ 'err' => 'bad request' ]);
      exit();
    }
  }

  static function fetchAllTasks() {
    $db = new Database();
    $authenticated = User::getLoggedInUser();

    if (is_null($authenticated)) {
      echo @json_encode([ 'message' => 'unauthenticated', 'todos' => [] ]);
      exit();
    }

    echo @json_encode([
      'user' => $authenticated,
      'tasks' => $db->TodoStore->findBy([ ['owner_id', '=', $authenticated->_id] ]),
    ]);
  }

  static function deleteByRequest(object $req) {
    $db = new Database();
    $authenticated = User::getLoggedInUser();

    Task::_checkifOwner($authenticated, $req);

    try {
      $db->TodoStore->deleteById($req->task_id);

      http_response_code(HTTP_OK);
      echo @json_encode([ 'message' => 'not sure, but I guess it has been removed' ]);
    } catch (InvalidArgumentException $err) {
      http_response_code(HTTP_BAD_REQUEST);
      echo @json_encode([ 'err' => 'bad request' ]);
      exit();
    }
  }

  static private function _checkifOwner(User $authenticated, object $req) {
    if (is_null($authenticated)) {
      echo @json_encode([ 'message' => 'unauthenticated', 'todos' => [] ]);
      exit();
    }

    if ($authenticated->_id != $req->owner_id) {
      http_response_code(HTTP_UNAUTHORIZED);
      echo @json_encode([ 'err' => 'unauthorized to delete task' ]);
      exit();
    }
  }

}

class Todos {

  public function __construct(private $todos = []) {}

  public function toArray() {
    $result = [];
    
    foreach ($this->todos as $todo) {
      array_push($result, (array) $todo);
    }

    return $result;
  }

  public function add(Todo $todo) {
    array_push($this->todos, $todo);
    return $this;
  }

  public function deleteById(int $id) {
    $result = [];

    foreach ($this->todos as $todo) {
      if ($todo->_id != $id) array_push($result, $todo);
    }

    return $this->todos = $result;
  }

  public function replaceTodoById(int $id, Todo $newTodo) {
    $result = [];
    
    foreach ($this->todos as $todo) {
      if ($todo->_id == $id) {
        array_push($result, $newTodo);
      } else {
        array_push($result, $todo);
      }
    }
  }

}

class Todo {

  public int $_id;

  public function __construct(
    public string $name,
    public ?string $description,
    public string $created,
    public ?string $finished,
    public TodoStatus $status,
  ) {}

}