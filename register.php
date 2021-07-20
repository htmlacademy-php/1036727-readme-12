<?php

require_once('init.php');

if (isset($_SESSION['user'])) {
    header('Location: /feed.php');
    exit;
}

$db = Database::getInstance();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = getPostInput('registration');
    $errors = validateForm('registration', $input);

    if (!is_null($errors) && empty($errors)) {
        $input['passwd'] = getPasswordHash($input['passwd']);
        $input['avatar-path'] = uploadLocalFile('avatar');
        $stmt_data = getStmtData($input, 'registration');
        $db->insertUser($stmt_data);

        header('Location: /index.php');
        exit;
    }
}

$form_inputs = $db->getFormInputs('registration');

$page_content = includeTemplate('register.php', [
    'errors' => $errors,
    'inputs' => $form_inputs
]);

$layout_content = includeTemplate('layouts/base.php', [
    'title' => 'readme: регистрация',
    'main_modifier' => 'registration',
    'page_content' => $page_content
]);

print($layout_content);
