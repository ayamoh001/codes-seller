<?php
try {
  require_once "./include/config.php";
  require_once "./include/functions.php";

  $callbackResponse = file_get_contents('php://input');
  $webhookLogsFile = "./binance_payment_webhook_logs.json";
  $log = fopen($webhookLogsFile, "a");
  fwrite($log, $callbackResponse);
  fclose($log);
} catch (Throwable $e) {
  logErrors($e);
  exit;
}
