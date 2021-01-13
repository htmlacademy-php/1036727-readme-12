<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    header('Location: /index.php');
    exit;
}
