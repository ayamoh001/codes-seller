<?php
session_start();

$baseURL = $_SERVER["HTTP_HOST"] == "localhost" ? "http://localhost/codes-seller" : "https://" . $_SERVER['HTTP_HOST'];

// $connection = mysqli_connect("localhost", "root", "", "codes_seller"); // for production
$connection = mysqli_connect("localhost", "root", "", "codes_seller"); // for development
