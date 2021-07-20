<?php

session_start();

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once('helpers.php');
require_once('functions.php');
require_once('validation.php');
require_once('classes/QueryBuilder.php');
require_once('classes/Database.php');

const ACCEPT_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif'];
const BYTES_PER_MEGABYTE = 1048576;

const SECONDS_PER_MINUTE = 60;
const SECONDS_PER_HOUR = SECONDS_PER_MINUTE * 60;
const SECONDS_PER_DAY = SECONDS_PER_HOUR * 24;
const SECONDS_PER_WEEK = SECONDS_PER_DAY * 7;
const SECONDS_PER_MONTH = SECONDS_PER_DAY * 30;
const SECONDS_PER_YEAR = SECONDS_PER_MONTH * 12;
