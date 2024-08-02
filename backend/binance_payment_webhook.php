<?php
try {
  require_once "./include/config.php";
  require_once "./include/functions.php";

  logErrors($_SERVER['REQUEST_URI']);
  logErrors(json_encode($_GET));
  logErrors(json_encode($_POST));
  logErrors(file_get_contents('php://input'));

  $callbackResponse = file_get_contents('php://input');
  $webhookLogsFile = "./binance_payment_webhook_logs.json";
  $log = fopen($webhookLogsFile, "a");
  fwrite($log, $callbackResponse);
  fclose($log);
} catch (Throwable $e) {
  logErrors($e);
  exit;
}
