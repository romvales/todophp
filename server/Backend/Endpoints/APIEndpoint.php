<?php

namespace Backend\Endpoints;

use Backend\DB\Database;
use Backend\Models\Todos;
use Backend\Models\Task;
use Backend\Models\User;
use \Bramus\Router\Router;
use SleekDB\Exceptions\InvalidArgumentException;

class APIEndpoint {
  private Database $db;

  function __construct(public Router $router) {
    $this->db = new Database();

    // $router->before('GET|POST|PUT|DELETE|HEAD', '/api/.*', function() {
    //   if (!User::getLoggedInUser()) {
    //     http_response_code(HTTP_UNAUTHORIZED);
    //     echo @json_encode([ 'error' => 'unauthorized' ]);
    //     exit();
    //   }
    // });
    
    $router->mount('/api', function() use ($router) {
      header('Content-Type: application/json');

      $router->get('/users', function() {
        echo @json_encode(User::fetchAllUsers());
      });

      $router->post('/users', function() {
        $json = file_get_contents('php://input');
        $user = json_decode($json);
        (new User($user->email, $user->name, password_hash($user->password, PASSWORD_BCRYPT)))->save();
      });

      $router->delete('/users', function() {
        $json = file_get_contents('php://input');
        $req = json_decode($json);

        try {
          $this->db->UserStore->deleteById($req->userId);

          echo @json_encode([ 'message' => "user was deleted." ]);
        } catch (InvalidArgumentException $err) {
          http_response_code(HTTP_BAD_REQUEST);
          echo @json_encode([ 'err' => '' ]);
          exit();
        }
      });


      $router->get('/tasks', function() {
        $json = file_get_contents('php://input');
        $req = @json_decode($json);
        return Task::fetchAllTasks($req);
      });

      $router->post('/tasks', function() {
        $json = file_get_contents('php://input');
        $req = @json_decode($json);

        $task = new Task(
          $req->title,
          $req->owner_id, 
          $req->created, 
          $req->expected, 
          new Todos($req->todos),
        );

        if ($req->_id) $task->_id = $req->_id;

        $task->save();
      });

      $router->delete('/tasks', function() {
        $json = file_get_contents('php://input');
        $req = @json_decode($json);
        Task::deleteByRequest($req);
      });

      
    });

    
  }


}