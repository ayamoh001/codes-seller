<?php
session_start();

$baseURL = "http://192.168.1.10/crypto-cards"; // for mobile dev
$baseURL = "http://localhost/crypto-cards"; // for dev
// $baseURL = "https://cryptogamingcards.com"; // for production

$webhookBaseURL = "https://cryptogamingcards.com"; // for dev
// $webhookBaseURL = "https://cryptogamingcards.com"; // for production

$binanceURL = "https://bpay.binanceapi.com"; // for dev
// $binanceURL = "https://bpay.binanceapi.com"; // for production

$connection = mysqli_connect("localhost", "root", "", "codes_seller", 3306); // for dev
// $connection = mysqli_connect("127.0.0.1", "crypto-cards-user", "oFsEczmeNJu3asjSJgAb", "crypto-cards", 3306); // for production

// Production Only Credentials
$API_KEY = "2l7sgrt1p04xfwou1c0dk9mu61bq5uv5zke92acjojl9td78iopq4s2iyqwtg6jz";
$API_SECRET = "mtxvzkjb1uvdgoa3b6nsyoloeyfd7za3issrkaygmzx07ixzpevwmb68s1bxfhwa";

$adminUsername = "admin";
$adminPassword = "admin";

$guestIdPrefix = "Guest_";

$errorLogsFilePath = realpath(__DIR__ . '/../') . '/logs.txt';
