<?php

session_start();

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once('helpers.php');
require_once('functions.php');
$db_config = require_once('config/db.php');
$db_config = array_values($db_config);

$link = mysqli_connect(...$db_config);

if (!$link) {
    http_response_code(500);
    exit;
}

mysqli_set_charset($link, 'utf8');
