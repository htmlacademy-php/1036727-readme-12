<?php

require_once('init.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = get_post_input($link, 'login');

    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'E-mail введён некорректно';
    }

    $required_fields = get_required_fields($link, 'login');
    foreach ($required_fields as $field) {
        if (strlen($input[$field]) == 0) {
            $errors[$field] = 'Заполните это поле';
        }
    }

    if (empty($errors)) {

    }
}

$layout_content = include_template('layout2.php', [
    'title' => 'readme: блог, каким он должен быть',
    'errors' => $errors
]);

print($layout_content);
