<?php

namespace Src\Models;

use Src\DBConnection;
use Src\Logging\Logger;

abstract class BaseModel extends DBConnection
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function logError(string $message, $errorMessage = null): void
      {
          $logMessage = $message;

          if ($errorMessage) {
              $logMessage .= ' - ' . $errorMessage;
          }

          Logger::logText($logMessage);
      }

    abstract public function getTableName(): string;
}