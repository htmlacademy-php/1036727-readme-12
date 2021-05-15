<?php

const FORM_TYPES = [
    'registration' => [
        'email|Электронная почта' => ['is_email_free', 'is_email_valid', 'is_filled'],
        'login|Логин' => ['is_filled'],
        'password|Пароль' => ['is_filled'],
        'password-repeat|Повтор пароля' => ['is_password_match', 'is_filled'],
        'userpic-file|Изображение' => ['is_file_valid']
    ],
    'login' => [
        'email|Электронная почта' => ['is_email_valid', 'is_filled'],
        'password|Пароль' => ['is_filled']
    ]
];

function validate_form(mysqli $con, string $form_type, array $post_data): array {
    if (!$validation_rules_list = FORM_TYPES[$form_type] ?? []) {
        return [true];
    }

    $errors = [];

    foreach ($validation_rules_list as $field_name => $validaiton_rules) {
        [$field_name, $label] = explode('|', $field_name);
        $value = $post_data[$field_name] ?? '';

        foreach ($validation_rules as $validation_rule) {
            $error = $validaiton_rule($con, $value);
            if ($error) {
                $errors[$field_name][0] = $error;
                $errors[$field_name][1] = $label;
            }
        }
    }

    return $errors;
}

function is_filled(mysqli $con, string $value) {
    if (mb_strlen($value) === 0) {
        return 'Это поле должно быть заполнено';
    }
}

function is_email_valid(mysqli $con, string $value) {
    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
        return 'E-mail введён некорректно';
    }
}

function is_email_free(mysqli $con, string $value) {
    $sql = 'SELECT id FROM user WHERE email = ?';
    if (get_mysqli_num_rows($con, $sql, [$value])) {
        return 'Пользователь с этим email уже зарегистрирован';
    }
}

function is_password_match(mysqli $con, string $value) {
    if ($value !== ($post_data['password'] ?? '')) {
        return 'Пароли не совпадают';
    }
}

function is_file_valid(mysqli $con, string $value) {
    if (!empty($_FILES['userpic-file']['name'])) {
        $mime_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_path = $_FILES['userpic-file']['tmp_name'];
        $file_size = $_FILES['userpic-file']['size'];
        $file_type = mime_content_type($file_path);

        if (!in_array($file_type, $mime_types)) {
            return 'Неверный MIME-тип файла';
        } elseif ($file_size > 1000000) {
            return 'Максимальный размер файла: 1Мб';
        }
    }
}
