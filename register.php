<?php

require_once('init.php');

if (isset($_SESSION['user'])) {
    header('Location: /feed.php');
    exit;
}

$form_inputs = Database::getInstance()->getFormInputs('registration');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = getPostInput('registration');
    $errors = validateForm('registration', $input);

    if (!is_null($errors) && empty($errors)) {
        $input['passwd'] = getPasswordHash($input['passwd']);
        $input['avatar-path'] = uploadAvatarFile();
        $stmt_data = getStmtData($input, 'registration');
        Database::getInstance()->insertUser($stmt_data);

        header('Location: /index.php');
        exit;
    }
}

$page_content = include_template('register.php', [
    'errors' => $errors,
    'inputs' => $form_inputs
]);

$layout_content = include_template('layouts/base.php', [
    'title' => 'readme: регистрация',
    'main_modifier' => 'registration',
    'page_content' => $page_content
]);

print($layout_content);
