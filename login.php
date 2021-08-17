<?php

require_once('init.php');

if (isset($_SESSION['user'])) {
    header('Location: /feed.php');
    exit;
}

$db = Database::getInstance();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = authenticate();
}

$form_inputs = $db->getFormInputs('login');

$page_content = includeTemplate('login.php', [
    'errors' => $errors,
    'inputs' => $form_inputs
]);

$layout_content = includeTemplate('layouts/base.php', [
    'title' => 'readme: авторизация',
    'main_modifier' => 'login',
    'page_content' => $page_content
]);

print($layout_content);
