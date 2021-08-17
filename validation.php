<?php

const FORM_TYPES = [
    'adding-post-photo' => [
        'heading:Заголовок' => 'maxLength:128|required',
        'image-url:Изображение' => 'maxLength:128|image:file-photo'
    ],
    'adding-post-video' => [
        'heading:Заголовок' => 'maxLength:128|required',
        'video-url:Ссылка youtube' => 'maxLength:128|youtube|url|required'
    ],
    'adding-post-text' => [
        'heading:Заголовок' => 'maxLength:128|required',
        'post-text:Текст поста' => 'maxLength:1024|required'
    ],
    'adding-post-quote' => [
        'heading:Заголовок' => 'maxLength:128|required',
        'cite-text:Текст цитаты' => 'maxLength:1024|required',
        'quote-author:Автор' => 'maxLength:128|required'
    ],
    'adding-post-link' => [
        'heading:Заголовок' => 'maxLength:128|required',
        'post-link:Ссылка' => 'maxLength:128|url|required'
    ],
    'registration' => [
        'email:Электронная почта' => 'maxLength:128|unique:email|email|required',
        'login:Логин' => 'maxLength:128|required',
        'passwd:Пароль' => 'required',
        'passwd-repeat:Повтор пароля' => 'same:passwd|required',
        'avatar:Изображение' => 'localFile:avatar'
    ],
    'login' => [
        'email:Электронная почта' => 'email|required',
        'passwd:Пароль' => 'verify|required'
    ],
    'comments' => [
        'comment:Ваш комментарий' => 'maxLength:128|minLength:4|required',
        'post-id:' => 'exists:post'
    ],
    'messages' => [
        'message:Ваше сообщение' => 'maxLength:1024|required',
        'contact-id:' => 'contact|exists:user'
    ]
];

function validateForm(string $form_type, array $post_data)
{
    if (!$validation_rules_list = FORM_TYPES[$form_type] ?? []) {
        return null;
    }

    $errors = [];

    foreach ($validation_rules_list as $form_field => $validation_rules) {
        list($field_name, $field_label) = explode(':', $form_field);
        $value = $post_data[$field_name] ?? '';

        foreach (explode('|', $validation_rules) as $validation_rule) {
            $validation_rule = explode(':', $validation_rule);
            $options = [$post_data, $validation_rule[1] ?? null];

            if ($error = $validation_rule[0]($value, $options)) {
                $errors[$field_name][0] = $error;
                $errors[$field_name][1] = $field_label;
            }
        }
    }

    return $errors;
}

function required(string $value, array $options)
{
    if (mb_strlen($value) === 0) {
        $error = 'Это поле должно быть заполнено';
    }

    return $error ?? null;
}

function email(string $value, array $options)
{
    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
        $error = 'E-mail введён некорректно';
    }

    return $error ?? null;
}

function url(string $value, array $options)
{
    if (!filter_var($value, FILTER_VALIDATE_URL)) {
        $error = 'Некорректный url-адрес';
    }

    return $error ?? null;
}

function youtube(string $value, array $options)
{
    if (strpos($value, 'youtube.com/watch?v=') === false) {
        $error = 'Некорректный youtube-адрес';
    }

    return $error ?? null;
}

function minLength(string $value, array $options)
{
    if (isset($options[1]) && (mb_strlen($value) < intval($options[1]))) {
        $error = "Значение должно быть от {$options[1]} символов";
    }

    return $error ?? null;
}

function maxLength(string $value, array $options)
{
    if (isset($options[1]) && (mb_strlen($value) > intval($options[1]))) {
        $error = "Значение должно быть до {$options[1]} символов";
    }

    return $error ?? null;
}

function exists(string $value, array $options)
{
    define('ACCEPT_ENTITIES', ['post', 'user', 'email']);

    if (isset($options[1]) && in_array($options[1], ACCEPT_ENTITIES)) {
        $method = 'is' . ucfirst($options[1]) . 'Exist';
        $method_exist = method_exists('Database', $method);

        if ($method_exist && !Database::getInstance()->$method($value)) {
            $error = true;
        }
    }

    return $error ?? null;
}

function contact(string $value, array $options)
{
    if (!Database::getInstance()->isContactValid($value)) {
        $error = true;
    }

    return $error ?? null;
}

function unique(string $value, array $options)
{
    $error_messages = [
        'email' => 'Пользователь с этим email уже зарегистрирован'
    ];

    if (isset($options[1]) && !exists($value, $options)) {
        $error = $error_messages[$options[1]] ?? '';
    }

    return $error ?? null;
}

function same(string $value, array $options)
{
    $error_messages = [
        'passwd' => 'Пароли не совпадают'
    ];

    if (isset($options[0], $options[1])) {
        list($post_data, $key) = $options;

        if ($value !== ($post_data[$key] ?? '')) {
            $error = $error_messages[$options[1]] ?? '';
        }
    }

    return $error ?? null;
}

function verify(string $value, array $options)
{
    $email = $options[0]['email'] ?? '';
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $user = Database::getInstance()->getUserByEmail($email);

        if (!($user && password_verify($value, $user['password']))) {
            $error = 'Вы ввели неверный email/пароль';
        }
    }

    return $error ?? null;
}

function localFile(string $value, array $options, int $max_size = 1)
{
    if (isset($options[1]) && !empty($_FILES[$options[1]]['tmp_name'])) {
        $file_path = $_FILES[$options[1]]['tmp_name'];
        $file_size = $_FILES[$options[1]]['size'];
        $file_type = mime_content_type($file_path);

        if (!in_array($file_type, ACCEPT_MIME_TYPES)) {
            $error = 'Неверный MIME-тип файла';
        } elseif ($file_size > (BYTES_PER_MEGABYTE * $max_size)) {
            $error = "Максимальный размер файла: {$max_size}Мб";
        }
    }

    return $error ?? null;
}

function remoteFile(string $value, array $options, int $max_size = 1)
{
    set_error_handler('exceptionsErrorHandler');
    try {
        $temp_file = tmpfile();
        $content = file_get_contents($value);
        fwrite($temp_file, $content);
        $file_path = stream_get_meta_data($temp_file)['uri'];
        $file_size = fstat($temp_file)['size'];
        $file_type = mime_content_type($file_path);
        fclose($temp_file);

    } catch (ErrorException $ex) {
        return 'Вы не загрузили файл';
    }
    restore_error_handler();

    if (!in_array($file_type, ACCEPT_MIME_TYPES)) {
        $error = 'Неверный MIME-тип файла';
    } elseif ($file_size > (BYTES_PER_MEGABYTE * $max_size)) {
        $error = "Максимальный размер файла: {$max_size}Мб";
    }

    return $error ?? null;
}

function image(string $value, array $options)
{
    if (!empty($_FILES[$options[1] ?? '']['tmp_name'])) {
        $error = localFile($value, $options);

    } elseif (filter_var($value, FILTER_VALIDATE_URL)) {
        $error = remoteFile($value, $options);

    } else {
        $error = 'Вы не загрузили файл';
    }

    return $error ?? null;
}
