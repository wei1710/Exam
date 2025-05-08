<?php

namespace Src\Logging;

/**
 * Logs the information it receives as a parameter to a log file in the log folder.
 * @param $info An undefined series of strings or arrays to log
 */

class Logger
{
  private const LOG_DIRECTORY = __DIR__ . '/../log';

  public static function logText(string|array ...$info): void
  {
    $logFileName = Logger::LOG_DIRECTORY . '/log' . date('Ymd') . '.htm';

    // If the logging directory does not exist, it is created
    if (!is_dir(self::LOG_DIRECTORY)) {
      if (!mkdir(self::LOG_DIRECTORY)) {
        return;
      }
    }

    $text = '';
    if (!file_exists($logFileName)) {
      $text .= '<pre>';
    }
    $text .= '--- ' . date('Y-m-d h:i:s A', time()) . ' ---<br>';

    // The name of the invoking file is displayed
    if (count($bt = debug_backtrace()) > 1) {
      $text .= 'FILE ' . $bt[1]['file'] . '<br>';
    };

    foreach ($info as $pieceOfInfo) {
      if (gettype($pieceOfInfo) === 'array') {
        $text .= print_r($pieceOfInfo, true);
      } else {
        $text .= $pieceOfInfo . '<br>';
      }
    }

    $logFile = fopen($logFileName, 'a');
    fwrite($logFile, $text);
    fclose($logFile);
  }

  /**
   * Logs details about the incoming HTTP request.
   *
   * @param string $method The HTTP request method (e.g., GET, POST).
   * @param string $url The requested URL.
   * @param string|null $body The raw request body string (optional).
   */
  public static function logRequest(string $method, string $url, ?string $body = null): void
  {
    $logInfo = ["REQUEST: {$method} {$url}"];

    // Log the raw body string if it exists
    if ($body !== null && $body !== '') { // Check if body is not null and not empty
      $logInfo[] = 'JSON Body:';
      $logInfo[] = $body; // Log the raw string directly
    }

    // Use the existing logText method to write the formatted request info
    self::logText(...$logInfo); // Use ... to pass array elements as separate arguments
  }
}
