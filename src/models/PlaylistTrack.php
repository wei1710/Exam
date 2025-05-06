<?php


namespace Src\Models;

use Src\DBConnection;
use Src\Logging\Logger;

Class PlaylistTrack extends DBConnection
{
  public function __construct()
  {
    parent::__construct();
  }
}