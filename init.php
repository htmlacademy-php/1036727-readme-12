<?php

session_start();

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once('helpers.php');
require_once('functions.php');
require_once('db-functions.php');
$db_config = require_once('config/db.php');
$db_config = array_values($db_config);

$con = mysqli_connect(...$db_config);

if (!$con) {
    http_response_code(500);
    exit;
}

mysqli_set_charset($con, 'utf8');
