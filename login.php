<?php

require_once('init.php');

if (isset($_SESSION['user'])) {
    header('Location: /feed.php');
    exit;
}

$input_fields = 'i.id, i.label, i.name, i.placeholder, i.required';
$sql = "SELECT {$input_fields}, it.name AS type, f.name AS form FROM input i
    INNER JOIN input_type it ON it.id = i.type_id
    INNER JOIN form_input fi ON fi.input_id = i.id
    INNER JOIN form f ON f.id = fi.form_id
    WHERE f.name = 'login'";

$form_inputs = get_mysqli_result($con, $sql);
$input_names = array_column($form_inputs, 'name');
$form_inputs = array_combine($input_names, $form_inputs);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = get_post_input($con, 'login');

    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'][0] = 'E-mail введён некорректно';
        $errors['email'][1] = 'Электронная почта';
    }

    $required_fields = get_required_fields($con, 'login');
    foreach ($required_fields as $field) {
        if (mb_strlen($input[$field]) === 0) {
            $errors[$field][0] = 'Это поле должно быть заполнено';
            $errors[$field][1] = $form_inputs[$field]['label'];
        }
    }

    if (empty($errors)) {
        $email = mysqli_real_escape_string($con, $input['email']);

        $user_fields = 'id, dt_add, email, login, password, avatar_path';
        $sql = "SELECT $user_fields FROM user WHERE email = '$email';";
        $user = get_mysqli_result($con, $sql, 'assoc');

        if ($user && password_verify($input['password'], $user['password'])) {
            $_SESSION['user'] = $user;
            $url = $_COOKIE['login_ref'] ?? '/feed.php';
            setcookie('login_ref', '', time() - 3600);

            header("Location: $url");
            exit;

        } else {
            $errors['email'] = ['Вы ввели неверный email/пароль', 'Электронная почта'];
            $errors['password'] = ['Вы ввели неверный email/пароль', 'Пароль'];
        }
    }
}

$page_content = include_template('login.php', [
    'errors' => $errors,
    'inputs' => $form_inputs
]);

$layout_content = include_template('layout.php', [
    'title' => 'readme: авторизация',
    'main_modifier' => 'login',
    'page_content' => $page_content
]);

print($layout_content);
