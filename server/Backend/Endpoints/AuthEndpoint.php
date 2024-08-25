<?php

namespace Backend\Endpoints;

use Backend\Models\User;
use Bramus\Router\Router;

class AuthEndpoint {
  public function __construct(public Router $router) {
    $router->mount('/auth', function() use ($router) {
      header('Content-Type: application/json');

      // 
      $router->post('/', function() {
        $json = file_get_contents('php://input');
        $req = json_decode($json);
        $pass = password_hash($req->password, PASSWORD_BCRYPT);

        $user = new User($req->email, $req->name, $pass);
        return $user->save();
      });

      //
      $router->delete('/', function() {
        $authenticated = User::getLoggedInUser();
        $json = file_get_contents('php://input');
        $req = json_decode($json);

        if (is_null($req->_id)) {
          http_response_code(HTTP_BAD_REQUEST);
          echo @json_encode([ 'err' => 'missing required parameter.' ]);
          exit();
        }

        $user = User::getUserById($req->_id);

        if ($authenticated->_id != $user->_id) {
          http_response_code(HTTP_UNAUTHORIZED);
          echo @json_encode([ 'err' => 'unauthorized' ]);
          exit();
        }

        $user->delete();
        User::logout();
        
        echo @json_encode([ 'message' => "user {$user->email} has been deleted." ]);
      });


      // 
      $router->post('/login', function() {
        $json = file_get_contents('php://input');
        $req = json_decode($json);
        User::login($req);
      });

      //
      $router->post('/logout', function() {
        User::logout();
      });

    });
  }



}