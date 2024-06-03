<?php
require_once "./include/config.php";

try {
  $callbackResponse = file_get_contents('php://input');
  $webhookLogsFile = "./binance_payment_webhook_logs.json";
  $log = fopen($webhookLogsFile, "a");
  fwrite($log, $callbackResponse);
  fclose($log);
} catch (Throwable $e) {
  $errorMessage = $e->getFile() . " | " . $e->getLine() . " | " . $e->getMessage();
  file_put_contents($errorLogsFilePath, $errorMessage, FILE_APPEND);
  exit;
}
