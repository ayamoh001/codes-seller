<?php

$callbackResponse = file_get_contents('php://input');
$logFile = "./binance_payment_webhook_logs.json";
$log = fopen($logFile, "a");
fwrite($log, $callbackResponse);
fclose($log);
