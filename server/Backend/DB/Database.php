<?php

namespace Backend\DB;

use \SleekDB\Store;

class Database {

  private string $databaseDirectory = __DIR__ . '/../../../data';

  public Store $UserStore;
  public Store $TodoStore;

  public function __construct() {
    $this->UserStore = new Store("users", $this->databaseDirectory);
    $this->TodoStore = new Store("todos", $this->databaseDirectory);
  }


}