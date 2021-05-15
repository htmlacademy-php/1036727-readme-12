<?php

require_once('init.php');

if (isset($_SESSION['user'])) {
    header('Location: /feed.php');
    exit;
}

$form_inputs = get_form_inputs($con, 'registration');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = get_post_input('registration');

    // if (!empty($_FILES['avatar']['name'])) {
    //     $file_path = $_FILES['avatar']['tmp_name'];
    //     $file_size = $_FILES['avatar']['size'];
    //     $file_type = mime_content_type($file_path);

    //     if (!in_array($file_type, $mime_types)) {
    //         $errors['avatar'][0] = 'Неверный MIME-тип файла';
    //         $errors['avatar'][1] = 'Изображение';
    //     } elseif ($file_size > 1000000) {
    //         $errors['avatar'][0] = 'Максимальный размер файла: 1Мб';
    //         $errors['avatar'][1] = 'Изображение';
    //     } else {
    //         $file_name = uniqid();
    //         $file_extension = explode('/', $file_type);
    //         $file_name .= ".{$file_extension[1]}";
    //         $input['avatar-path'] = $file_name;
    //     }
    // }

    if (!$errors = validate_form($con, 'registration', $input)) {
        $input['password'] = password_hash($input['password'], PASSWORD_DEFAULT);
        $stmt_data = get_stmt_data($input, 'registration');
        insert_user($con, $stmt_data);

        if ($input['avatar-path']) {
            move_uploaded_file($file_path, "uploads/$file_name");
        }

        header('Location: /index.php');
        exit;
    }
}

$page_content = include_template('register.php', [
    'errors' => $errors,
    'inputs' => $form_inputs
]);

$layout_content = include_template('layout.php', [
    'title' => 'readme: регистрация',
    'main_modifier' => 'registration',
    'page_content' => $page_content
]);

print($layout_content);
