<?php
session_start();

$baseURL = "http://localhost/crypto-cards"; // for dev
// $baseURL = "https://cryptogamingcards.com"; // for production

$binanceURL = "https://bpay.binanceapi.com"; // for dev
// $binanceURL = "https://bpay.binanceapi.com"; // for production

$connection = mysqli_connect("localhost", "root", "", "codes_seller"); // for dev
// $connection = mysqli_connect("127.0.0.1", "crypto-cards-user", "oFsEczmeNJu3asjSJgAb", "crypto-cards", 3306); // for production

$API_KEY = "7i0vMOZJsb46LU4shHfpDN0QouQmB7jVsbkczQQs5PyEfw7QhiLXFJB6ryxGoVg1";
$API_SECRET = "IxQwY8QrnHEhT2lBLSsRSmftkffAmhRyesWBQYWfZMnWEt8Zc3AYIsbMtmOhCYda";

$adminUsername = "admin";
$adminPassword = "admin";

$guestIdPrefix = "Guest_";

$errorLogsFilePath = __DIR__ . "/logs.txt";
