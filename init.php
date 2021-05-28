<?php

session_start();

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once('helpers.php');
require_once('functions.php');
require_once('validation.php');
require_once('classes/QueryBuilder.php');
require_once('classes/Database.php');

define('ACCEPT_MIME_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('BYTES_PER_MEGABYTE', 1048576);

define('SECONDS_PER_MINUTE', 60);
define('SECONDS_PER_HOUR', SECONDS_PER_MINUTE * 60);
define('SECONDS_PER_DAY', SECONDS_PER_HOUR * 24);
define('SECONDS_PER_WEEK', SECONDS_PER_DAY * 7);
define('SECONDS_PER_MONTH', SECONDS_PER_DAY * 30);
define('SECONDS_PER_YEAR', SECONDS_PER_MONTH * 12);
