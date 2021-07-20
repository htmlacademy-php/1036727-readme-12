<?php

require_once('init.php');

if (isset($_SESSION['user'])) {
    header('Location: /feed.php');
    exit;
}

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

$layout_content = include_template('layouts/anonym.php', [
    'title' => 'readme: блог, каким он должен быть',
    'errors' => $errors
]);

print($layout_content);
