<?php

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once('helpers.php');
require_once('functions.php');
$db = require_once('config/db.php');

$link = mysqli_connect($db['host'], $db['user'], $db['password'], $db['database']);

if (!$link) {
    http_response_code(500);
    exit;
}

mysqli_set_charset($link, 'utf8');

date_default_timezone_set('Europe/Moscow');
