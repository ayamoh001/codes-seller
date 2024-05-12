<?php
include "../include/config.php";

$_SERVER['PHP_AUTH_USER'] = "";
$_SERVER['PHP_AUTH_PW'] = "";

header("Location: $baseURL");
