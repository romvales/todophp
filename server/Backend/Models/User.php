<?php

namespace Backend\Models;

use Backend\DB\Database;
use LengthException;
use SleekDB\Exceptions\InvalidArgumentException;
use SleekDB\Exceptions\IdNotAllowedException;
use TypeError;

class User {

  public int $_id;

  public function __construct(
    public string $email,
    public ?string $name,
    public ?string $password,
  ) {}

  public function save() {
    $db = new Database();

    try {
      $user = [
        'email' => $this->email,
        'name'  => $this->name,
        'password' => $this->password,
      ];

      $count = count($db->UserStore->findBy([ 'email', '=', $user['email'] ], [], 1));

      // New record
      if ($count != 1) $user = $db->UserStore->insert($user);
      else throw new LengthException();

      unset($user['password']);
      http_response_code(HTTP_OK);
      echo @json_encode($user);
      exit();
    } catch (InvalidArgumentException $err) {
      http_response_code(HTTP_BAD_REQUEST);
      echo @json_encode([ 'err' => $err ]);
      exit();
    } catch (IdNotAllowedException $err) {
      http_response_code(HTTP_BAD_REQUEST);
      echo @json_encode([ 'err' => $err ]);
      exit();
    } catch (LengthException $err) {
      http_response_code(HTTP_BAD_REQUEST);
      echo @json_encode([ 'message' => "user {$user['email']} already exists." ]);
      exit();
    }
  }

  public function delete() {
    $db = new Database();

    try { 
      $db->UserStore->deleteById($this->_id);
      http_response_code(HTTP_OK);
      echo @json_encode([ 'message' => "user {$this->email} just got deleted." ]);
      exit();
    } catch (InvalidArgumentException $err) {
      http_response_code(HTTP_BAD_REQUEST);
      echo @json_encode([ 'err' => $err ]);
      exit();
    } catch (TypeError $err) {
      http_response_code(HTTP_BAD_REQUEST);
      echo @json_encode([ 'err' => 'bad request' ]);
    }
  }

  static function getUserById(int $id) {
    $db = new Database();

    try {
      $result = $db->UserStore->findById($id);

      $user = new User($result['email'], $result['name'], $result['password']);
      $user->_id = $result['_id'];

      return $user;
    } catch (InvalidArgumentException $err) {
      http_response_code(HTTP_BAD_REQUEST);
      echo @json_encode([ 'err' => $err ]);
      exit();
    } catch (TypeError $err) {
      http_response_code(HTTP_BAD_REQUEST);
      echo @json_encode([ 'err' => 'bad request' ]);
      exit();
    }
  }

  static function getUserByEmail(string $email) {
    $db = new Database();

    try {
      $result = $db->UserStore->findBy([ 'email', '=', $email ], null, 1)[0];

      if (count($result) == 0) throw new LengthException();

      $user = new User($result['email'], $result['name'], $result['password']);
      $user->_id = $result['_id'];

      return $user;
    } catch (InvalidArgumentException $err) {
      http_response_code(HTTP_BAD_REQUEST);
      echo @json_encode([ 'err' => $err ]);
      exit();
    } catch (TypeError $err) {
      http_response_code(HTTP_BAD_REQUEST);
      echo @json_encode([ 'err' => 'bad request' ]);
      exit();
    } catch (LengthException $err) {
      http_response_code(HTTP_NOT_FOUND);
      echo @json_encode([ 'err' => "user {$email} was not found." ]);
      exit();
    }
    
  }

  static function fetchAllUsers() {
    $users = (new Database())->UserStore->findAll();

    $result = [];

    foreach ($users as $user) {
      $user = (array) $user;
      unset($user['password']);
      array_push($result, $user);
    }

    return $result;
  }

  static function login(object|null $req) {
    $now = time();

    if (isset($_COOKIE['authenticated'])) {
      http_response_code(HTTP_BAD_REQUEST);
      echo @json_encode([ 'err' => 'already authenticated' ]);
      exit();
    }

    if (is_null($req)) {
      http_response_code(HTTP_BAD_REQUEST);
      echo @json_encode([ 'err' => 'missing required parameters' ]);
      exit();
    }

    $user = User::getUserByEmail($req->email);
    $digest = crypt($req->password, $user->password);

    if ($digest == $user->password) {
      $user->password = $req->password;
      setcookie('authenticated', base64_encode(json_encode($user)), $now+(60*60*24*30), '/', '', true);
      echo @json_encode([ 'status' => 'authenticated' ]);
    } else {
      http_response_code(HTTP_UNAUTHORIZED);
      echo @json_encode([ 'err' => "incorrect password provided." ]);
    }
  }

  static function getLoggedInUser() {
    $db = new Database();

    if (!isset($_COOKIE['authenticated'])) return null;

    $json = @json_decode(base64_decode($_COOKIE['authenticated']));
    $user = new User($json->email, $json->name, $json->password);
    $user->_id = $json->_id;

    if (!$db->UserStore->findById($user->_id)) {
      http_response_code(HTTP_UNAUTHORIZED);
      User::clear($user);
      exit();
    }

    unset($user->password);

    return $user;
  }

  static function logout() {
    $now = time();

    if (!isset($_COOKIE['authenticated'])) {
      http_response_code(HTTP_BAD_REQUEST);
      echo @json_encode([ 'err' => 'not logged in.' ]);
      exit();
    }

    $user = User::getLoggedInUser();

    User::clear($user);
  }

  static function clear(User $user) {
    $now = time();

    unset($_COOKIE['authenticated']);
    setcookie('authenticated', '', -($now+(60*60*24*30)), '/', '', true);
    echo @json_encode([ 'message' => "user {$user->email} has been logged out." ]);
  }

}