<?php
session_start();

$baseURL = "http://localhost/crypto-cards"; // for dev
// $baseURL = "https://cryptogamingcards.com"; // for production

$connection = mysqli_connect("localhost", "root", "", "codes_seller"); // for development
// $connection = mysqli_connect("127.0.0.1", "crypto-cards-user", "oFsEczmeNJu3asjSJgAb", "crypto-cards", 3306); // for production

$adminUsername = "admin";
$adminPassword = "admin";
