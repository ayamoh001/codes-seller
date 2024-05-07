<?php
session_start();

$baseURL = $_SERVER["HTTP_HOST"] == "localhost" ? "http://localhost/codes-seller" : "https://" . $_SERVER['HTTP_HOST'];

$connection = mysqli_connect("localhost", "root", "", "codes_seller"); // for production
// $connection = mysqli_connect("127.0.0.1", "crypto-cards-user", "oFsEczmeNJu3asjSJgAb", "crypto-cards", 3306); // for production

$adminUsername = "admin";
$adminPassword = "admin";
