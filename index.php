<?php

require_once('init.php');

if (isset($_SESSION['user'])) {
    header('Location: /feed.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = authenticate();
}

$layout_content = includeTemplate('layouts/anonym.php', [
    'title' => 'readme: блог, каким он должен быть',
    'errors' => $errors
]);

print($layout_content);
