<?php

namespace Src\Logging;

/**
 * Logs information to a daily log file (safe against XSS).
 * Use this to debug requests, data, or errors.
 */
class Logger
{
  private const LOG_DIRECTORY = __DIR__ . '/../log';

  /**
   * Escapes text to prevent XSS if viewing logs in browser.
   */
  private static function escape(string $text): string
  {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
  }

  /**
   * Logs text or arrays to a .log file with timestamp and source.
   */
  public static function logText(string|array ...$info): void
  {
    $logFileName = self::LOG_DIRECTORY . '/log' . date('Ymd') . '.log';

    // Ensure logging directory exists
    if (!is_dir(self::LOG_DIRECTORY)) {
      if (!mkdir(self::LOG_DIRECTORY, 0777, true)) {
        return;
      }
    }

    $text = '--- ' . date('Y-m-d h:i:s A') . " ---\n";

    if (count($bt = debug_backtrace()) > 1) {
      $text .= 'FILE: ' . $bt[1]['file'] . "\n";
    }

    foreach ($info as $pieceOfInfo) {
      if (is_array($pieceOfInfo)) {
        $text .= self::escape(print_r($pieceOfInfo, true)) . "\n";
      } else {
        $text .= self::escape($pieceOfInfo) . "\n";
      }
    }

    file_put_contents($logFileName, $text, FILE_APPEND);
  }

  /**
   * Logs incoming HTTP requests (method, URL, body).
   */
  public static function logRequest(string $method, string $url, ?string $body = null): void
  {
    $logInfo = ["REQUEST: {$method} {$url}"];

    if (!empty($body)) {
      $logInfo[] = 'JSON Body:';
      $logInfo[] = $body;
    }

    self::logText(...$logInfo);
  }
}

?>