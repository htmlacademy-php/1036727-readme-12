<?php

require_once('init.php');

if (isset($_SESSION['user'])) {
    header('Location: /feed.php');
    exit;
}

$form_inputs = Database::getInstance()->getFormInputs('login');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = getPostInput('login');
    $errors = validateForm('login', $input);

    if (!is_null($errors) && empty($errors)) {
        $user = Database::getInstance()->getUserByEmail($input['email']);
        $_SESSION['user'] = $user;
        $url = $_COOKIE['login_ref'] ?? '/feed.php';
        setcookie('login_ref', '', time() - 3600);

        header("Location: $url");
        exit;
    }
}

$page_content = include_template('login.php', [
    'errors' => $errors,
    'inputs' => $form_inputs
]);

$layout_content = include_template('layouts/base.php', [
    'title' => 'readme: авторизация',
    'main_modifier' => 'login',
    'page_content' => $page_content
]);

print($layout_content);
