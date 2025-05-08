<?php


namespace Src\Models;

use Src\DBConnection;
use Src\Logging\Logger;

class PlaylistTrack extends DBConnection
{
  public function __construct()
  {
    parent::__construct();
  }
}